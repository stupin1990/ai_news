<?php

namespace Tests\Feature;

use App\Console\Commands\GetNews;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GetNewsCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_get_news_command_persists_mocked_data(): void
    {
        foreach (GetNews::NEWS_SERVICES as $serviceClass) {
            Http::fake([
                $serviceClass::$lastNewsUrl . '*' => Http::response($serviceClass::getLastNewsMock(), 200)
            ]);
        }

        $this->artisan('app:get-news')->assertExitCode(0);

        $this->assertDatabaseCount('news', 2);

        $newsFirst = News::query()->where('external_id', 'mock-1')->first();
        $newsSecond = News::query()->where('external_id', 'mock-2')->first();

        $this->assertNotNull($newsFirst);
        $this->assertNotNull($newsSecond);
    }
}
