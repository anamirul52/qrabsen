<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. School Years
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. 2025/2026
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });

        // 2. Classes (without FK to teachers yet)
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 7A, 8B, 9C
            $table->enum('level', ['7', '8', '9'])->index();
            $table->unsignedBigInteger('homeroom_teacher_id')->nullable()->index();
            $table->timestamps();
        });

        // 3. Teachers
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nip')->nullable()->unique();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->timestamps();
        });

        // Add FK from classes to teachers
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('homeroom_teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });

        // 4. Students
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nis')->unique()->index();
            $table->string('nisn')->nullable()->unique()->index();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->enum('status', ['active', 'inactive', 'graduated'])->default('active')->index();
            $table->timestamps();
        });

        // 5. Class - Student Enrollment History
        Schema::create('class_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['class_id', 'student_id', 'school_year_id'], 'class_student_unique');
        });

        // 6. Subjects
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        // 7. Schedules
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
            $table->enum('day_of_week', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('late_tolerance_minutes')->default(15);
            $table->timestamps();

            $table->index(['teacher_id', 'day_of_week']);
            $table->index(['class_id', 'day_of_week']);
        });

        // 8. Attendance Sessions
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
            $table->date('date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('late_tolerance_minutes')->default(15);
            $table->enum('status', ['scheduled', 'active', 'closed', 'cancelled'])->default('scheduled')->index();
            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
            $table->index(['class_id', 'date']);
        });

        // 9. Attendance Records
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained('attendance_sessions')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('status', ['HADIR', 'TERLAMBAT', 'IZIN', 'SAKIT', 'ALPA'])->default('HADIR')->index();
            $table->dateTime('scanned_at')->nullable();
            $table->string('device_info')->nullable();
            $table->text('notes')->nullable();
            $table->enum('recorded_by', ['qr_scan', 'manual_teacher', 'manual_admin'])->default('qr_scan');
            $table->timestamps();

            // CONCURRENCY & DUPLICATE PROTECTION: Exactly ONE record per student per session
            $table->unique(['attendance_session_id', 'student_id'], 'attendance_record_unique');
            $table->index(['student_id', 'status']);
        });

        // 10. QR Tokens
        Schema::create('qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('token')->unique()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->dateTime('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'is_active']);
        });

        // 11. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->string('entity_type')->nullable();
            $table->string('entity_id')->nullable();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // 12. Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('qr_tokens');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('class_student');
        Schema::dropIfExists('students');
        
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['homeroom_teacher_id']);
        });
        
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('school_years');
    }
};
