<?php

namespace App\Console\Commands;

use App\Jobs\SendPostNotifications;
use App\Models\Post;
use App\Models\PostNotification;
use App\Models\Subscription;
use Illuminate\Console\Command;

class WebsitesNotifySubscribers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websites:notify-subscribers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all websites and send all new posts to subscribers which haven\'t been sent yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Search all the unsent posts...');

        $processed = 0;

        Post::whereNull('notified_at')
            ->orWhereHas('pendingNotifications')
            ->with('website')
            ->cursor()
            ->each(function ($post) use (&$processed) {
                Subscription::where('website_id', $post->website_id)
                    ->where('created_at', '<=', $post->created_at)
                    ->select(['id', 'subscriber_id'])
                    ->chunkById(1000, function ($subscriptions) use ($post) {
                        $now = now();

                        PostNotification::insertOrIgnore($subscriptions->map(fn ($subscription) => [
                            'post_id' => $post->id,
                            'subscriber_id' => $subscription->subscriber_id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])->all());
                    });

                $post->forceFill(['notified_at' => now()])->save();

                $queued = $this->dispatchPending($post);

                $this->info("Queued {$queued} notifications for: '{$post->title}' → {$post->website->name}");
                $processed++;
            });

        $this->info("Processed {$processed} posts!");

        return self::SUCCESS;
    }

    private function dispatchPending(Post $post): int
    {
        $queued = 0;

        $post->pendingNotifications()
            ->select('id')
            ->chunkById(1000, function ($notifications) use (&$queued) {
                $ids = $notifications->modelKeys();

                foreach ($ids as $id) {
                    SendPostNotifications::dispatch($id);
                }

                PostNotification::whereKey($ids)
                    ->whereNull('sent_at')
                    ->update(['queued_at' => now()]);

                $queued += count($ids);
            });

        return $queued;
    }
}
