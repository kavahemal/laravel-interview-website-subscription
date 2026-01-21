<?php

namespace App\Console\Commands;

use App\Jobs\SendPostNotifications;
use App\Models\Post;
use App\Models\PostNotification;
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

        Post::whereDoesntHave('notifications')
            ->orWhereHas('pendingNotifications')
            ->cursor()
            ->each(function ($post) use (&$processed) {
                $post->website->subscribers()->chunkById(1000, function ($subscribers) use ($post) {
                    foreach ($subscribers as $subscriber) {
                        PostNotification::firstOrCreate([
                            'post_id' => $post->id,
                            'subscriber_id' => $subscriber->id,
                        ]);
                    }
                });

                $this->info("Queued notifications for: '{$post->title}' → {$post->website->name}");
                SendPostNotifications::dispatch($post);
                $processed++;
            });

        $this->info("Processed {$processed} posts!");

        return self::SUCCESS;
    }
}
