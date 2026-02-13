<?php

namespace Tests\Feature;

use App\Models\News;
use App\Services\GNewsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class GetNewsCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_get_news_command_persists_mocked_data(): void
    {
        $mockDataService = new GNewsService();

        $this->partialMock(GNewsService::class, function (MockInterface $mock) use ($mockDataService): void {
            $mock->shouldReceive('getLastNews')
                ->times(1)
                ->andReturnUsing(function (string $lang = 'en', int $page = 1) use ($mockDataService): array {
                    return $mockDataService->getLastNewsMock($lang, $page);
                });
        });

        $this->artisan('app:get-news')->assertExitCode(0);

        $this->assertDatabaseCount('news', 1);

        $news = News::query()->where('external_id', 'mock-1-1')->first();

        $this->assertNotNull($news);
    }
}
