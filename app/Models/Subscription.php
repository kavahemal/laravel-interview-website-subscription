<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = ['subscriber_id', 'website_id'];

    public function subscribers(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function sentNotifications(): HasMany
    {
        return $this->hasMany(PostNotification::class, 'subscriber_id', 'subscriber_id');
    }
}
