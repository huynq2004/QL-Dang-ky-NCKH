<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'class',
        'major',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function getActiveInvitationsCount(): int
    {
        return $this->invitations()
            ->where('status', 'accepted')
            ->count();
    }

    public function canSendInvitation(): bool
    {
        return $this->getActiveInvitationsCount() < 1;
    }
} 