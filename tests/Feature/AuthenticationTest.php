<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIPRES');
        $response->assertSee('SMP NEGERI 2 MIJEN');
    }

    public function test_users_can_authenticate_using_email(): void
    {
        $user = User::create([
            'name' => 'Pak Budi',
            'email' => 'budi@sipres.test',
            'username' => 'budi',
            'role' => 'teacher',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'budi@sipres.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('teacher.dashboard'));
    }

    public function test_users_can_authenticate_using_username(): void
    {
        $user = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sipres.test',
            'username' => 'admin',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sipres.test',
            'username' => 'admin',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create([
            'name' => 'Siswa Nonaktif',
            'email' => 'inactive@sipres.test',
            'username' => 'inactive',
            'role' => 'student',
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'login' => 'inactive',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'Siswa Aktif',
            'email' => 'siswa@sipres.test',
            'username' => 'siswa',
            'role' => 'student',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
