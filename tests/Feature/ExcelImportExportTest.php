<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected SchoolClass $class;
    protected SchoolYear $year;
    protected Teacher $teacher;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Excel',
            'email' => 'admin@excel.test',
            'username' => 'adminexcel',
            'role' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->year = SchoolYear::create([
            'name' => '2025/2026',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        $this->class = SchoolClass::create([
            'name' => '8A',
            'level' => '8',
        ]);

        $teacherUser = User::create([
            'name' => 'Guru Excel',
            'email' => 'guru@excel.test',
            'username' => 'guruexcel',
            'role' => 'teacher',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '198501012010011005',
            'gender' => 'L',
        ]);

        $this->subject = Subject::create([
            'code' => 'IPA-8',
            'name' => 'Ilmu Pengetahuan Alam',
        ]);
    }

    // === 1. STUDENTS EXCEL TESTS ===
    public function test_admin_can_download_student_excel_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('template_import_siswa.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_export_students_excel(): void
    {
        $studentUser = User::create([
            'name' => 'Siswa Test',
            'email' => 'siswa@test.com',
            'username' => 'nis24050',
            'role' => 'student',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        Student::create([
            'user_id' => $studentUser->id,
            'nis' => '24050',
            'gender' => 'L',
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.students.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('data_siswa_smpn2mijen_', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_import_students_excel(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['nis', 'nisn', 'name', 'gender', 'class_name', 'email', 'phone'],
            ['24081', '0089911221', 'Rian Pratama', 'L', '8A', 'rian@excel.test', '081299881122'],
            ['24082', '0089911222', 'Nisa Sabyan', 'P', '8A', 'nisa@excel.test', '081299881123'],
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_stu_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempFile);

        $file = new UploadedFile($tempFile, 'students.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->admin)->post(route('admin.students.import'), [
            'excel_file' => $file,
        ]);

        @unlink($tempFile);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', ['nis' => '24081']);
        $this->assertDatabaseHas('students', ['nis' => '24082']);
        $this->assertDatabaseHas('users', ['email' => 'rian@excel.test']);
    }

    // === 2. SUBJECTS CRUD & EXCEL TESTS ===
    public function test_admin_can_crud_subjects(): void
    {
        // Store
        $storeResp = $this->actingAs($this->admin)->post(route('admin.subjects.store'), [
            'code' => 'MTK-8',
            'name' => 'Matematika Kelas 8',
        ]);
        $storeResp->assertRedirect(route('admin.subjects.index'));
        $this->assertDatabaseHas('subjects', ['code' => 'MTK-8']);

        $subject = Subject::where('code', 'MTK-8')->first();

        // Update
        $updateResp = $this->actingAs($this->admin)->put(route('admin.subjects.update', $subject), [
            'code' => 'MTK-8',
            'name' => 'Matematika Terpadu',
        ]);
        $updateResp->assertRedirect(route('admin.subjects.index'));
        $this->assertDatabaseHas('subjects', ['name' => 'Matematika Terpadu']);

        // Destroy
        $delResp = $this->actingAs($this->admin)->delete(route('admin.subjects.destroy', $subject));
        $delResp->assertRedirect(route('admin.subjects.index'));
        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_admin_can_download_subject_excel_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.subjects.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('template_import_mata_pelajaran.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_export_and_import_subjects_excel(): void
    {
        // Export
        $expResp = $this->actingAs($this->admin)->get(route('admin.subjects.export'));
        $expResp->assertStatus(200);
        $expResp->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Import
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['code', 'name'],
            ['BIG-8', 'Bahasa Inggris'],
            ['BIN-8', 'Bahasa Indonesia'],
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_sub_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempFile);

        $file = new UploadedFile($tempFile, 'subjects.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $impResp = $this->actingAs($this->admin)->post(route('admin.subjects.import'), [
            'excel_file' => $file,
        ]);

        @unlink($tempFile);

        $impResp->assertRedirect(route('admin.subjects.index'));
        $impResp->assertSessionHas('success');

        $this->assertDatabaseHas('subjects', ['code' => 'BIG-8']);
        $this->assertDatabaseHas('subjects', ['code' => 'BIN-8']);
    }

    // === 3. SCHEDULES CRUD & EXCEL TESTS ===
    public function test_admin_can_crud_schedules_with_conflict_validation(): void
    {
        // Store
        $response = $this->actingAs($this->admin)->post(route('admin.schedules.store'), [
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30',
            'end_time' => '09:00',
            'late_tolerance_minutes' => 15,
        ]);

        $response->assertRedirect(route('admin.schedules.index'));
        $this->assertDatabaseHas('schedules', [
            'class_id' => $this->class->id,
            'day_of_week' => 'Senin',
        ]);

        // Conflict check: Same teacher, overlapping time on same day
        $otherClass = SchoolClass::create(['name' => '8B', 'level' => '8']);
        $conflictResp = $this->actingAs($this->admin)->post(route('admin.schedules.store'), [
            'class_id' => $otherClass->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'day_of_week' => 'Senin',
            'start_time' => '08:00', // Overlaps 07:30-09:00
            'end_time' => '09:30',
            'late_tolerance_minutes' => 15,
        ]);

        $conflictResp->assertSessionHas('error');
        $this->assertDatabaseMissing('schedules', ['class_id' => $otherClass->id]);
    }

    public function test_admin_can_download_schedule_excel_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.schedules.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('template_import_jadwal.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_export_and_import_schedules_excel(): void
    {
        // Export
        $expResp = $this->actingAs($this->admin)->get(route('admin.schedules.export'));
        $expResp->assertStatus(200);
        $expResp->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Import
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['class_name', 'subject_code', 'teacher_nip_or_email', 'day_of_week', 'start_time', 'end_time', 'late_tolerance_minutes'],
            ['8A', 'IPA-8', $this->teacher->nip, 'Selasa', '07:30', '09:00', '15'],
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_sch_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempFile);

        $file = new UploadedFile($tempFile, 'schedules.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $impResp = $this->actingAs($this->admin)->post(route('admin.schedules.import'), [
            'excel_file' => $file,
        ]);

        @unlink($tempFile);

        $impResp->assertRedirect(route('admin.schedules.index'));
        $impResp->assertSessionHas('success');

        $this->assertDatabaseHas('schedules', [
            'class_id' => $this->class->id,
            'day_of_week' => 'Selasa',
        ]);
    }

    // === 4. ATTENDANCE EXCEL EXPORT TEST ===
    public function test_admin_can_export_attendance_to_excel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.attendance.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('rekap_presensi_', $response->headers->get('content-disposition'));
    }
}
