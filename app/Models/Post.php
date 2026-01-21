<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PostNotification::class);
    }

    public function pendingNotifications(): HasMany
    {
        return $this->hasMany(PostNotification::class)->whereNull('sent_at');
    }
}
