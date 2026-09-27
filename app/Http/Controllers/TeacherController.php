<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    protected function getTeacher(): Teacher
    {
        $teacher = Auth::user()->teacher;
        if (!$teacher) {
            abort(403, 'Profil pengajar tidak ditemukan.');
        }
        return $teacher;
    }

    /**
     * Teacher Action-Oriented Dashboard
     */
    public function dashboard()
    {
        $teacher = $this->getTeacher();
        $today = Carbon::today()->toDateString();

        $dayMap = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 0 => 'Minggu',
        ];
        $todayName = $dayMap[Carbon::now()->dayOfWeek] ?? 'Senin';

        // 1. Active Ongoing Session
        $activeSession = AttendanceSession::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->first();

        // 2. Today's Teaching Schedules
        $todaySchedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('day_of_week', $todayName)
            ->orderBy('start_time')
            ->get();

        // Check which schedules already have sessions today
        $todaySessions = AttendanceSession::where('teacher_id', $teacher->id)
            ->whereDate('date', $today)
            ->get()
            ->keyBy('schedule_id');

        // Recent closed sessions
        $recentSessions = AttendanceSession::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('status', 'closed')
            ->latest('date')
            ->latest('start_time')
            ->take(5)
            ->get();

        return view('teacher.dashboard', compact(
            'teacher',
            'activeSession',
            'todaySchedules',
            'todaySessions',
            'recentSessions',
            'todayName'
        ));
    }

    /**
     * Sessions List
     */
    public function sessions()
    {
        $teacher = $this->getTeacher();
        $sessions = AttendanceSession::with(['schoolClass', 'subject', 'records'])
            ->where('teacher_id', $teacher->id)
            ->latest('date')
            ->latest('start_time')
            ->paginate(15);

        return view('teacher.sessions.index', compact('sessions'));
    }

    /**
     * Create New Attendance Session Form
     */
    public function createSession(Request $request)
    {
        $teacher = $this->getTeacher();
        $scheduleId = $request->query('schedule_id');
        $prefillSchedule = null;

        if ($scheduleId) {
            $prefillSchedule = Schedule::where('teacher_id', $teacher->id)->find($scheduleId);
        }

        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $schedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get();

        return view('teacher.sessions.create', compact('classes', 'subjects', 'schedules', 'prefillSchedule'));
    }

    /**
     * Store New Attendance Session
     */
    public function storeSession(Request $request)
    {
        $teacher = $this->getTeacher();

        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $activeYear = SchoolYear::getActive() ?? SchoolYear::first();

        $session = $this->attendanceService->openSession($teacher->id, [
            'class_id' => $validated['class_id'],
            'subject_id' => $validated['subject_id'],
            'schedule_id' => $validated['schedule_id'] ?? null,
            'school_year_id' => $activeYear->id,
            'late_tolerance_minutes' => $validated['late_tolerance_minutes'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('teacher.sessions.scanner', $session)
            ->with('success', "Sesi presensi untuk {$session->schoolClass->name} ({$session->subject->name}) berhasil dibuka! Kamera siap memindai QR.");
    }

    /**
     * The Dedicated High-Performance QR Scanner Page
     */
    public function scanner(AttendanceSession $session)
    {
        $teacher = $this->getTeacher();

        if ($session->teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk sesi ini.');
        }

        $session->load(['schoolClass.students.user', 'subject', 'records.student.user']);

        $stats = $this->attendanceService->getSessionStats($session);

        // List of students in the class with their current attendance record
        $students = $session->schoolClass->students()
            ->with('user')
            ->where('status', 'active')
            ->orderBy('nis')
            ->get();

        $recordsMap = $session->records->keyBy('student_id');

        $studentsList = $students->map(function ($s) use ($recordsMap) {
            $rec = $recordsMap->get($s->id);
            return [
                'id' => $s->id,
                'name' => $s->name,
                'nis' => $s->nisn ?: $s->nis,
                'nisn' => $s->nisn ?: $s->nis,
                'gender' => $s->gender,
                'status' => $rec ? $rec->status : null,
                'time' => $rec && $rec->scanned_at ? $rec->scanned_at->format('H:i') : null,
            ];
        });

        return view('teacher.sessions.scanner', compact('session', 'stats', 'students', 'recordsMap', 'studentsList'));
    }

    /**
     * Scan QR API Endpoint (Called asynchronously by html5-qrcode)
     */
    public function scan(Request $request, AttendanceSession $session)
    {
        $teacher = $this->getTeacher();

        if ($session->teacher_id !== $teacher->id) {
            return response()->json([
                'success' => false,
                'code' => 'UNAUTHORIZED',
                'message' => 'Anda tidak memiliki akses ke sesi ini.',
            ], 403);
        }

        $request->validate([
            'token' => ['required', 'string'],
            'device_info' => ['nullable', 'string'],
        ]);

        $result = $this->attendanceService->validateAndRecordScan(
            $session,
            $request->input('token'),
            $request->input('device_info')
        );

        return response()->json($result);
    }

    /**
     * Manual Attendance Record Update (For students without QR or corrections)
     */
    public function manualAttendance(Request $request, AttendanceSession $session)
    {
        $teacher = $this->getTeacher();

        if ($session->teacher_id !== $teacher->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'status' => ['required', 'in:HADIR,TERLAMBAT,IZIN,SAKIT,ALPA'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->attendanceService->updateRecord(
            $session,
            $validated['student_id'],
            $validated['status'],
            $validated['notes'] ?? null,
            'manual_teacher'
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Presensi manual berhasil diperbarui.',
                'stats' => $this->attendanceService->getSessionStats($session),
            ]);
        }

        return back()->with('success', 'Presensi berhasil diperbarui.');
    }

    /**
     * Close Attendance Session
     */
    public function closeSession(Request $request, AttendanceSession $session)
    {
        $teacher = $this->getTeacher();

        if ($session->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $this->attendanceService->closeSession($session, true);

        return redirect()->route('teacher.sessions.show', $session)
            ->with('success', "Sesi presensi {$session->schoolClass->name} telah ditutup. Siswa yang belum hadir otomatis tercatat ALPA.");
    }

    /**
     * Session Detail & Summary Report
     */
    public function showSession(AttendanceSession $session)
    {
        $teacher = $this->getTeacher();

        if ($session->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $session->load(['schoolClass', 'subject', 'records.student.user']);
        $stats = $this->attendanceService->getSessionStats($session);

        $students = $session->schoolClass->students()
            ->with('user')
            ->orderBy('nis')
            ->get();

        $recordsMap = $session->records->keyBy('student_id');

        return view('teacher.sessions.show', compact('session', 'stats', 'students', 'recordsMap'));
    }

    /**
     * Teacher Attendance History
     */
    public function attendance(Request $request)
    {
        $teacher = $this->getTeacher();

        $query = AttendanceRecord::with(['student.user', 'student.currentClass', 'attendanceSession.subject', 'attendanceSession.schoolClass'])
            ->whereHas('attendanceSession', fn($q) => $q->where('teacher_id', $teacher->id));

        if ($classId = $request->input('class_id')) {
            $query->whereHas('attendanceSession', fn($q) => $q->where('class_id', $classId));
        }

        if ($date = $request->input('date')) {
            $query->whereHas('attendanceSession', fn($q) => $q->whereDate('date', $date));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $records = $query->latest('id')->paginate(20)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();

        return view('teacher.attendance.index', compact('records', 'classes'));
    }
}
