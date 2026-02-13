<?php

namespace App\Console\Commands;

use App\Models\News;
use App\Services\GNewsService;
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

    /**
     * Execute the console command.
     */
    public function handle(GNewsService $gNewsService): int
    {
        echo 'Get news from gNews: ';

        $data = $gNewsService->getLastNews();

        if (!count($data)) {
             echo 'Fail' . PHP_EOL;
             return self::FAILURE;
        }

        News::upsert($data, uniqueBy: ['external_id'], update: ['title', 'slug', 'content', 'source_url', 'image', 'published_at']);
        echo 'Ok (' . count($data) .  ')' . PHP_EOL;

        return self::SUCCESS;
    }
}
