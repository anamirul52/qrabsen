<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\QrToken;
use App\Models\Student;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    protected QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    protected function getStudent(): Student
    {
        $student = Auth::user()->student;
        if (!$student) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }
        return $student;
    }

    /**
     * Student Dashboard
     */
    public function dashboard()
    {
        $student = $this->getStudent();
        $student->load(['currentClass', 'activeQrToken']);

        $today = Carbon::today()->toDateString();

        // Today's attendance records for this student
        $todayAttendances = AttendanceRecord::with(['attendanceSession.subject', 'attendanceSession.teacher.user'])
            ->where('student_id', $student->id)
            ->whereHas('attendanceSession', fn($q) => $q->whereDate('date', $today))
            ->get();

        // Overall statistics
        $allRecords = AttendanceRecord::where('student_id', $student->id)->get();
        $hadirCount = $allRecords->where('status', AttendanceRecord::STATUS_HADIR)->count();
        $lateCount = $allRecords->where('status', AttendanceRecord::STATUS_TERLAMBAT)->count();
        $izinCount = $allRecords->where('status', AttendanceRecord::STATUS_IZIN)->count();
        $sakitCount = $allRecords->where('status', AttendanceRecord::STATUS_SAKIT)->count();
        $alpaCount = $allRecords->where('status', AttendanceRecord::STATUS_ALPA)->count();
        $totalRecords = $allRecords->count();

        $presentPercentage = $totalRecords > 0 ? round((($hadirCount + $lateCount) / $totalRecords) * 100) : 100;

        // Ensure student has QR token
        $qrToken = $student->activeQrToken;
        if (!$qrToken) {
            $qrToken = QrToken::generateForStudent($student->id);
            $student->load('activeQrToken');
        }

        $qrSvg = $this->qrCodeService->generateSvg($qrToken->token, 200);

        return view('student.dashboard', compact(
            'student',
            'todayAttendances',
            'hadirCount',
            'lateCount',
            'izinCount',
            'sakitCount',
            'alpaCount',
            'totalRecords',
            'presentPercentage',
            'qrSvg'
        ));
    }

    /**
     * Dedicated Student QR Page (Big screen, fullscreen mode, download)
     */
    public function qr()
    {
        $student = $this->getStudent();
        $student->load(['currentClass', 'activeQrToken']);

        $qrToken = $student->activeQrToken;
        if (!$qrToken) {
            $qrToken = QrToken::generateForStudent($student->id);
            $student->load('activeQrToken');
        }

        // Generate large high-resolution SVG
        $qrSvg = $this->qrCodeService->generateSvg($qrToken->token, 300);

        return view('student.qr', compact('student', 'qrToken', 'qrSvg'));
    }

    /**
     * Student Attendance History
     */
    public function attendance(Request $request)
    {
        $student = $this->getStudent();

        $query = AttendanceRecord::with(['attendanceSession.subject', 'attendanceSession.schoolClass', 'attendanceSession.teacher.user'])
            ->where('student_id', $student->id);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($month = $request->input('month')) {
            $query->whereHas('attendanceSession', fn($q) => $q->whereMonth('date', Carbon::parse($month)->month));
        }

        $records = $query->latest('id')->paginate(15)->withQueryString();

        return view('student.attendance', compact('student', 'records'));
    }
}
