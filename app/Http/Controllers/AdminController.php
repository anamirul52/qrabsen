<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\QrToken;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ExcelService;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Admin Dashboard Overview
     */
    public function dashboard()
    {
        $today = Carbon::today()->toDateString();

        $totalStudents = Student::where('status', 'active')->count();
        $totalTeachers = Teacher::count();
        $totalClasses = SchoolClass::count();

        // Today's attendance stats across all sessions
        $todaySessions = AttendanceSession::with('schoolClass', 'subject', 'teacher.user')
            ->whereDate('date', $today)
            ->get();

        $todaySessionIds = $todaySessions->pluck('id')->toArray();

        $todayRecords = AttendanceRecord::whereIn('attendance_session_id', $todaySessionIds)->get();

        $hadirToday = $todayRecords->where('status', AttendanceRecord::STATUS_HADIR)->count();
        $terlambatToday = $todayRecords->where('status', AttendanceRecord::STATUS_TERLAMBAT)->count();
        $izinToday = $todayRecords->where('status', AttendanceRecord::STATUS_IZIN)->count();
        $sakitToday = $todayRecords->where('status', AttendanceRecord::STATUS_SAKIT)->count();
        $alpaToday = $todayRecords->where('status', AttendanceRecord::STATUS_ALPA)->count();
        $totalHadirToday = $hadirToday + $terlambatToday;

        // Active ongoing sessions
        $activeSessions = AttendanceSession::with(['schoolClass', 'subject', 'teacher.user'])
            ->where('status', 'active')
            ->latest('opened_at')
            ->take(5)
            ->get();

        // Recent attendance activity
        $recentRecords = AttendanceRecord::with(['student.user', 'student.currentClass', 'attendanceSession.subject'])
            ->latest('scanned_at')
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'hadirToday',
            'terlambatToday',
            'izinToday',
            'sakitToday',
            'alpaToday',
            'totalHadirToday',
            'todaySessions',
            'activeSessions',
            'recentRecords'
        ));
    }

    /**
     * Students Management
     */
    public function students(Request $request)
    {
        $query = Student::with(['user', 'currentClass', 'activeQrToken']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $students = $query->latest()->paginate(15)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function createStudent()
    {
        $classes = SchoolClass::orderBy('name')->get();
        return view('admin.students.create', compact('classes'));
    }

    public function storeStudent(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'nisn' => ['required', 'string', 'max:50', 'unique:students,nisn'],
            'gender' => ['required', 'in:L,P'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['nisn'] . '@siswa.sipres.id',
                'username' => $validated['nisn'],
                'role' => 'student',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'nis' => $validated['nisn'],
                'nisn' => $validated['nisn'],
                'gender' => $validated['gender'],
                'class_id' => $validated['class_id'],
                'status' => 'active',
            ]);

            // Create initial active QR token
            QrToken::generateForStudent($student->id);

            AuditLog::log('tambah_siswa', "Menambahkan siswa baru {$student->name} (NISN: {$student->nisn})", 'Student', (string)$student->id);
        });

        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil ditambahkan dan QR Code telah dibuat.');
    }

    public function editStudent(Student $student)
    {
        $classes = SchoolClass::orderBy('name')->get();
        return view('admin.students.edit', compact('student', 'classes'));
    }

    public function updateStudent(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'nisn' => ['required', 'string', 'max:50', 'unique:students,nisn,' . $student->id],
            'gender' => ['required', 'in:L,P'],
        ]);

        DB::transaction(function () use ($validated, $student) {
            $student->user->update([
                'name' => $validated['name'],
                'email' => $validated['nisn'] . '@siswa.sipres.id',
                'username' => $validated['nisn'],
            ]);

            $student->update([
                'nis' => $validated['nisn'],
                'nisn' => $validated['nisn'],
                'gender' => $validated['gender'],
                'class_id' => $validated['class_id'],
            ]);

            AuditLog::log('edit_siswa', "Memperbarui data siswa {$student->name} (NISN: {$student->nisn})", 'Student', (string)$student->id);
        });

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function regenerateQr(Student $student)
    {
        $oldToken = $student->activeQrToken?->token;
        $newToken = QrToken::generateForStudent($student->id);

        AuditLog::log(
            'regenerate_qr',
            "Regenerasi QR Code siswa {$student->name} (Token lama dinonaktifkan)",
            'Student',
            (string)$student->id
        );

        return back()->with('success', "QR Code untuk {$student->name} berhasil diperbarui. QR lama telah tidak berlaku.");
    }

    /**
     * Print Student QR Card (Single)
     */
    public function printSingleQr(Student $student, QrCodeService $qrService)
    {
        $token = $student->activeQrToken;
        if (!$token) {
            $token = QrToken::generateForStudent($student->id);
        }

        $qrSvg = $qrService->generateSvg($token->token, 200);

        return view('admin.qr-cards.single', compact('student', 'token', 'qrSvg'));
    }

    /**
     * Batch Print QR Cards
     */
    public function batchPrintQrCards(Request $request, QrCodeService $qrService)
    {
        $classId = $request->input('class_id');
        $classes = SchoolClass::orderBy('name')->get();

        $students = collect();
        if ($classId) {
            $students = Student::with(['user', 'currentClass', 'activeQrToken'])
                ->where('class_id', $classId)
                ->where('status', 'active')
                ->orderBy('nis')
                ->get();

            // Ensure every student has active token
            foreach ($students as $student) {
                if (!$student->activeQrToken) {
                    QrToken::generateForStudent($student->id);
                    $student->load('activeQrToken');
                }
                $student->qrSvg = $qrService->generateSvg($student->activeQrToken->token, 160);
            }
        }

        return view('admin.qr-cards.index', compact('classes', 'students', 'classId'));
    }

    /**
     * Export Students to Excel (.xlsx)
     */
    public function exportStudents(Request $request, ExcelService $excel)
    {
        $query = Student::with(['user', 'currentClass', 'activeQrToken']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
        }

        $students = $query->orderBy('class_id')->get();

        $headers = ['No', 'Nama Siswa', 'Kelas', 'NISN', 'Jenis Kelamin'];
        $rows = [];
        $no = 1;

        foreach ($students as $s) {
            $rows[] = [
                $no++,
                $s->user?->name ?? 'Siswa',
                $s->currentClass?->name ?? '-',
                (string)($s->nisn ?: $s->nis),
                $s->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            ];
        }

        AuditLog::log('export_siswa_excel', 'Mengekspor data siswa ke file Excel (.xlsx)');

        return $excel->export('data_siswa_smpn2mijen_' . date('Ymd_His') . '.xlsx', $headers, $rows, 'Data Siswa');
    }

    /**
     * Download Excel (.xlsx) Template for Students Import
     */
    public function downloadStudentTemplate(ExcelService $excel)
    {
        $headers = ['Nama Siswa', 'Kelas', 'NISN', 'Jenis Kelamin'];
        $samples = [
            ['Ahmad Fauzi', '7A', '0081234567', 'L'],
            ['Siti Nurhaliza', '7A', '0087654321', 'P'],
            ['Bagus Prasetyo', '8B', '0089988776', 'L'],
        ];

        return $excel->downloadTemplate('template_import_siswa.xlsx', $headers, $samples, 'Template Siswa');
    }

    /**
     * Import Students via Excel (.xlsx)
     */
    public function importStudents(Request $request, ExcelService $excel)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $file = $request->file('excel_file');
        $rows = $excel->readRows($file->getRealPath());

        if (empty($rows)) {
            return back()->with('error', 'File Excel kosong atau tidak memiliki baris data yang valid.');
        }

        $inserted = 0;
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $rowNum++;

                $name = trim($row['nama_siswa'] ?? ($row['nama'] ?? ($row['name'] ?? '')));
                $className = trim($row['kelas'] ?? ($row['class'] ?? ($row['class_name'] ?? '')));
                $nisn = trim($row['nisn'] ?? ($row['nis'] ?? ''));
                $genderRaw = strtoupper(trim($row['jenis_kelamin'] ?? ($row['gender'] ?? ($row['jk'] ?? 'L'))));
                
                $gender = 'L';
                if (str_starts_with($genderRaw, 'P') || $genderRaw === 'PEREMPUAN') {
                    $gender = 'P';
                }

                if (empty($name) || empty($nisn)) {
                    $errors[] = "Baris {$rowNum}: Kolom Nama Siswa dan NISN wajib diisi.";
                    continue;
                }

                $class = null;
                if (!empty($className)) {
                    $class = SchoolClass::where('name', $className)->first();
                    if (!$class) {
                        $class = SchoolClass::firstOrCreate(
                            ['name' => $className],
                            ['level' => preg_match('/^[789]/', $className, $m) ? $m[0] : '7']
                        );
                    }
                }

                if (Student::where('nisn', $nisn)->orWhere('nis', $nisn)->exists()) {
                    $errors[] = "Baris {$rowNum}: Siswa dengan NISN '{$nisn}' sudah terdaftar.";
                    continue;
                }

                $email = $nisn . '@siswa.sipres.id';

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'username' => $nisn,
                    'role' => 'student',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]);

                $student = Student::create([
                    'user_id' => $user->id,
                    'nis' => $nisn,
                    'nisn' => $nisn,
                    'gender' => $gender,
                    'class_id' => $class?->id,
                    'status' => 'active',
                ]);

                QrToken::generateForStudent($student->id);
                $inserted++;
            }

            DB::commit();

            AuditLog::log('import_siswa_excel', "Import data siswa Excel berhasil. {$inserted} siswa baru dimasukkan.");

            $message = "Berhasil mengimpor {$inserted} data siswa dari Excel.";
            if (count($errors) > 0) {
                $message .= " Beberapa baris dilewati (" . count($errors) . "): " . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= ' ...dan lainnya.';
                }
            }

            return redirect()->route('admin.students.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Teachers Management
     */
    public function teachers(Request $request)
    {
        $query = Teacher::with(['user', 'homeroomClass']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        if ($gender = $request->input('gender')) {
            $query->where('gender', $gender);
        }

        $teachers = $query->latest()->paginate(15)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();

        return view('admin.teachers.index', compact('teachers', 'classes'));
    }

    public function storeTeacher(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'nip' => ['nullable', 'string', 'max:50', 'unique:teachers,nip'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'gender' => ['required', 'in:L,P'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
            'homeroom_class_id' => ['nullable', 'exists:classes,id'],
        ]);

        DB::transaction(function () use ($validated) {
            // Generate username if empty
            $username = $validated['username'] ?? null;
            if (empty($username)) {
                $base = !empty($validated['nip']) ? 'guru_' . $validated['nip'] : Str::slug(explode(' ', $validated['name'])[0]);
                $username = $base;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $base . $counter;
                    $counter++;
                }
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $username,
                'role' => 'teacher',
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password'] ?? 'password'),
                'is_active' => true,
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'] ?: null,
                'gender' => $validated['gender'],
            ]);

            // Handle homeroom assignment
            if (!empty($validated['homeroom_class_id'])) {
                SchoolClass::where('homeroom_teacher_id', $teacher->id)->update(['homeroom_teacher_id' => null]);
                SchoolClass::where('id', $validated['homeroom_class_id'])->update(['homeroom_teacher_id' => $teacher->id]);
            }

            AuditLog::log('tambah_guru', "Menambahkan data guru baru {$user->name} (NIP: " . ($teacher->nip ?? '-') . ")", 'Teacher', (string)$teacher->id);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function updateTeacher(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $teacher->user_id],
            'nip' => ['nullable', 'string', 'max:50', 'unique:teachers,nip,' . $teacher->id],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $teacher->user_id],
            'gender' => ['required', 'in:L,P'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable'],
            'homeroom_class_id' => ['nullable', 'exists:classes,id'],
        ]);

        DB::transaction(function () use ($validated, $request, $teacher) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'phone' => $validated['phone'] ?? null,
                'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : true,
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $teacher->user->update($userData);

            $teacher->update([
                'nip' => $validated['nip'] ?: null,
                'gender' => $validated['gender'],
            ]);

            // Homeroom class update
            SchoolClass::where('homeroom_teacher_id', $teacher->id)->update(['homeroom_teacher_id' => null]);
            if (!empty($validated['homeroom_class_id'])) {
                SchoolClass::where('id', $validated['homeroom_class_id'])->update(['homeroom_teacher_id' => $teacher->id]);
            }

            AuditLog::log('edit_guru', "Memperbarui data guru {$teacher->user->name}", 'Teacher', (string)$teacher->id);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroyTeacher(Teacher $teacher)
    {
        $scheduleCount = $teacher->schedules()->count();
        $sessionCount = $teacher->attendanceSessions()->count();

        if ($scheduleCount > 0 || $sessionCount > 0) {
            return back()->with('error', "Guru {$teacher->name} tidak dapat dihapus karena memiliki {$scheduleCount} jadwal pelajaran dan {$sessionCount} sesi presensi. Anda dapat menonaktifkan akunnya pada menu edit.");
        }

        DB::transaction(function () use ($teacher) {
            $name = $teacher->name;
            // Detach homeroom
            SchoolClass::where('homeroom_teacher_id', $teacher->id)->update(['homeroom_teacher_id' => null]);
            $user = $teacher->user;
            $teacher->delete();
            $user?->delete();

            AuditLog::log('hapus_guru', "Menghapus data guru {$name}", 'Teacher');
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil dihapus.');
    }

    /**
     * Export Teachers to Excel (.xlsx)
     */
    public function exportTeachers(Request $request, ExcelService $excel)
    {
        $query = Teacher::with(['user', 'homeroomClass']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        if ($gender = $request->input('gender')) {
            $query->where('gender', $gender);
        }

        $teachers = $query->latest('id')->get();

        $headers = ['No', 'NIP', 'Nama Guru & Gelar', 'Jenis Kelamin', 'Email', 'No. Telepon / WA', 'Username', 'Wali Kelas', 'Status Akun'];
        $rows = [];
        $no = 1;

        foreach ($teachers as $t) {
            $rows[] = [
                $no++,
                (string)($t->nip ?? '-'),
                $t->user?->name ?? 'Guru',
                $t->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                $t->user?->email ?? '-',
                (string)($t->user?->phone ?? '-'),
                $t->user?->username ?? '-',
                $t->homeroomClass ? "Wali Kelas {$t->homeroomClass->name}" : '-',
                ($t->user?->is_active ?? true) ? 'Aktif' : 'Non-Aktif',
            ];
        }

        AuditLog::log('export_guru_excel', 'Mengekspor data guru ke file Excel (.xlsx)');

        return $excel->export('data_guru_smpn2mijen_' . date('Ymd_His') . '.xlsx', $headers, $rows, 'Data Guru');
    }

    /**
     * Download Excel (.xlsx) Template for Teachers Import
     */
    public function downloadTeacherTemplate(ExcelService $excel)
    {
        $headers = ['nip', 'name', 'gender', 'email', 'phone', 'username', 'homeroom_class'];
        $samples = [
            ['198503152010011015', 'Bambang Supriyanto, S.Pd.', 'L', 'bambang@sipres.test', '081234567899', 'bambang', '7A'],
            ['199104202015022003', 'Endah Pratiwi, M.Pd.', 'P', 'endah@sipres.test', '081234567898', 'endah', ''],
            ['198807122011011007', 'Ahmad Dahlan, S.Kom.', 'L', 'ahmad.dahlan@sipres.test', '081234567897', 'dahlan', '8A'],
        ];

        return $excel->downloadTemplate('template_import_guru.xlsx', $headers, $samples, 'Template Guru');
    }

    /**
     * Import Teachers via Excel (.xlsx)
     */
    public function importTeachers(Request $request, ExcelService $excel)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $file = $request->file('excel_file');
        $rows = $excel->readRows($file->getRealPath());

        if (empty($rows)) {
            return back()->with('error', 'File Excel kosong atau format tidak valid.');
        }

        $inserted = 0;
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $rowNum++;

                $nip = trim($row['nip'] ?? '');
                $name = trim($row['name'] ?? ($row['nama'] ?? ''));
                $gender = strtoupper(trim($row['gender'] ?? ($row['jenis_kelamin'] ?? 'L')));
                $email = trim($row['email'] ?? '');
                $phone = trim($row['phone'] ?? ($row['telepon'] ?? ''));
                $username = trim($row['username'] ?? '');
                $homeroomClass = trim($row['homeroom_class'] ?? ($row['wali_kelas'] ?? ''));

                if (empty($name) || empty($email)) {
                    $errors[] = "Baris {$rowNum}: Kolom Nama dan Email wajib diisi.";
                    continue;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Baris {$rowNum}: Format email '{$email}' tidak valid.";
                    continue;
                }

                if (User::where('email', $email)->exists()) {
                    $errors[] = "Baris {$rowNum}: Email '{$email}' sudah terdaftar.";
                    continue;
                }

                if (!empty($nip) && Teacher::where('nip', $nip)->exists()) {
                    $errors[] = "Baris {$rowNum}: NIP '{$nip}' sudah terdaftar.";
                    continue;
                }

                if (empty($username)) {
                    $baseUsername = !empty($nip) ? 'guru_' . $nip : Str::slug(explode(' ', $name)[0]);
                    $username = $baseUsername;
                }
                $counter = 1;
                $originalUsername = $username;
                while (User::where('username', $username)->exists()) {
                    $username = $originalUsername . $counter;
                    $counter++;
                }

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'username' => $username,
                    'role' => 'teacher',
                    'phone' => $phone ?: null,
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]);

                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'nip' => $nip ?: null,
                    'gender' => in_array($gender, ['L', 'P']) ? $gender : 'L',
                ]);

                if (!empty($homeroomClass)) {
                    $cls = SchoolClass::where('name', $homeroomClass)->first();
                    if ($cls) {
                        SchoolClass::where('homeroom_teacher_id', $teacher->id)->update(['homeroom_teacher_id' => null]);
                        $cls->update(['homeroom_teacher_id' => $teacher->id]);
                    }
                }

                $inserted++;
            }

            DB::commit();

            AuditLog::log('import_guru_excel', "Import data guru Excel berhasil. {$inserted} data guru baru dimasukkan.");

            $message = "Berhasil mengimpor {$inserted} data guru dari Excel.";
            if (count($errors) > 0) {
                $message .= " Beberapa baris dilewati (" . count($errors) . "): " . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= ' ...dan lainnya.';
                }
            }

            return redirect()->route('admin.teachers.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Classes Management
     */
    public function classes()
    {
        $classes = SchoolClass::with(['homeroomTeacher.user', 'students'])->orderBy('name')->get();
        $teachers = Teacher::with('user')->get();
        return view('admin.classes.index', compact('classes', 'teachers'));
    }

    public function storeClass(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:classes,name'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $name = strtoupper(trim($validated['name']));
        $level = '7';
        if (preg_match('/^[789]/', $name, $m)) {
            $level = $m[0];
        }

        $class = SchoolClass::create([
            'name' => $name,
            'level' => $level,
            'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?: null,
        ]);

        AuditLog::log('tambah_kelas', "Menambahkan kelas {$class->name}", 'SchoolClass', (string)$class->id);

        return redirect()->route('admin.classes.index')->with('success', "Kelas {$class->name} berhasil ditambahkan.");
    }

    public function updateClass(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:classes,name,' . $class->id],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $name = strtoupper(trim($validated['name']));
        $level = '7';
        if (preg_match('/^[789]/', $name, $m)) {
            $level = $m[0];
        }

        $class->update([
            'name' => $name,
            'level' => $level,
            'homeroom_teacher_id' => $validated['homeroom_teacher_id'] ?: null,
        ]);

        AuditLog::log('edit_kelas', "Memperbarui kelas {$class->name}", 'SchoolClass', (string)$class->id);

        return redirect()->route('admin.classes.index')->with('success', "Kelas {$class->name} berhasil diperbarui.");
    }

    public function destroyClass(SchoolClass $class)
    {
        $studentCount = $class->students()->count();
        if ($studentCount > 0) {
            return back()->with('error', "Kelas {$class->name} tidak dapat dihapus karena masih memiliki {$studentCount} siswa terdaftar.");
        }

        $name = $class->name;
        $class->delete();

        AuditLog::log('hapus_kelas', "Menghapus kelas {$name}", 'SchoolClass');

        return redirect()->route('admin.classes.index')->with('success', "Kelas {$name} berhasil dihapus.");
    }

    public function downloadClassTemplate(ExcelService $excel)
    {
        $headers = ['Nama Kelas'];
        $samples = [
            ['7A'],
            ['7B'],
            ['7C'],
            ['8A'],
            ['8B'],
            ['8C'],
            ['9A'],
            ['9B'],
            ['9C'],
        ];

        return $excel->downloadTemplate('template_import_kelas.xlsx', $headers, $samples, 'Template Kelas');
    }

    public function importClasses(Request $request, ExcelService $excel)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $file = $request->file('excel_file');
        $rows = $excel->readRows($file->getRealPath());

        if (empty($rows)) {
            return back()->with('error', 'File Excel kosong atau tidak memiliki baris data yang valid.');
        }

        $inserted = 0;
        $errors = [];
        $rowNum = 1;

        foreach ($rows as $row) {
            $rowNum++;
            $name = strtoupper(trim($row['nama_kelas'] ?? ($row['nama'] ?? ($row['kelas'] ?? ($row['name'] ?? ($row['class'] ?? ''))))));

            if (empty($name)) {
                continue;
            }

            if (SchoolClass::where('name', $name)->exists()) {
                $errors[] = "Baris {$rowNum}: Kelas '{$name}' sudah ada.";
                continue;
            }

            $level = '7';
            if (preg_match('/^[789]/', $name, $m)) {
                $level = $m[0];
            }

            SchoolClass::create([
                'name' => $name,
                'level' => $level,
            ]);

            $inserted++;
        }

        AuditLog::log('import_kelas_excel', "Import data kelas Excel berhasil. {$inserted} kelas baru ditambahkan.");

        $message = "Berhasil mengimpor {$inserted} kelas baru dari Excel.";
        if (count($errors) > 0) {
            $message .= " Beberapa kelas dilewati (" . count($errors) . "): " . implode('; ', array_slice($errors, 0, 3));
        }

        return redirect()->route('admin.classes.index')->with('success', $message);
    }

    /**
     * Subjects Management (Kurikulum & Mata Pelajaran)
     */
    public function subjects(Request $request)
    {
        $query = Subject::withCount(['schedules', 'attendanceSessions']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $subjects = $query->orderBy('code')->paginate(15)->withQueryString();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function storeSubject(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $subject = Subject::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
        ]);

        AuditLog::log('tambah_mapel', "Menambahkan mata pelajaran {$subject->name} ({$subject->code})", 'Subject', (string)$subject->id);

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code,' . $subject->id],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $subject->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
        ]);

        AuditLog::log('edit_mapel', "Memperbarui mata pelajaran {$subject->name} ({$subject->code})", 'Subject', (string)$subject->id);

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroySubject(Subject $subject)
    {
        $schedCount = $subject->schedules()->count();
        $sessCount = $subject->attendanceSessions()->count();

        if ($schedCount > 0 || $sessCount > 0) {
            return back()->with('error', "Mata pelajaran {$subject->name} tidak dapat dihapus karena masih digunakan di {$schedCount} jadwal dan {$sessCount} sesi presensi.");
        }

        $name = $subject->name;
        $subject->delete();

        AuditLog::log('hapus_mapel', "Menghapus mata pelajaran {$name}", 'Subject');

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    public function exportSubjects(ExcelService $excel)
    {
        $subjects = Subject::withCount(['schedules', 'attendanceSessions'])->orderBy('code')->get();

        $headers = ['No', 'Kode Mapel', 'Nama Mata Pelajaran', 'Jumlah Jadwal Aktif', 'Total Sesi Presensi'];
        $rows = [];
        $no = 1;

        foreach ($subjects as $s) {
            $rows[] = [
                $no++,
                (string)$s->code,
                $s->name,
                $s->schedules_count,
                $s->attendance_sessions_count,
            ];
        }

        AuditLog::log('export_mapel_excel', 'Mengekspor data mata pelajaran ke file Excel (.xlsx)');

        return $excel->export('mata_pelajaran_smpn2mijen_' . date('Ymd_His') . '.xlsx', $headers, $rows, 'Mata Pelajaran');
    }

    public function downloadSubjectTemplate(ExcelService $excel)
    {
        $headers = ['code', 'name'];
        $samples = [
            ['IPA-7', 'Ilmu Pengetahuan Alam Kelas 7'],
            ['MTK-7', 'Matematika Kelas 7'],
            ['BIG-7', 'Bahasa Inggris Kelas 7'],
            ['BIN-7', 'Bahasa Indonesia Kelas 7'],
        ];

        return $excel->downloadTemplate('template_import_mata_pelajaran.xlsx', $headers, $samples, 'Template Mapel');
    }

    public function importSubjects(Request $request, ExcelService $excel)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $file = $request->file('excel_file');
        $rows = $excel->readRows($file->getRealPath());

        if (empty($rows)) {
            return back()->with('error', 'File Excel kosong atau format tidak valid.');
        }

        $inserted = 0;
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $rowNum++;

                $code = strtoupper(trim($row['code'] ?? ($row['kode'] ?? '')));
                $name = trim($row['name'] ?? ($row['nama'] ?? ''));

                if (empty($code) || empty($name)) {
                    $errors[] = "Baris {$rowNum}: Kode dan Nama Mapel wajib diisi.";
                    continue;
                }

                if (Subject::where('code', $code)->exists()) {
                    $errors[] = "Baris {$rowNum}: Kode Mapel '{$code}' sudah ada.";
                    continue;
                }

                Subject::create([
                    'code' => $code,
                    'name' => $name,
                ]);

                $inserted++;
            }

            DB::commit();

            AuditLog::log('import_mapel_excel', "Import data mata pelajaran Excel berhasil. {$inserted} mapel baru dimasukkan.");

            $message = "Berhasil mengimpor {$inserted} mata pelajaran dari Excel.";
            if (count($errors) > 0) {
                $message .= " Beberapa baris dilewati (" . count($errors) . "): " . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) $message .= ' ...dan lainnya.';
            }

            return redirect()->route('admin.subjects.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Schedules Management (Jadwal Pelajaran)
     */
    public function schedules(Request $request)
    {
        $query = Schedule::with(['teacher.user', 'schoolClass', 'subject', 'schoolYear']);

        if ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
        }

        if ($day = $request->input('day')) {
            $query->where('day_of_week', $day);
        }

        $schedules = $query->orderByRaw("CASE day_of_week WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        $classes = SchoolClass::orderBy('name')->get();
        $teachers = Teacher::with('user')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.schedules.index', compact('schedules', 'classes', 'teachers', 'subjects'));
    }

    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'day_of_week' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:60'],
        ]);

        $activeYear = SchoolYear::where('is_active', true)->first() ?? SchoolYear::latest()->first();

        // Conflict check: Teacher double booking
        $teacherConflict = Schedule::where('teacher_id', $validated['teacher_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhere(function ($sub) use ($validated) {
                      $sub->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                  });
            })->exists();

        if ($teacherConflict) {
            return back()->withInput()->with('error', 'Konflik jadwal: Guru tersebut sudah memiliki jadwal mengajar di kelas lain pada hari dan jam yang sama.');
        }

        // Conflict check: Class double booking
        $classConflict = Schedule::where('class_id', $validated['class_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhere(function ($sub) use ($validated) {
                      $sub->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                  });
            })->exists();

        if ($classConflict) {
            return back()->withInput()->with('error', 'Konflik jadwal: Kelas tersebut sudah memiliki jadwal pelajaran lain pada hari dan jam yang sama.');
        }

        $schedule = Schedule::create([
            'school_year_id' => $activeYear?->id ?? 1,
            'class_id' => $validated['class_id'],
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'late_tolerance_minutes' => $validated['late_tolerance_minutes'],
        ]);

        AuditLog::log('tambah_jadwal', "Menambahkan jadwal baru {$schedule->day_of_week} ({$schedule->start_time} - {$schedule->end_time})", 'Schedule', (string)$schedule->id);

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function updateSchedule(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'day_of_week' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:60'],
        ]);

        // Conflict check excluding current schedule
        $teacherConflict = Schedule::where('id', '!=', $schedule->id)
            ->where('teacher_id', $validated['teacher_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhere(function ($sub) use ($validated) {
                      $sub->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                  });
            })->exists();

        if ($teacherConflict) {
            return back()->withInput()->with('error', 'Konflik jadwal: Guru tersebut sudah memiliki jadwal mengajar di kelas lain pada hari dan jam yang sama.');
        }

        $classConflict = Schedule::where('id', '!=', $schedule->id)
            ->where('class_id', $validated['class_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhere(function ($sub) use ($validated) {
                      $sub->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                  });
            })->exists();

        if ($classConflict) {
            return back()->withInput()->with('error', 'Konflik jadwal: Kelas tersebut sudah memiliki jadwal pelajaran lain pada hari dan jam yang sama.');
        }

        $schedule->update([
            'class_id' => $validated['class_id'],
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'late_tolerance_minutes' => $validated['late_tolerance_minutes'],
        ]);

        AuditLog::log('edit_jadwal', "Memperbarui jadwal pelajaran ID: {$schedule->id}", 'Schedule', (string)$schedule->id);

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function destroySchedule(Schedule $schedule)
    {
        $sessionCount = $schedule->attendanceSessions()->count();
        if ($sessionCount > 0) {
            return back()->with('error', "Jadwal ini tidak dapat dihapus karena sudah memiliki {$sessionCount} sesi presensi yang tersimpan.");
        }

        $id = $schedule->id;
        $schedule->delete();

        AuditLog::log('hapus_jadwal', "Menghapus jadwal pelajaran ID: {$id}", 'Schedule');

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    public function exportSchedules(Request $request, ExcelService $excel)
    {
        $query = Schedule::with(['teacher.user', 'schoolClass', 'subject']);

        if ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
        }

        if ($day = $request->input('day')) {
            $query->where('day_of_week', $day);
        }

        $schedules = $query->orderByRaw("CASE day_of_week WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('start_time')
            ->get();

        $headers = ['No', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Kelas', 'Kode Mapel', 'Nama Mata Pelajaran', 'Guru Pengajar', 'NIP Guru', 'Toleransi Terlambat (menit)'];
        $rows = [];
        $no = 1;

        foreach ($schedules as $sc) {
            $rows[] = [
                $no++,
                $sc->day_of_week,
                substr($sc->start_time, 0, 5),
                substr($sc->end_time, 0, 5),
                $sc->schoolClass?->name ?? '-',
                (string)($sc->subject?->code ?? '-'),
                $sc->subject?->name ?? '-',
                $sc->teacher?->user?->name ?? '-',
                (string)($sc->teacher?->nip ?? '-'),
                $sc->late_tolerance_minutes,
            ];
        }

        AuditLog::log('export_jadwal_excel', 'Mengekspor jadwal pelajaran ke file Excel (.xlsx)');

        return $excel->export('jadwal_pelajaran_smpn2mijen_' . date('Ymd_His') . '.xlsx', $headers, $rows, 'Jadwal Pelajaran');
    }

    public function downloadScheduleTemplate(ExcelService $excel)
    {
        $headers = ['class_name', 'subject_code', 'teacher_nip_or_email', 'day_of_week', 'start_time', 'end_time', 'late_tolerance_minutes'];
        $samples = [
            ['8A', 'IPA-8', '198503152010011012', 'Senin', '07:30', '09:00', '15'],
            ['8B', 'MTK-8', 'guru@sipres.test', 'Selasa', '09:15', '10:45', '15'],
            ['7A', 'BIG-7', '198708222011012015', 'Rabu', '10:45', '12:15', '15'],
        ];

        return $excel->downloadTemplate('template_import_jadwal.xlsx', $headers, $samples, 'Template Jadwal');
    }

    public function importSchedules(Request $request, ExcelService $excel)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $file = $request->file('excel_file');
        $rows = $excel->readRows($file->getRealPath());

        if (empty($rows)) {
            return back()->with('error', 'File Excel kosong atau format tidak valid.');
        }

        $activeYear = SchoolYear::where('is_active', true)->first() ?? SchoolYear::latest()->first();
        $inserted = 0;
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $rowNum++;

                $className = trim($row['class_name'] ?? ($row['kelas'] ?? ''));
                $subjectCode = strtoupper(trim($row['subject_code'] ?? ($row['kode_mapel'] ?? '')));
                $teacherIden = trim($row['teacher_nip_or_email'] ?? ($row['guru'] ?? ''));
                $day = ucfirst(strtolower(trim($row['day_of_week'] ?? ($row['hari'] ?? 'Senin'))));
                $start = trim($row['start_time'] ?? ($row['jam_mulai'] ?? ''));
                $end = trim($row['end_time'] ?? ($row['jam_selesai'] ?? ''));
                $tolerance = intval($row['late_tolerance_minutes'] ?? ($row['toleransi'] ?? 15));

                if (empty($className) || empty($subjectCode) || empty($teacherIden) || empty($start) || empty($end)) {
                    $errors[] = "Baris {$rowNum}: Kolom Kelas, Kode Mapel, Guru, Jam Mulai, dan Jam Selesai wajib diisi.";
                    continue;
                }

                $class = SchoolClass::where('name', $className)->first();
                if (!$class) {
                    $errors[] = "Baris {$rowNum}: Kelas '{$className}' tidak ditemukan.";
                    continue;
                }

                $subject = Subject::where('code', $subjectCode)->first();
                if (!$subject) {
                    $errors[] = "Baris {$rowNum}: Mata pelajaran dengan kode '{$subjectCode}' tidak ditemukan.";
                    continue;
                }

                $teacher = Teacher::where('nip', $teacherIden)
                    ->orWhereHas('user', fn($uq) => $uq->where('email', $teacherIden)->orWhere('username', $teacherIden))
                    ->first();
                if (!$teacher) {
                    $errors[] = "Baris {$rowNum}: Guru dengan NIP/Email/Username '{$teacherIden}' tidak ditemukan.";
                    continue;
                }

                // Check conflict
                $conflict = Schedule::where('teacher_id', $teacher->id)
                    ->where('day_of_week', $day)
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('start_time', [$start, $end])
                          ->orWhereBetween('end_time', [$start, $end]);
                    })->exists();

                if ($conflict) {
                    $errors[] = "Baris {$rowNum}: Guru '{$teacher->name}' mengalami konflik jadwal pada {$day} {$start}-{$end}.";
                    continue;
                }

                Schedule::create([
                    'school_year_id' => $activeYear?->id ?? 1,
                    'class_id' => $class->id,
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'day_of_week' => $day,
                    'start_time' => $start,
                    'end_time' => $end,
                    'late_tolerance_minutes' => $tolerance,
                ]);

                $inserted++;
            }

            DB::commit();

            AuditLog::log('import_jadwal_excel', "Import jadwal pelajaran Excel berhasil. {$inserted} jadwal baru dimasukkan.");

            $message = "Berhasil mengimpor {$inserted} jadwal pelajaran dari Excel.";
            if (count($errors) > 0) {
                $message .= " Beberapa baris dilewati (" . count($errors) . "): " . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) $message .= ' ...dan lainnya.';
            }

            return redirect()->route('admin.schedules.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Attendance Recap & Report with Filters
     */
    public function attendance(Request $request)
    {
        $query = AttendanceRecord::with(['student.user', 'student.currentClass', 'attendanceSession.subject', 'attendanceSession.teacher.user', 'attendanceSession.schoolClass']);

        if ($startDate = $request->input('start_date')) {
            $query->whereHas('attendanceSession', fn($q) => $q->whereDate('date', '>=', $startDate));
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereHas('attendanceSession', fn($q) => $q->whereDate('date', '<=', $endDate));
        }

        if ($classId = $request->input('class_id')) {
            $query->whereHas('attendanceSession', fn($q) => $q->where('class_id', $classId));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('student', function ($sq) use ($search) {
                $sq->where('nis', 'like', "%{$search}%")
                   ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $records = (clone $query)->latest('id')->paginate(20)->withQueryString();

        // Summary statistics for current filter
        $totalHadir = (clone $query)->where('status', AttendanceRecord::STATUS_HADIR)->count();
        $totalTerlambat = (clone $query)->where('status', AttendanceRecord::STATUS_TERLAMBAT)->count();
        $totalIzin = (clone $query)->where('status', AttendanceRecord::STATUS_IZIN)->count();
        $totalSakit = (clone $query)->where('status', AttendanceRecord::STATUS_SAKIT)->count();
        $totalAlpa = (clone $query)->where('status', AttendanceRecord::STATUS_ALPA)->count();

        $classes = SchoolClass::orderBy('name')->get();

        return view('admin.attendance.index', compact(
            'records',
            'classes',
            'totalHadir',
            'totalTerlambat',
            'totalIzin',
            'totalSakit',
            'totalAlpa'
        ));
    }

    /**
     * Export Attendance to Excel (.xlsx)
     */
    public function exportAttendance(Request $request, ExcelService $excel)
    {
        $query = AttendanceRecord::with(['student.user', 'student.currentClass', 'attendanceSession.subject', 'attendanceSession.teacher.user', 'attendanceSession.schoolClass']);

        if ($startDate = $request->input('start_date')) {
            $query->whereHas('attendanceSession', fn($q) => $q->whereDate('date', '>=', $startDate));
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereHas('attendanceSession', fn($q) => $q->whereDate('date', '<=', $endDate));
        }
        if ($classId = $request->input('class_id')) {
            $query->whereHas('attendanceSession', fn($q) => $q->where('class_id', $classId));
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $records = $query->latest('id')->get();

        $headers = [
            'No',
            'Tanggal',
            'Waktu Presensi',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Mata Pelajaran',
            'Guru Pengajar',
            'Status Kehadiran',
            'Metode',
            'Catatan',
        ];

        $rows = [];
        $no = 1;

        foreach ($records as $r) {
            $rows[] = [
                $no++,
                $r->attendanceSession?->date?->format('Y-m-d') ?? '-',
                $r->scanned_at ? $r->scanned_at->format('H:i:s') : '-',
                (string)($r->student?->nis ?? '-'),
                $r->student?->name ?? ($r->student?->user?->name ?? '-'),
                $r->student?->currentClass?->name ?? ($r->attendanceSession?->schoolClass?->name ?? '-'),
                $r->attendanceSession?->subject?->name ?? '-',
                $r->attendanceSession?->teacher?->name ?? ($r->attendanceSession?->teacher?->user?->name ?? '-'),
                strtoupper($r->status),
                $r->method === 'qr' ? 'Scan QR' : 'Manual',
                $r->notes ?? '-',
            ];
        }

        AuditLog::log('export_presensi_excel', 'Mengekspor rekap presensi ke file Excel (.xlsx)');

        return $excel->export('rekap_presensi_' . date('Y-m-d_His') . '.xlsx', $headers, $rows, 'Rekap Presensi');
    }

    /**
     * Audit Logs
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }

    /**
     * School Settings
     */
    public function settings()
    {
        $settings = Setting::pluck('value', 'key')->all();
        $schoolYear = SchoolYear::getActive();
        return view('admin.settings.index', compact('settings', 'schoolYear'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_npsn' => ['required', 'string', 'max:50'],
            'school_address' => ['required', 'string'],
            'school_phone' => ['nullable', 'string', 'max:50'],
            'school_email' => ['nullable', 'email', 'max:100'],
            'default_late_tolerance' => ['required', 'integer', 'min:1', 'max:120'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, (string)$value);
        }

        AuditLog::log('update_settings', 'Memperbarui pengaturan sekolah');

        return back()->with('success', 'Pengaturan sekolah berhasil disimpan.');
    }
}
