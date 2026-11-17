<?php

namespace App\Jobs;

use App\Mail\NewPostNotification;
use App\Models\PostNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPostNotifications implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(public int $notificationId) {}

    public function handle(): void
    {
        // Atomic claim: only one worker can set sent_at, so a row is never sent twice.
        $claimed = PostNotification::whereKey($this->notificationId)
            ->whereNull('sent_at')
            ->update(['sent_at' => now()]);

        if ($claimed === 0) {
            return;
        }

        $notification = PostNotification::with(['post.website', 'subscriber'])->find($this->notificationId);

        try {
            Mail::to($notification->subscriber->email)
                ->send(new NewPostNotification($notification->post));
        } catch (Throwable $e) {
            PostNotification::whereKey($this->notificationId)->update(['sent_at' => null]);

            throw $e;
        }
    }

    public function failed(?Throwable $e): void
    {
        // Let the next command run pick it up again.
        PostNotification::whereKey($this->notificationId)
            ->whereNull('sent_at')
            ->update(['queued_at' => null]);
    }
}
