<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    public const STATUS_HADIR = 'HADIR';
    public const STATUS_TERLAMBAT = 'TERLAMBAT';
    public const STATUS_IZIN = 'IZIN';
    public const STATUS_SAKIT = 'SAKIT';
    public const STATUS_ALPA = 'ALPA';

    protected $fillable = [
        'attendance_session_id',
        'student_id',
        'status',
        'scanned_at',
        'device_info',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_HADIR,
            self::STATUS_TERLAMBAT,
            self::STATUS_IZIN,
            self::STATUS_SAKIT,
            self::STATUS_ALPA,
        ];
    }
}
