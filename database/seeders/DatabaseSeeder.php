<?php

namespace Database\Seeders;

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
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Settings
        Setting::set('school_name', 'SMP NEGERI 2 MIJEN');
        Setting::set('school_npsn', '20317892');
        Setting::set('school_address', 'Jl. Raya Mijen No. 45, Kec. Mijen, Demak, Jawa Tengah');
        Setting::set('school_phone', '(0291) 685123');
        Setting::set('school_email', 'smpn2mijen@sekolah.id');
        Setting::set('default_late_tolerance', '15');

        // 2. School Year
        $schoolYear = SchoolYear::create([
            'name' => '2025/2026',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        // 3. Subjects
        $subjectsData = [
            ['code' => 'INF-8', 'name' => 'Informatika'],
            ['code' => 'MAT-8', 'name' => 'Matematika'],
            ['code' => 'IPA-8', 'name' => 'Ilmu Pengetahuan Alam'],
            ['code' => 'BIN-8', 'name' => 'Bahasa Indonesia'],
            ['code' => 'BIG-8', 'name' => 'Bahasa Inggris'],
            ['code' => 'IPS-8', 'name' => 'Ilmu Pengetahuan Sosial'],
            ['code' => 'PKN-8', 'name' => 'Pendidikan Pancasila'],
            ['code' => 'PJK-8', 'name' => 'Pendidikan Jasmani & Olahraga'],
            ['code' => 'SNB-8', 'name' => 'Seni Budaya'],
        ];

        $subjects = [];
        foreach ($subjectsData as $s) {
            $subjects[$s['code']] = Subject::create($s);
        }

        // 4. Admin User
        $admin = User::create([
            'name' => 'Administrator SIPRES',
            'email' => 'admin@sipres.test',
            'username' => 'admin',
            'role' => 'admin',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        AuditLog::log('system_init', 'Inisialisasi sistem SIPRES SMP NEGERI 2 MIJEN', 'User', (string)$admin->id, $admin->id);

        // 5. Teachers
        $teachersData = [
            [
                'name' => 'Budi Santoso, S.Pd.',
                'email' => 'guru@sipres.test',
                'username' => 'guru',
                'nip' => '198503152010011012',
                'gender' => 'L',
                'phone' => '081234567891',
            ],
            [
                'name' => 'Siti Aminah, M.Pd.',
                'email' => 'siti@sipres.test',
                'username' => 'siti',
                'nip' => '198708222011012015',
                'gender' => 'P',
                'phone' => '081234567892',
            ],
            [
                'name' => 'Drs. Hendro Wibowo',
                'email' => 'hendro@sipres.test',
                'username' => 'hendro',
                'nip' => '197605101999031004',
                'gender' => 'L',
                'phone' => '081234567893',
            ],
            [
                'name' => 'Ratna Dewi, S.Pd.',
                'email' => 'ratna@sipres.test',
                'username' => 'ratna',
                'nip' => '199001122015022008',
                'gender' => 'P',
                'phone' => '081234567894',
            ],
        ];

        $teachers = [];
        foreach ($teachersData as $tData) {
            $user = User::create([
                'name' => $tData['name'],
                'email' => $tData['email'],
                'username' => $tData['username'],
                'role' => 'teacher',
                'phone' => $tData['phone'],
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);

            $teachers[$tData['username']] = Teacher::create([
                'user_id' => $user->id,
                'nip' => $tData['nip'],
                'gender' => $tData['gender'],
            ]);
        }

        // 6. Classes
        $classesData = [
            ['name' => '7A', 'level' => '7', 'homeroom' => 'ratna'],
            ['name' => '7B', 'level' => '7', 'homeroom' => null],
            ['name' => '8A', 'level' => '8', 'homeroom' => 'guru'],
            ['name' => '8B', 'level' => '8', 'homeroom' => 'siti'],
            ['name' => '9A', 'level' => '9', 'homeroom' => 'hendro'],
        ];

        $classes = [];
        foreach ($classesData as $c) {
            $classes[$c['name']] = SchoolClass::create([
                'name' => $c['name'],
                'level' => $c['level'],
                'homeroom_teacher_id' => $c['homeroom'] ? $teachers[$c['homeroom']]->id : null,
            ]);
        }

        // 7. Students (Indonesian realistic student names)
        $students8A = [
            'Ahmad Fauzi', 'Anisa Rahmawati', 'Bagas Pratama', 'Citra Lestari',
            'Dimas Setiawan', 'Eka Nuraini', 'Fajar Ramadhan', 'Gita Permata',
            'Hafidz Maulana', 'Indah Kusuma', 'Joko Susilo', 'Kartika Putri',
            'Luthfi Hakim', 'Mega Utami', 'Nabila Syahrani', 'Oki Pratama',
            'Panji Saputra', 'Qori Amelia', 'Rian Hidayat', 'Salsabila Zahra',
            'Taufik Ismail', 'Umar Al Faruq', 'Vina Anggraini', 'Wahyu Nugroho',
            'Xenia Maharani', 'Yoga Aditya', 'Zahra Aulia', 'Bayu Pamungkas',
            'Dhea Novitasari', 'Fikri Haikal', 'Galang Prasetyo', 'Hana Pertiwi',
        ];

        $students8B = [
            'Aditya Nugraha', 'Bella Safitri', 'Candra Wijaya', 'Devi Anggraini',
            'Erik Firmansyah', 'Fitri Handayani', 'Gilang Ramadhan', 'Helma Yuliana',
            'Irfan Maulana', 'Julia Rahayu', 'Kevin Prasetya', 'Laila Sari',
            'Muhammad Rizky', 'Nadia Safira', 'Oktavian Dwi', 'Puspa Indah',
            'Rangga Pratama', 'Siti Maryam', 'Tegar Wibowo', 'Ulfa Damayanti',
            'Vicky Ardiansyah', 'Winda Lestari', 'Yusuf Habibi', 'Zulfa Khoirunnisa',
            'Agus Santoso', 'Bunga Citra', 'Cahyo Utomo', 'Dian Sastro',
            'Eko Prasetyo', 'Farah Diva',
        ];

        $students7A = [
            'Andi Saputra', 'Bintang Kejora', 'Clara Shinta', 'Danu Tirta',
            'Evan Dimas', 'Febri Haryadi', 'Ghani Alamsyah', 'Hesti Purwanti',
            'Ilham Akbar', 'Jessica Mila', 'Kurniawan Dwi', 'Lintang Kemukus',
            'Mochamad Ridwan', 'Niko Alhakim', 'Olivia Zalianty', 'Pandu Dewanata',
            'Rafi Ahmad', 'Sherina Munaf', 'Tommy Kurniawan', 'Utari Dewi',
            'Vino Bastian', 'Wulan Guritno', 'Yansen Indiani', 'Zaskia Gotik',
            'Alif Ba Ta', 'Bagus Kahfi', 'Cantika Abigail', 'Doni Salmanan',
            'Erlangga Satria', 'Fita Anggraini',
        ];

        $nisCounter = 24001;

        // Create 8A students
        $seededStudents8A = [];
        foreach ($students8A as $idx => $name) {
            $isDemoStudent = ($idx === 0);
            $email = $isDemoStudent ? 'student@sipres.test' : 'siswa.' . Str::slug($name, '.') . '@sipres.test';
            $username = $isDemoStudent ? 'student' : 'nis' . $nisCounter;

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'username' => $username,
                'role' => 'student',
                'phone' => '0857' . rand(10000000, 99999999),
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);

            $gender = ($idx % 2 === 0) ? 'L' : 'P';
            $student = Student::create([
                'user_id' => $user->id,
                'nis' => (string) $nisCounter,
                'nisn' => '00' . rand(10000000, 99999999),
                'gender' => $gender,
                'class_id' => $classes['8A']->id,
                'status' => 'active',
            ]);

            // Pivot enrollment
            $student->classes()->attach($classes['8A']->id, ['school_year_id' => $schoolYear->id]);

            // Generate unique QR token
            QrToken::create([
                'student_id' => $student->id,
                'token' => 'STU_' . Str::random(32),
                'is_active' => true,
            ]);

            $seededStudents8A[] = $student;
            $nisCounter++;
        }

        // Create 8B students
        foreach ($students8B as $idx => $name) {
            $user = User::create([
                'name' => $name,
                'email' => 'siswa.' . Str::slug($name, '.') . '@sipres.test',
                'username' => 'nis' . $nisCounter,
                'role' => 'student',
                'phone' => '0857' . rand(10000000, 99999999),
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);

            $gender = ($idx % 2 === 0) ? 'L' : 'P';
            $student = Student::create([
                'user_id' => $user->id,
                'nis' => (string) $nisCounter,
                'nisn' => '00' . rand(10000000, 99999999),
                'gender' => $gender,
                'class_id' => $classes['8B']->id,
                'status' => 'active',
            ]);

            $student->classes()->attach($classes['8B']->id, ['school_year_id' => $schoolYear->id]);

            QrToken::create([
                'student_id' => $student->id,
                'token' => 'STU_' . Str::random(32),
                'is_active' => true,
            ]);

            $nisCounter++;
        }

        // Create 7A students
        foreach ($students7A as $idx => $name) {
            $user = User::create([
                'name' => $name,
                'email' => 'siswa.' . Str::slug($name, '.') . '@sipres.test',
                'username' => 'nis' . $nisCounter,
                'role' => 'student',
                'phone' => '0857' . rand(10000000, 99999999),
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);

            $gender = ($idx % 2 === 0) ? 'L' : 'P';
            $student = Student::create([
                'user_id' => $user->id,
                'nis' => (string) $nisCounter,
                'nisn' => '00' . rand(10000000, 99999999),
                'gender' => $gender,
                'class_id' => $classes['7A']->id,
                'status' => 'active',
            ]);

            $student->classes()->attach($classes['7A']->id, ['school_year_id' => $schoolYear->id]);

            QrToken::create([
                'student_id' => $student->id,
                'token' => 'STU_' . Str::random(32),
                'is_active' => true,
            ]);

            $nisCounter++;
        }

        // 8. Teaching Schedules
        // Days map in Indonesian:
        $dayMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            0 => 'Minggu',
        ];
        $todayIndoDay = $dayMap[Carbon::now()->dayOfWeek] ?? 'Senin';
        if ($todayIndoDay === 'Minggu') {
            $todayIndoDay = 'Senin';
        }

        // Schedule for Pak Budi (Informatika)
        $scheduleBudi8A = Schedule::create([
            'teacher_id' => $teachers['guru']->id,
            'class_id' => $classes['8A']->id,
            'subject_id' => $subjects['INF-8']->id,
            'school_year_id' => $schoolYear->id,
            'day_of_week' => $todayIndoDay, // Ensure it always matches today for seamless testing!
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'late_tolerance_minutes' => 15,
        ]);

        $scheduleBudi8B = Schedule::create([
            'teacher_id' => $teachers['guru']->id,
            'class_id' => $classes['8B']->id,
            'subject_id' => $subjects['INF-8']->id,
            'school_year_id' => $schoolYear->id,
            'day_of_week' => $todayIndoDay,
            'start_time' => '08:45:00',
            'end_time' => '10:15:00',
            'late_tolerance_minutes' => 15,
        ]);

        // Schedules for Ibu Siti (Matematika)
        Schedule::create([
            'teacher_id' => $teachers['siti']->id,
            'class_id' => $classes['8A']->id,
            'subject_id' => $subjects['MAT-8']->id,
            'school_year_id' => $schoolYear->id,
            'day_of_week' => 'Selasa',
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'late_tolerance_minutes' => 15,
        ]);

        // Schedules for Pak Hendro (IPA)
        Schedule::create([
            'teacher_id' => $teachers['hendro']->id,
            'class_id' => $classes['8A']->id,
            'subject_id' => $subjects['IPA-8']->id,
            'school_year_id' => $schoolYear->id,
            'day_of_week' => 'Rabu',
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'late_tolerance_minutes' => 15,
        ]);

        // 9. Past Attendance Session (Yesterday) for realistic recap & reports
        $yesterday = Carbon::now()->subDay();
        $pastSession = AttendanceSession::create([
            'teacher_id' => $teachers['guru']->id,
            'class_id' => $classes['8A']->id,
            'subject_id' => $subjects['INF-8']->id,
            'schedule_id' => $scheduleBudi8A->id,
            'school_year_id' => $schoolYear->id,
            'date' => $yesterday->toDateString(),
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'late_tolerance_minutes' => 15,
            'status' => 'closed',
            'opened_at' => $yesterday->copy()->setTime(7, 0, 0),
            'closed_at' => $yesterday->copy()->setTime(8, 30, 0),
            'notes' => 'Sesi presensi kemarin selesai dengan tertib.',
        ]);

        // Generate past records for 8A students
        foreach ($seededStudents8A as $idx => $st) {
            if ($idx < 25) {
                // Hadir tepat waktu
                AttendanceRecord::create([
                    'attendance_session_id' => $pastSession->id,
                    'student_id' => $st->id,
                    'status' => AttendanceRecord::STATUS_HADIR,
                    'scanned_at' => $yesterday->copy()->setTime(7, rand(1, 14), rand(0, 59)),
                    'recorded_by' => 'qr_scan',
                ]);
            } elseif ($idx < 28) {
                // Terlambat
                AttendanceRecord::create([
                    'attendance_session_id' => $pastSession->id,
                    'student_id' => $st->id,
                    'status' => AttendanceRecord::STATUS_TERLAMBAT,
                    'scanned_at' => $yesterday->copy()->setTime(7, rand(16, 28), rand(0, 59)),
                    'recorded_by' => 'qr_scan',
                ]);
            } elseif ($idx === 28) {
                // Izin
                AttendanceRecord::create([
                    'attendance_session_id' => $pastSession->id,
                    'student_id' => $st->id,
                    'status' => AttendanceRecord::STATUS_IZIN,
                    'notes' => 'Surat izin lomba matematika',
                    'recorded_by' => 'manual_teacher',
                ]);
            } elseif ($idx === 29) {
                // Sakit
                AttendanceRecord::create([
                    'attendance_session_id' => $pastSession->id,
                    'student_id' => $st->id,
                    'status' => AttendanceRecord::STATUS_SAKIT,
                    'notes' => 'Surat keterangan dokter',
                    'recorded_by' => 'manual_teacher',
                ]);
            } else {
                // Alpa
                AttendanceRecord::create([
                    'attendance_session_id' => $pastSession->id,
                    'student_id' => $st->id,
                    'status' => AttendanceRecord::STATUS_ALPA,
                    'notes' => 'Tanpa keterangan',
                    'recorded_by' => 'manual_teacher',
                ]);
            }
        }
    }
}
