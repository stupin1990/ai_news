<?php

namespace Tests\Feature;

use App\Enums\NewsStatus;
use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsFeedAnchorPaginationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_feed_uses_anchor_news_id_for_stable_page_two_results(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $basePublishedAt = now()->subHour();

        for ($index = 0; $index < 45; $index++) {
            $news = News::query()->create([
                'title' => 'Baseline news ' . $index,
                'slug' => 'baseline-news-' . $index,
                'image' => null,
                'source_url' => 'https://example.com/baseline-' . $index,
                'external_id' => 'baseline-' . $index,
                'raw_content' => null,
                'ai_content' => 'content ' . $index,
                'status' => NewsStatus::DONE->value,
                'source' => 'example',
                'published_at' => $basePublishedAt->copy()->subMinute($index),
            ]);

            $news->categories()->attach($category->id);
        }

        $this->actingAs($user);

        $firstPageResponse = $this->getJson(route('news.feed', [
            'category_ids' => [$category->id],
            'page' => 1,
        ]));

        $firstPageResponse->assertOk();

        $firstPageItems = $firstPageResponse->json('items');
        $this->assertCount(20, $firstPageItems);

        $anchorNewsId = (int) $firstPageItems[0]['id'];

        $baselinePageTwoResponse = $this->getJson(route('news.feed', [
            'category_ids' => [$category->id],
            'page' => 2,
            'anchor_news_id' => $anchorNewsId,
        ]));

        $baselinePageTwoResponse->assertOk();

        $expectedPageTwoIds = collect($baselinePageTwoResponse->json('items'))
            ->map(static fn (array $item): int => (int) $item['id'])
            ->values()
            ->all();

        for ($index = 0; $index < 3; $index++) {
            $news = News::query()->create([
                'title' => 'Fresh news ' . $index,
                'slug' => 'fresh-news-' . $index,
                'image' => null,
                'source_url' => 'https://example.com/fresh-' . $index,
                'external_id' => 'fresh-' . $index,
                'raw_content' => null,
                'ai_content' => 'fresh content ' . $index,
                'status' => NewsStatus::DONE->value,
                'source' => 'example',
                'published_at' => now()->addMinute($index + 1),
            ]);

            $news->categories()->attach($category->id);
        }

        $anchoredPageTwoResponse = $this->getJson(route('news.feed', [
            'category_ids' => [$category->id],
            'page' => 2,
            'anchor_news_id' => $anchorNewsId,
        ]));

        $anchoredPageTwoResponse->assertOk();

        $anchoredPageTwoIds = collect($anchoredPageTwoResponse->json('items'))
            ->map(static fn (array $item): int => (int) $item['id'])
            ->values()
            ->all();

        $this->assertSame($expectedPageTwoIds, $anchoredPageTwoIds);

        $unanchoredPageTwoResponse = $this->getJson(route('news.feed', [
            'category_ids' => [$category->id],
            'page' => 2,
        ]));

        $unanchoredPageTwoResponse->assertOk();

        $unanchoredPageTwoIds = collect($unanchoredPageTwoResponse->json('items'))
            ->map(static fn (array $item): int => (int) $item['id'])
            ->values()
            ->all();

        $this->assertNotSame($expectedPageTwoIds, $unanchoredPageTwoIds);
    }
}
