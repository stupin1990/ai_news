<?php

namespace Tests\Feature;

use App\Enums\NewsStatus;
use App\Jobs\GetAiNewsContent;
use App\Models\Category;
use App\Models\News;
use App\Services\DeepSeekService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GetAiNewsContentJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_ai_news_content_job_updates_news_and_attaches_categories(): void
    {
        $techCategory = Category::query()->create([
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $news = News::query()->create([
            'title' => 'AI Update',
            'slug' => 'ai-update',
            'source_url' => 'https://example.test/news/3',
            'external_id' => 'external-3',
            'status' => NewsStatus::CONTENT_PARSED,
            'raw_content' => 'Raw content for processing',
            'source' => 'test',
            'published_at' => now(),
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://api.deepseek.com/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'summary' => 'AI summary text',
                                'categories' => ['Tech', 'Unknown'],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $job = new GetAiNewsContent($news);
        $job->handle(app(DeepSeekService::class));

        $news->refresh();

        $this->assertSame(NewsStatus::DONE, $news->status);
        $this->assertSame('AI summary text', $news->ai_content);

        $this->assertDatabaseHas('news_categories', [
            'news_id' => $news->id,
            'cat_id' => $techCategory->id,
        ]);

        Http::assertSentCount(1);
    }

    public function test_get_ai_news_content_job_sets_content_error_for_empty_summary(): void
    {
        Category::query()->create([
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $news = News::query()->create([
            'title' => 'Not a news article',
            'slug' => 'not-a-news-article',
            'source_url' => 'https://example.test/news/4',
            'external_id' => 'external-4',
            'status' => NewsStatus::CONTENT_PARSED,
            'raw_content' => 'Random non-news text',
            'source' => 'test',
            'published_at' => now(),
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://api.deepseek.com/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'summary' => '',
                                'categories' => [],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $job = new GetAiNewsContent($news);
        $job->handle(app(DeepSeekService::class));

        $news->refresh();

        $this->assertSame(NewsStatus::CONTENT_ERROR, $news->status);
        $this->assertNull($news->ai_content);
        $this->assertDatabaseCount('news_categories', 0);

        Http::assertSentCount(1);
    }
}
