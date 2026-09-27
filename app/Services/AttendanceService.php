<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\QrToken;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Validate and record a QR scan attendance.
     *
     * @param AttendanceSession $session
     * @param string $token
     * @param string|null $deviceInfo
     * @return array
     */
    public function validateAndRecordScan(AttendanceSession $session, string $token, ?string $deviceInfo = null): array
    {
        // 1. Session must be active
        if ($session->status !== 'active') {
            return [
                'success' => false,
                'code' => 'SESSION_INACTIVE',
                'message' => 'Sesi presensi belum dimulai atau sudah ditutup.',
            ];
        }

        // 2. Token must exist and be active
        $qrToken = QrToken::with('student.user', 'student.currentClass')
            ->where('token', trim($token))
            ->first();

        if (!$qrToken || !$qrToken->is_active) {
            return [
                'success' => false,
                'code' => 'INVALID_QR',
                'message' => 'QR Code tidak valid atau sudah dinonaktifkan.',
            ];
        }

        $student = $qrToken->student;

        // 3. Student must exist and be active
        if (!$student || $student->status !== 'active') {
            return [
                'success' => false,
                'code' => 'STUDENT_INACTIVE',
                'message' => 'Data siswa tidak aktif atau tidak ditemukan.',
            ];
        }

        // 4. Student must be enrolled in the session's class
        // Check both direct class_id and class_student pivot for flexibility
        $isInClass = ($student->class_id == $session->class_id) ||
            $student->classes()->where('classes.id', $session->class_id)->exists();

        if (!$isInClass) {
            $studentClass = $student->currentClass ? $student->currentClass->name : 'Tanpa Kelas';
            return [
                'success' => false,
                'code' => 'WRONG_CLASS',
                'message' => 'Siswa bukan dari kelas ini.',
                'student' => [
                    'name' => $student->name,
                    'nis' => $student->nis,
                    'class' => $studentClass,
                ],
                'session_class' => $session->schoolClass->name,
            ];
        }

        // 5. Check if student already attended
        $existing = AttendanceRecord::where('attendance_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'code' => 'ALREADY_ATTENDED',
                'message' => 'Siswa sudah melakukan presensi pada sesi ini.',
                'student' => [
                    'name' => $student->name,
                    'nis' => $student->nis,
                    'class' => $session->schoolClass->name,
                ],
                'status' => $existing->status,
                'scanned_at' => $existing->scanned_at ? $existing->scanned_at->format('H:i:s') : '-',
            ];
        }

        // 6. Determine attendance status based on late tolerance
        $now = Carbon::now();
        $lateThreshold = $session->getLateThreshold();
        $status = $now->greaterThan($lateThreshold) ? AttendanceRecord::STATUS_TERLAMBAT : AttendanceRecord::STATUS_HADIR;

        // 7. Atomic DB insertion with race-condition catching
        try {
            $record = DB::transaction(function () use ($session, $student, $status, $now, $deviceInfo) {
                return AttendanceRecord::create([
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                    'status' => $status,
                    'scanned_at' => $now,
                    'device_info' => $deviceInfo,
                    'recorded_by' => 'qr_scan',
                ]);
            });
        } catch (QueryException $e) {
            // Handle race condition / duplicate key constraint (error 23000 / 1062)
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'attendance_record_unique') || str_contains($e->getMessage(), 'Duplicate entry')) {
                $already = AttendanceRecord::where('attendance_session_id', $session->id)
                    ->where('student_id', $student->id)
                    ->first();

                return [
                    'success' => false,
                    'code' => 'ALREADY_ATTENDED',
                    'message' => 'Siswa sudah melakukan presensi pada sesi ini.',
                    'student' => [
                        'name' => $student->name,
                        'nis' => $student->nis,
                        'class' => $session->schoolClass->name,
                    ],
                    'status' => $already ? $already->status : $status,
                    'scanned_at' => $already && $already->scanned_at ? $already->scanned_at->format('H:i:s') : $now->format('H:i:s'),
                ];
            }
            throw $e;
        }

        // 8. Audit Log
        AuditLog::log(
            'scan_qr',
            "Presensi {$status} siswa {$student->name} (NIS: {$student->nis}) pada sesi {$session->schoolClass->name} - {$session->subject->name}",
            'AttendanceRecord',
            (string) $record->id
        );

        // 9. Fetch updated session statistics
        $stats = $this->getSessionStats($session);

        return [
            'success' => true,
            'status' => $status,
            'message' => $status === AttendanceRecord::STATUS_HADIR ? 'Presensi Berhasil' : 'Presensi Tercatat (Terlambat)',
            'student' => [
                'name' => $student->name,
                'nis' => $student->nis,
                'class' => $session->schoolClass->name,
            ],
            'scanned_at' => $record->scanned_at->format('H:i:s'),
            'stats' => $stats,
        ];
    }

    /**
     * Get statistics for a session.
     */
    public function getSessionStats(AttendanceSession $session): array
    {
        $totalStudents = Student::where('class_id', $session->class_id)
            ->where('status', 'active')
            ->count();

        $records = AttendanceRecord::where('attendance_session_id', $session->id)->get();

        $presentCount = $records->where('status', AttendanceRecord::STATUS_HADIR)->count();
        $lateCount = $records->where('status', AttendanceRecord::STATUS_TERLAMBAT)->count();
        $permissionCount = $records->where('status', AttendanceRecord::STATUS_IZIN)->count();
        $sickCount = $records->where('status', AttendanceRecord::STATUS_SAKIT)->count();
        $alphaCount = $records->where('status', AttendanceRecord::STATUS_ALPA)->count();

        $attendedCount = $presentCount + $lateCount;
        $unattendedCount = max(0, $totalStudents - $records->count());

        return [
            'total_students' => $totalStudents,
            'attended_count' => $attendedCount,
            'present_count' => $presentCount,
            'late_count' => $lateCount,
            'permission_count' => $permissionCount,
            'sick_count' => $sickCount,
            'alpha_count' => $alphaCount,
            'unattended_count' => $unattendedCount,
        ];
    }

    /**
     * Open or activate a new attendance session.
     */
    public function openSession(int $teacherId, array $data): AttendanceSession
    {
        // Check for conflicting active session for the same teacher
        $existingActive = AttendanceSession::where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->first();

        if ($existingActive) {
            // Close the previous active session or throw
            $existingActive->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);
        }

        $session = AttendanceSession::create([
            'teacher_id' => $teacherId,
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
            'schedule_id' => $data['schedule_id'] ?? null,
            'school_year_id' => $data['school_year_id'],
            'date' => $data['date'] ?? now()->toDateString(),
            'start_time' => $data['start_time'] ?? now()->toTimeString(),
            'end_time' => $data['end_time'] ?? now()->addMinutes(90)->toTimeString(),
            'late_tolerance_minutes' => $data['late_tolerance_minutes'] ?? 15,
            'status' => 'active',
            'opened_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        AuditLog::log(
            'create_session',
            "Membuka sesi presensi kelas {$session->schoolClass->name} untuk mata pelajaran {$session->subject->name}",
            'AttendanceSession',
            (string) $session->id
        );

        return $session;
    }

    /**
     * Close an attendance session and auto-fill remaining students as ALPA if needed.
     */
    public function closeSession(AttendanceSession $session, bool $markRemainingAsAlpha = true): void
    {
        DB::transaction(function () use ($session, $markRemainingAsAlpha) {
            if ($markRemainingAsAlpha) {
                // Get all active students in class who don't have a record yet
                $existingStudentIds = AttendanceRecord::where('attendance_session_id', $session->id)
                    ->pluck('student_id')
                    ->toArray();

                $unattendedStudents = Student::where('class_id', $session->class_id)
                    ->where('status', 'active')
                    ->whereNotIn('id', $existingStudentIds)
                    ->get();

                foreach ($unattendedStudents as $student) {
                    AttendanceRecord::create([
                        'attendance_session_id' => $session->id,
                        'student_id' => $student->id,
                        'status' => AttendanceRecord::STATUS_ALPA,
                        'recorded_by' => 'manual_teacher',
                        'notes' => 'Otomatis alpa saat sesi ditutup',
                    ]);
                }
            }

            $session->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            AuditLog::log(
                'close_session',
                "Menutup sesi presensi kelas {$session->schoolClass->name} - {$session->subject->name}",
                'AttendanceSession',
                (string) $session->id
            );
        });
    }

    /**
     * Manual attendance update (for teacher or admin correction).
     */
    public function updateRecord(AttendanceSession $session, int $studentId, string $status, ?string $notes = null, string $recordedBy = 'manual_teacher'): AttendanceRecord
    {
        $record = AttendanceRecord::updateOrCreate(
            [
                'attendance_session_id' => $session->id,
                'student_id' => $studentId,
            ],
            [
                'status' => $status,
                'notes' => $notes,
                'recorded_by' => $recordedBy,
                'scanned_at' => in_array($status, [AttendanceRecord::STATUS_HADIR, AttendanceRecord::STATUS_TERLAMBAT]) ? now() : null,
            ]
        );

        AuditLog::log(
            'koreksi_presensi',
            "Koreksi status presensi siswa ID {$studentId} menjadi {$status} pada sesi {$session->id}",
            'AttendanceRecord',
            (string) $record->id
        );

        return $record;
    }
}
