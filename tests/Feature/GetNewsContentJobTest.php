<?php

namespace Tests\Feature;

use App\Enums\NewsStatus;
use App\Jobs\GetAiNewsContent;
use App\Jobs\GetNewsContent;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GetNewsContentJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_news_content_job_parses_content_and_dispatches_ai_job(): void
    {
        $news = News::query()->create([
            'title' => 'Test News',
            'slug' => 'test-news',
            'source_url' => 'https://example.test/news/1',
            'external_id' => 'external-1',
            'status' => NewsStatus::NEW,
            'source' => 'test',
            'published_at' => now(),
        ]);

        Bus::fake();
        Http::preventStrayRequests();
        Http::fake([
            'https://r.jina.ai/*' => Http::response([
                'data' => [
                    'content' => 'Parsed content body',
                ],
            ], 200),
        ]);

        $job = new GetNewsContent($news);
        $job->handle();

        $news->refresh();

        $this->assertSame(NewsStatus::CONTENT_PARSED, $news->status);
        $this->assertSame('Parsed content body', $news->raw_content);

        Bus::assertDispatched(GetAiNewsContent::class, 1);
        Http::assertSentCount(1);
    }

    public function test_get_news_content_job_sets_error_status_when_content_is_missing(): void
    {
        $news = News::query()->create([
            'title' => 'Broken News',
            'slug' => 'broken-news',
            'source_url' => 'https://example.test/news/2',
            'external_id' => 'external-2',
            'status' => NewsStatus::NEW,
            'source' => 'test',
            'published_at' => now(),
        ]);

        Bus::fake();
        Http::preventStrayRequests();
        Http::fake([
            'https://r.jina.ai/*' => Http::response([
                'data' => [],
            ], 200),
        ]);

        $job = new GetNewsContent($news);
        $job->handle();

        $news->refresh();

        $this->assertSame(NewsStatus::CONTENT_ERROR, $news->status);
        Bus::assertNotDispatched(GetAiNewsContent::class);
        Http::assertSentCount(1);
    }
}
