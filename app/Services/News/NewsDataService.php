<?php

namespace App\Services\News;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsDataService extends NewsService
{
    public static string $lastNewsUrl = 'https://newsdata.io/api/1/latest';
    public static string $name = 'newsdataio';

    public function serializeItem(array $newsItem): array
    {
        if (empty($newsItem['article_id']) || empty($newsItem['title']) || empty($newsItem['link']) || empty($newsItem['fetched_at'])) {
            return [];
        }

        return [
            'source' => self::$name,
            'external_id'  => $newsItem['article_id'],
            'title'        => $newsItem['title'],
            'slug'         => Str::slug($newsItem['title']),
            'source_url'   => $newsItem['link'],
            'image'        => $newsItem['image_url'] ?? null,
            'published_at' => Carbon::parse($newsItem['fetched_at']),
        ];
    }

    public function getLastNews(string $lang = 'en'): array
    {
        /** @var array */
        $result = [];

        try {
            /** @var \Illuminate\Http\Client\Response|\GuzzleHttp\Promise\PromiseInterface */
            $response = Http::timeout(10)->retry(3, 100)
                ->get(self::$lastNewsUrl, [
                    'language'        => $lang,
                    'apikey'          => $this->apiKey,
                    'removeduplicate' => 1
                ]);

            if ($response->failed()) {
                if ($response->status() !== 403 && $response->status() !== 429) {
                    Log::error("NewsData API Error: " . $response->body());
                }
                return [];
            }

            $data = $response->json();
            if (empty($data['results'])) {
                Log::error("NewsData API Error: parse data error");
                return [];
            }

            foreach ($data['results'] as $article) {
                $result[] = $this->serializeItem($article);
            }

            $result = array_filter($result, function($item) {
                return count($item);
            });
        } catch (\Exception $e) {
            Log::error("NewsData Service Exception: " . $e->getMessage());
        } finally {
            return $result;
        }
    }

    public static function getLastNewsMock(): array {
        return [
            'results' => [
                [
                    'article_id' => 'mock-2',
                    'title' => 'Mock news 2',
                    'link' => 'https://example.test/2',
                    'image_url' => 'https://example.test/images/2.jpg',
                    'fetched_at' => now()->toIso8601String(),
                ],
            ],
        ];
    }

}