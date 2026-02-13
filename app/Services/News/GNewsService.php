<?php

namespace App\Services\News;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GNewsService extends NewsService
{

    public static string $lastNewsUrl = 'https://gnews.io/api/v4/top-headlines';
    public static string $name = 'gnewsio';

    public function serializeItem(array $newsItem): array
    {
        if (empty($newsItem['id']) || empty($newsItem['title']) || empty($newsItem['url']) || empty($newsItem['publishedAt'])) {
            return [];
        }

        return [
            'source' => self::$name,
            'external_id'  => $newsItem['id'],
            'title'        => $newsItem['title'],
            'slug'         => Str::slug($newsItem['title']),
            'source_url'   => $newsItem['url'],
            'image'        => $newsItem['image'] ?? null,
            'published_at' => Carbon::parse($newsItem['publishedAt']),
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
                    'lang'     => $lang,
                    'apikey'   => $this->apiKey,
                ]);

            if ($response->failed()) {
                if ($response->status() !== 403 && $response->status() !== 429) {
                    Log::error("GNews API Error: " . $response->body());
                }
                return [];
            }

            $data = $response->json();
            if (empty($data['articles'])) {
                Log::error("GNews API Error: parse data error");
                return [];
            }

            foreach ($data['articles'] as $article) {
                $result[] = $this->serializeItem($article);
            }

            $result = array_filter($result, function($item) {
                return count($item);
            });
        } catch (\Exception $e) {
            Log::error("GNews Service Exception: " . $e->getMessage());
        } finally {
            return $result;
        }
    }

    public static function getLastNewsMock(): array {
        return [
            'articles' => [
                [
                    'id' => 'mock-1',
                    'title' => 'Mock news 1',
                    'url' => 'https://example.test/1',
                    'image' => 'https://example.test/images/1.jpg',
                    'publishedAt' => now()->toIso8601String(),
                ],
            ],
        ];
    }

}