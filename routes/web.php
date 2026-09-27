<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SIPRES SMP NEGERI 2 MIJEN
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role;
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN / OPERATOR ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Students Management
    Route::get('/students', [AdminController::class, 'students'])->name('students.index');
    Route::get('/students/create', [AdminController::class, 'createStudent'])->name('students.create');
    Route::post('/students', [AdminController::class, 'storeStudent'])->name('students.store');
    Route::get('/students/export', [AdminController::class, 'exportStudents'])->name('students.export');
    Route::get('/students/template', [AdminController::class, 'downloadStudentTemplate'])->name('students.template');
    Route::post('/students/import', [AdminController::class, 'importStudents'])->name('students.import');
    Route::get('/students/{student}/edit', [AdminController::class, 'editStudent'])->name('students.edit');
    Route::put('/students/{student}', [AdminController::class, 'updateStudent'])->name('students.update');
    Route::post('/students/{student}/regenerate-qr', [AdminController::class, 'regenerateQr'])->name('students.regenerate-qr');
    Route::get('/students/{student}/print-qr', [AdminController::class, 'printSingleQr'])->name('students.print-qr');

    // QR Cards Batch Print
    Route::get('/qr-cards', [AdminController::class, 'batchPrintQrCards'])->name('qr-cards.index');

    // Teachers Management
    Route::get('/teachers', [AdminController::class, 'teachers'])->name('teachers.index');
    Route::post('/teachers', [AdminController::class, 'storeTeacher'])->name('teachers.store');
    Route::get('/teachers/export', [AdminController::class, 'exportTeachers'])->name('teachers.export');
    Route::get('/teachers/template', [AdminController::class, 'downloadTeacherTemplate'])->name('teachers.template');
    Route::post('/teachers/import', [AdminController::class, 'importTeachers'])->name('teachers.import');
    Route::put('/teachers/{teacher}', [AdminController::class, 'updateTeacher'])->name('teachers.update');
    Route::delete('/teachers/{teacher}', [AdminController::class, 'destroyTeacher'])->name('teachers.destroy');

    // Classes Management
    Route::get('/classes', [AdminController::class, 'classes'])->name('classes.index');
    Route::post('/classes', [AdminController::class, 'storeClass'])->name('classes.store');
    Route::get('/classes/template', [AdminController::class, 'downloadClassTemplate'])->name('classes.template');
    Route::post('/classes/import', [AdminController::class, 'importClasses'])->name('classes.import');
    Route::put('/classes/{class}', [AdminController::class, 'updateClass'])->name('classes.update');
    Route::delete('/classes/{class}', [AdminController::class, 'destroyClass'])->name('classes.destroy');

    // Subjects Management (Mata Pelajaran)
    Route::get('/subjects', [AdminController::class, 'subjects'])->name('subjects.index');
    Route::post('/subjects', [AdminController::class, 'storeSubject'])->name('subjects.store');
    Route::get('/subjects/export', [AdminController::class, 'exportSubjects'])->name('subjects.export');
    Route::get('/subjects/template', [AdminController::class, 'downloadSubjectTemplate'])->name('subjects.template');
    Route::post('/subjects/import', [AdminController::class, 'importSubjects'])->name('subjects.import');
    Route::put('/subjects/{subject}', [AdminController::class, 'updateSubject'])->name('subjects.update');
    Route::delete('/subjects/{subject}', [AdminController::class, 'destroySubject'])->name('subjects.destroy');

    // Schedules Management (Jadwal Pelajaran)
    Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules.index');
    Route::post('/schedules', [AdminController::class, 'storeSchedule'])->name('schedules.store');
    Route::get('/schedules/export', [AdminController::class, 'exportSchedules'])->name('schedules.export');
    Route::get('/schedules/template', [AdminController::class, 'downloadScheduleTemplate'])->name('schedules.template');
    Route::post('/schedules/import', [AdminController::class, 'importSchedules'])->name('schedules.import');
    Route::put('/schedules/{schedule}', [AdminController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [AdminController::class, 'destroySchedule'])->name('schedules.destroy');

    // Attendance Recap & Excel Export
    Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance.index');
    Route::get('/attendance/export', [AdminController::class, 'exportAttendance'])->name('attendance.export');

    // Audit Logs & Settings
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs.index');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings.index');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});

/*
|--------------------------------------------------------------------------
| TEACHER (GURU) ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');

    // Attendance Sessions & Scanner
    Route::get('/sessions', [TeacherController::class, 'sessions'])->name('sessions.index');
    Route::get('/sessions/create', [TeacherController::class, 'createSession'])->name('sessions.create');
    Route::post('/sessions', [TeacherController::class, 'storeSession'])->name('sessions.store');
    Route::get('/sessions/{session}', [TeacherController::class, 'showSession'])->name('sessions.show');

    // High Priority QR Scanner
    Route::get('/sessions/{session}/scanner', [TeacherController::class, 'scanner'])->name('sessions.scanner');
    Route::post('/sessions/{session}/scan', [TeacherController::class, 'scan'])->name('sessions.scan');
    Route::post('/sessions/{session}/manual', [TeacherController::class, 'manualAttendance'])->name('sessions.manual');
    Route::post('/sessions/{session}/close', [TeacherController::class, 'closeSession'])->name('sessions.close');

    // Teacher Attendance History
    Route::get('/attendance', [TeacherController::class, 'attendance'])->name('attendance.index');
});

/*
|--------------------------------------------------------------------------
| STUDENT (SISWA) ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/qr', [StudentController::class, 'qr'])->name('qr');
    Route::get('/attendance', [StudentController::class, 'attendance'])->name('attendance');
});
