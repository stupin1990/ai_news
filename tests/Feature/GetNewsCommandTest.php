<?php

namespace Tests\Feature;

use App\Console\Commands\GetNews;
use App\Jobs\GetNewsContent;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GetNewsCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_get_news_command_persists_mocked_data_without_executing_jobs(): void
    {
        Queue::fake();

        $responses = [];
        foreach (GetNews::NEWS_SERVICES as $serviceClass) {
            $responses[$serviceClass::$lastNewsUrl . '*'] = Http::response($serviceClass::getLastNewsMock(), 200);
        }

        Http::preventStrayRequests();
        Http::fake($responses);

        $this->artisan('app:get-news')->assertSuccessful();

        $this->assertDatabaseCount('news', 2);

        $newsFirst = News::query()->where('external_id', 'mock-1')->first();
        $newsSecond = News::query()->where('external_id', 'mock-2')->first();

        $this->assertNotNull($newsFirst);
        $this->assertNotNull($newsSecond);

        Queue::assertPushed(GetNewsContent::class, 2);
    }
}
