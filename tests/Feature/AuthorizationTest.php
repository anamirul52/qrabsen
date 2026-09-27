<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin SIPRES',
            'email' => 'admin@sipres.test',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Ringkasan Sistem Presensi');
    }

    public function test_teacher_cannot_access_admin_routes(): void
    {
        $teacherUser = User::create([
            'name' => 'Guru Pengajar',
            'email' => 'guru@sipres.test',
            'role' => 'teacher',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        Teacher::create(['user_id' => $teacherUser->id, 'gender' => 'L']);

        $response = $this->actingAs($teacherUser)->get('/admin/dashboard');
        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_student_cannot_access_teacher_or_admin_routes(): void
    {
        $studentUser = User::create([
            'name' => 'Siswa Belajar',
            'email' => 'siswa@sipres.test',
            'role' => 'student',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $class = SchoolClass::create(['name' => '8A', 'level' => '8']);
        Student::create(['user_id' => $studentUser->id, 'nis' => '24001', 'gender' => 'L', 'class_id' => $class->id]);

        $responseAdmin = $this->actingAs($studentUser)->get('/admin/dashboard');
        $responseAdmin->assertRedirect(route('student.dashboard'));

        $responseTeacher = $this->actingAs($studentUser)->get('/teacher/dashboard');
        $responseTeacher->assertRedirect(route('student.dashboard'));
    }

    public function test_student_can_access_personal_qr_page(): void
    {
        $studentUser = User::create([
            'name' => 'Siswa Belajar',
            'email' => 'siswa@sipres.test',
            'role' => 'student',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $class = SchoolClass::create(['name' => '8A', 'level' => '8']);
        Student::create(['user_id' => $studentUser->id, 'nis' => '24001', 'gender' => 'L', 'class_id' => $class->id]);

        $response = $this->actingAs($studentUser)->get('/student/qr');
        $response->assertStatus(200);
        $response->assertSee('QR Code Presensi Pribadi');
        $response->assertSee('24001');
    }
}
