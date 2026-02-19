<?php

namespace App\Console\Commands;

use App\Jobs\GetNewsContent;
use App\Models\News;
use App\Services\News\NewsService;
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

    /** @var array<class-string<NewsService>> */
    public const NEWS_SERVICES = [
        \App\Services\News\GNewsService::class,
        \App\Services\News\NewsDataService::class
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var int */
        $added = 0;
        /** @var int */
        $totalAdded = 0;
        /** @var array */
        $slugs = [];

        foreach (self::NEWS_SERVICES as $serviceClass) {

            $service = new $serviceClass;

            echo 'Get news from ' . $serviceClass::$name . ': ';

            $data = $service->getLastNews();

            if (!count($data)) {
                echo 'Fail' . PHP_EOL;
                continue;
            }

            $data = collect($data);

            $slugs = array_merge($slugs, $data->pluck('slug')->all());

            $data = $data->unique('slug')->values()->all();

            $before = News::count();
            News::upsert($data, uniqueBy: ['slug'], update: ['title', 'slug', 'source_url', 'image', 'published_at', 'source']);
            $after = News::count();

            $added = $after - $before;
            $totalAdded += $added;

            echo 'Ok (' . $added . ' added)' . PHP_EOL;
        }
        echo 'Total added: ' . $totalAdded . PHP_EOL;

        $news = News::whereIn('slug', $slugs)->get();
        $news->map(function (News $item) {
            echo 'Dispatch GetNewsContent: '.$item->id.PHP_EOL;
            GetNewsContent::dispatch($item)
                ->onQueue('GetNewsContent');
        });

        return self::SUCCESS;
    }
}
