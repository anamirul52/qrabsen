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
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacherUser;
    protected Teacher $teacher;
    protected SchoolClass $class8A;
    protected SchoolClass $class8B;
    protected Subject $subject;
    protected SchoolYear $schoolYear;
    protected Student $student8A;
    protected Student $student8B;
    protected AttendanceSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        // School Year
        $this->schoolYear = SchoolYear::create([
            'name' => '2025/2026',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        // Classes
        $this->class8A = SchoolClass::create(['name' => '8A', 'level' => '8']);
        $this->class8B = SchoolClass::create(['name' => '8B', 'level' => '8']);

        // Subject
        $this->subject = Subject::create(['code' => 'INF-8', 'name' => 'Informatika']);

        // Teacher
        $this->teacherUser = User::create([
            'name' => 'Budi Santoso, S.Pd.',
            'email' => 'guru@sipres.test',
            'role' => 'teacher',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198503152010011012',
            'gender' => 'L',
        ]);

        // Student 8A
        $userStudent8A = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad@sipres.test',
            'role' => 'student',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $this->student8A = Student::create([
            'user_id' => $userStudent8A->id,
            'nis' => '24001',
            'gender' => 'L',
            'class_id' => $this->class8A->id,
            'status' => 'active',
        ]);
        QrToken::generateForStudent($this->student8A->id);

        // Student 8B
        $userStudent8B = User::create([
            'name' => 'Bambang Sudirman',
            'email' => 'bambang@sipres.test',
            'role' => 'student',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $this->student8B = Student::create([
            'user_id' => $userStudent8B->id,
            'nis' => '24050',
            'gender' => 'L',
            'class_id' => $this->class8B->id,
            'status' => 'active',
        ]);
        QrToken::generateForStudent($this->student8B->id);

        // Active Session for Class 8A
        $this->session = AttendanceSession::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->class8A->id,
            'subject_id' => $this->subject->id,
            'school_year_id' => $this->schoolYear->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subMinutes(5)->toTimeString(),
            'end_time' => Carbon::now()->addMinutes(85)->toTimeString(),
            'late_tolerance_minutes' => 15,
            'status' => 'active',
            'opened_at' => Carbon::now()->subMinutes(5),
        ]);
    }

    public function test_valid_qr_scan_records_attendance_atomically(): void
    {
        $token = $this->student8A->activeQrToken->token;

        $response = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.sessions.scan', $this->session), [
                'token' => $token,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'HADIR',
            'student' => [
                'name' => 'Ahmad Fauzi',
                'nis' => '24001',
                'class' => '8A',
            ],
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $this->session->id,
            'student_id' => $this->student8A->id,
            'status' => 'HADIR',
            'recorded_by' => 'qr_scan',
        ]);
    }

    public function test_duplicate_scan_is_prevented_and_returns_already_attended(): void
    {
        $token = $this->student8A->activeQrToken->token;

        // First scan succeeds
        $firstResponse = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.sessions.scan', $this->session), [
                'token' => $token,
            ]);
        $firstResponse->assertStatus(200);
        $firstResponse->assertJson(['success' => true]);

        // Second scan must be rejected
        $secondResponse = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.sessions.scan', $this->session), [
                'token' => $token,
            ]);

        $secondResponse->assertStatus(200);
        $secondResponse->assertJson([
            'success' => false,
            'code' => 'ALREADY_ATTENDED',
        ]);

        // Assert database has EXACTLY ONE attendance record
        $this->assertEquals(
            1,
            AttendanceRecord::where('attendance_session_id', $this->session->id)
                ->where('student_id', $this->student8A->id)
                ->count()
        );
    }

    public function test_wrong_class_student_is_rejected(): void
    {
        // Student 8B attempts scanning in class 8A session
        $token8B = $this->student8B->activeQrToken->token;

        $response = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.sessions.scan', $this->session), [
                'token' => $token8B,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'code' => 'WRONG_CLASS',
        ]);

        $this->assertDatabaseMissing('attendance_records', [
            'attendance_session_id' => $this->session->id,
            'student_id' => $this->student8B->id,
        ]);
    }

    public function test_inactive_session_rejects_scans(): void
    {
        $this->session->update(['status' => 'closed']);

        $token = $this->student8A->activeQrToken->token;

        $response = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.sessions.scan', $this->session), [
                'token' => $token,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'code' => 'SESSION_INACTIVE',
        ]);
    }

    public function test_late_scan_calculates_terlambat_status(): void
    {
        // Session started 30 minutes ago, with 15 minutes tolerance => scanning now is TERLAMBAT
        $lateSession = AttendanceSession::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->class8A->id,
            'subject_id' => $this->subject->id,
            'school_year_id' => $this->schoolYear->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subMinutes(30)->toTimeString(),
            'end_time' => Carbon::now()->addMinutes(60)->toTimeString(),
            'late_tolerance_minutes' => 15,
            'status' => 'active',
            'opened_at' => Carbon::now()->subMinutes(30),
        ]);

        $token = $this->student8A->activeQrToken->token;

        $response = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.sessions.scan', $lateSession), [
                'token' => $token,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'TERLAMBAT',
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $lateSession->id,
            'student_id' => $this->student8A->id,
            'status' => 'TERLAMBAT',
        ]);
    }

    public function test_regenerated_qr_invalidates_old_token(): void
    {
        $oldToken = $this->student8A->activeQrToken->token;

        // Admin regenerates QR token for student
        $newTokenModel = QrToken::generateForStudent($this->student8A->id);
        $newToken = $newTokenModel->token;

        // Verify old token is marked inactive
        $this->assertDatabaseHas('qr_tokens', [
            'token' => $oldToken,
            'is_active' => false,
        ]);

        // Attempt scan with old token -> must be rejected
        $oldResponse = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.sessions.scan', $this->session), [
                'token' => $oldToken,
            ]);

        $oldResponse->assertStatus(200);
        $oldResponse->assertJson([
            'success' => false,
            'code' => 'INVALID_QR',
        ]);

        // Attempt scan with new token -> must succeed
        $newResponse = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.sessions.scan', $this->session), [
                'token' => $newToken,
            ]);

        $newResponse->assertStatus(200);
        $newResponse->assertJson([
            'success' => true,
            'status' => 'HADIR',
        ]);
    }
}
