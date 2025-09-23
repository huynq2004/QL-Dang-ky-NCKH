<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proposal extends Model
{
    protected $fillable = [
        'title',
        'description',
        'field',
        'lecturer_id',
        'student_id',
        'current_members',
        'is_visible',
        'status',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function getAcceptedMembersCount(): int
    {
        return $this->invitations()
            ->where('status', 'accepted')
            ->count();
    }

    public function canAcceptMoreMembers(): bool
    {
        return app(\App\Services\InvitationService::class)
               ->proposalHasCapacity($this->id);
    }
} 