<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class QrToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'token',
        'is_active',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'revoked_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Generate a new secure QR token for a student, revoking old ones.
     */
    public static function generateForStudent(int $studentId): self
    {
        // Deactivate all existing tokens
        static::where('student_id', $studentId)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'revoked_at' => now(),
            ]);

        // Create new unique token
        $token = 'STU_' . Str::random(32);

        return static::create([
            'student_id' => $studentId,
            'token' => $token,
            'is_active' => true,
        ]);
    }
}
