<?php

namespace App\Jobs;

use App\Mail\NewPostNotification;
use App\Models\Post;
use App\Models\PostNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendPostNotifications implements ShouldQueue
{
    use Queueable;

    public $post;

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        PostNotification::with('subscriber')->where('post_id', $this->post->id)
            ->whereNull('sent_at')
            ->chunkById(500, function ($notifications) {
                foreach ($notifications as $notification) {
                    Mail::to($notification->subscriber->email)->queue(new NewPostNotification($this->post));

                    $notification->update(['sent_at' => now()]);
                }
            });
    }
}
