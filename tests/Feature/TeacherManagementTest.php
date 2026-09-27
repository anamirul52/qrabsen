<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacherUser;
    protected Teacher $teacher;
    protected SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'username' => 'admintest',
            'role' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->teacherUser = User::create([
            'name' => 'Pak Guru A',
            'email' => 'guru.a@test.com',
            'username' => 'gurua',
            'role' => 'teacher',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'nip' => '198001012005011001',
            'gender' => 'L',
        ]);

        $this->year = SchoolYear::create([
            'name' => '2025/2026',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        $this->class = SchoolClass::create([
            'name' => '7A',
            'level' => '7',
            'homeroom_teacher_id' => null,
        ]);
    }

    public function test_admin_can_view_teachers_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.teachers.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Guru Pengajar');
        $response->assertSee('Pak Guru A');
        $response->assertSee('198001012005011001');
    }

    public function test_admin_can_download_teacher_excel_template(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.teachers.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('template_import_guru.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_export_teachers_excel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.teachers.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_store_new_teacher(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.teachers.store'), [
            'name' => 'Ibu Guru B, S.Pd.',
            'email' => 'guru.b@test.com',
            'nip' => '198502022010012002',
            'username' => 'gurub',
            'gender' => 'P',
            'phone' => '081299887766',
            'password' => 'password123',
            'homeroom_class_id' => $this->class->id,
        ]);

        $response->assertRedirect(route('admin.teachers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'guru.b@test.com',
            'username' => 'gurub',
            'role' => 'teacher',
        ]);

        $newTeacher = Teacher::where('nip', '198502022010012002')->first();
        $this->assertNotNull($newTeacher);
        $this->assertEquals('P', $newTeacher->gender);

        $this->assertDatabaseHas('classes', [
            'id' => $this->class->id,
            'homeroom_teacher_id' => $newTeacher->id,
        ]);
    }

    public function test_admin_cannot_store_teacher_with_duplicate_email(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.teachers.store'), [
            'name' => 'Guru Clone',
            'email' => 'guru.a@test.com', // Duplicate
            'gender' => 'L',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_store_teacher_with_duplicate_nip(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.teachers.store'), [
            'name' => 'Guru Duplicate NIP',
            'email' => 'guru.dup@test.com',
            'nip' => '198001012005011001', // Duplicate NIP
            'gender' => 'L',
        ]);

        $response->assertSessionHasErrors('nip');
    }

    public function test_admin_can_update_teacher(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.teachers.update', $this->teacher), [
            'name' => 'Pak Guru A Updated, M.Pd.',
            'email' => 'guru.a.new@test.com',
            'username' => 'gurua_new',
            'nip' => '198001012005011001',
            'gender' => 'L',
            'phone' => '081234567899',
            'is_active' => '1',
            'homeroom_class_id' => $this->class->id,
        ]);

        $response->assertRedirect(route('admin.teachers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->teacherUser->id,
            'name' => 'Pak Guru A Updated, M.Pd.',
            'email' => 'guru.a.new@test.com',
        ]);

        $this->assertDatabaseHas('classes', [
            'id' => $this->class->id,
            'homeroom_teacher_id' => $this->teacher->id,
        ]);
    }

    public function test_admin_can_import_teachers_via_excel(): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['nip', 'name', 'gender', 'email', 'phone', 'username', 'homeroom_class'],
            ['199003032014021003', 'Drs. Eko Prasetyo, M.Si.', 'L', 'eko@test.com', '081233445566', 'ekop', ''],
            ['199204042016022004', 'Dewi Lestari, S.Pd.', 'P', 'dewi@test.com', '081277889900', 'dewil', ''],
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_teachers_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);

        $file = new UploadedFile($tempFile, 'teachers.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->admin)->post(route('admin.teachers.import'), [
            'excel_file' => $file,
        ]);

        @unlink($tempFile);

        $response->assertRedirect(route('admin.teachers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'eko@test.com',
            'username' => 'ekop',
            'role' => 'teacher',
        ]);

        $this->assertDatabaseHas('teachers', [
            'nip' => '199003032014021003',
            'gender' => 'L',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'dewi@test.com',
            'username' => 'dewil',
            'role' => 'teacher',
        ]);

        $this->assertDatabaseHas('teachers', [
            'nip' => '199204042016022004',
            'gender' => 'P',
        ]);
    }

    public function test_admin_can_delete_teacher_without_schedules(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.teachers.destroy', $this->teacher));

        $response->assertRedirect(route('admin.teachers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('teachers', ['id' => $this->teacher->id]);
        $this->assertDatabaseMissing('users', ['id' => $this->teacherUser->id]);
    }

    public function test_admin_cannot_delete_teacher_with_active_schedules(): void
    {
        $subject = Subject::create(['code' => 'MAT-7', 'name' => 'Matematika']);

        Schedule::create([
            'school_year_id' => $this->year->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $subject->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30:00',
            'end_time' => '09:00:00',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.teachers.destroy', $this->teacher));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('teachers', ['id' => $this->teacher->id]);
    }

    public function test_teacher_or_student_cannot_access_teacher_admin_routes(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('admin.teachers.index'));
        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');

        $response2 = $this->actingAs($this->teacherUser)->post(route('admin.teachers.store'), []);
        $response2->assertRedirect(route('teacher.dashboard'));
        $response2->assertSessionHas('error');
    }
}
