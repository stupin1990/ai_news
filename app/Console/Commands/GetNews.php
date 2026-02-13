<?php

namespace App\Console\Commands;

use App\Models\News;
use Illuminate\Console\Command;

class GetNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get last news from api';

    const array NEWS_SERVICES = [
        \App\Services\News\GNewsService::class,
        \App\Services\News\NewsDataService::class
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {

        foreach (self::NEWS_SERVICES as $serviceClass) {

            $service = new $serviceClass;

            echo 'Get news from ' . $serviceClass::$name . ': ';

            $data = $service->getLastNews();

            if (!count($data)) {
                echo 'Fail' . PHP_EOL;
                continue;
            }

            $before = News::count();
            News::upsert($data, uniqueBy: ['slug'], update: ['title', 'slug', 'source_url', 'image', 'published_at', 'source']);
            $after = News::count();

            echo 'Ok (' . ($after - $before) .  ' added)' . PHP_EOL;
        }

        return self::SUCCESS;
    }
}
