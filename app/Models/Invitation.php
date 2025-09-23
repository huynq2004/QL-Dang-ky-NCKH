<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'lecturer_id',
        'proposal_id',
        'status',
        'message',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function canBeRevoked(): bool
    {
        return $this->status === 'pending' && 
               $this->created_at->addHours(24)->isFuture();
    }

    public function shouldBeAutoProcessed(): bool
    {
        // Align with service: expire after 7 days
        return $this->status === 'pending' && 
               $this->created_at->addDays(7)->isPast();
    }

    public function process(string $status): void
    {
        $this->update([
            'status' => $status,
            'processed_at' => Carbon::now(),
        ]);
    }
} 