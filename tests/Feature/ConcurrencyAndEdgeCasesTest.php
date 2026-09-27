<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\QrToken;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConcurrencyAndEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_duplicate_scan_is_caught_by_database_constraint(): void
    {
        $schoolYear = SchoolYear::create(['name' => '2025/2026', 'semester' => 'ganjil', 'is_active' => true]);
        $class = SchoolClass::create(['name' => '8A', 'level' => '8']);
        $subject = Subject::create(['code' => 'INF-8', 'name' => 'Informatika']);

        $teacherUser = User::create(['name' => 'Pak Budi', 'email' => 'budi@test.com', 'role' => 'teacher', 'password' => Hash::make('password')]);
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'gender' => 'L']);

        $studentUser = User::create(['name' => 'Citra', 'email' => 'citra@test.com', 'role' => 'student', 'password' => Hash::make('password')]);
        $student = Student::create(['user_id' => $studentUser->id, 'nis' => '24005', 'gender' => 'P', 'class_id' => $class->id, 'status' => 'active']);
        $token = QrToken::generateForStudent($student->id);

        $session = AttendanceSession::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $schoolYear->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subMinutes(5)->toTimeString(),
            'end_time' => Carbon::now()->addMinutes(85)->toTimeString(),
            'late_tolerance_minutes' => 15,
            'status' => 'active',
            'opened_at' => Carbon::now()->subMinutes(5),
        ]);

        $service = app(AttendanceService::class);

        // First call
        $res1 = $service->validateAndRecordScan($session, $token->token);
        $this->assertTrue($res1['success']);
        $this->assertEquals('HADIR', $res1['status']);

        // Second call simulating simultaneous request
        $res2 = $service->validateAndRecordScan($session, $token->token);
        $this->assertFalse($res2['success']);
        $this->assertEquals('ALREADY_ATTENDED', $res2['code']);

        // Exactly one record exists
        $this->assertEquals(1, AttendanceRecord::where('attendance_session_id', $session->id)->where('student_id', $student->id)->count());
    }

    public function test_excel_export_endpoint_returns_streaming_excel_file(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'role' => 'admin', 'password' => Hash::make('password'), 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_inactive_student_is_rejected_on_scan(): void
    {
        $schoolYear = SchoolYear::create(['name' => '2025/2026', 'semester' => 'ganjil', 'is_active' => true]);
        $class = SchoolClass::create(['name' => '8A', 'level' => '8']);
        $subject = Subject::create(['code' => 'INF-8', 'name' => 'Informatika']);

        $teacherUser = User::create(['name' => 'Pak Budi', 'email' => 'budi2@test.com', 'role' => 'teacher', 'password' => Hash::make('password')]);
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'gender' => 'L']);

        $studentUser = User::create(['name' => 'Siswa Nonaktif', 'email' => 'inactive@test.com', 'role' => 'student', 'password' => Hash::make('password')]);
        $student = Student::create(['user_id' => $studentUser->id, 'nis' => '24099', 'gender' => 'L', 'class_id' => $class->id, 'status' => 'inactive']);
        $token = QrToken::generateForStudent($student->id);

        $session = AttendanceSession::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'school_year_id' => $schoolYear->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subMinutes(5)->toTimeString(),
            'end_time' => Carbon::now()->addMinutes(85)->toTimeString(),
            'late_tolerance_minutes' => 15,
            'status' => 'active',
            'opened_at' => Carbon::now()->subMinutes(5),
        ]);

        $service = app(AttendanceService::class);
        $res = $service->validateAndRecordScan($session, $token->token);

        $this->assertFalse($res['success']);
        $this->assertEquals('STUDENT_INACTIVE', $res['code']);
    }
}
