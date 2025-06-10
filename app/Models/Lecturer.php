<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecturer extends Model
{
    protected $fillable = [
        'user_id',
        'lecturer_id',
        'department',
        'title',
        'specialization',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function getActiveProposalsCount(): int
    {
        return $this->proposals()
            ->whereHas('invitations', function ($query) {
                $query->where('status', 'accepted');
            })
            ->count();
    }

    public function canAcceptInvitation(): bool
    {
        return $this->getActiveProposalsCount() < 5;
    }
} 