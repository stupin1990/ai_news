<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GNewsService
{
    private string $apiKey;
    private string $baseUrl = 'https://gnews.io/api/v4/';

    public function __construct()
    {
        $this->apiKey = config('services.gnews.apikey');
    }

    /**
     * Serialize news item to model format
     * @param array $newsItem
     * 
     * @return array [id, title, url, image, publishedAt]
     */
    public function serializeItem(array $newsItem): array
    {
        if (empty($newsItem['id']) || empty($newsItem['title']) || empty($newsItem['url']) || empty($newsItem['publishedAt'])) {
            return [];
        }

        return [
            'external_id'  => $newsItem['id'],
            'title'        => $newsItem['title'],
            'slug'         => Str::slug($newsItem['title']),
            'source_url'   => $newsItem['url'],
            'image'        => $newsItem['image'] ?? null,
            'published_at' => Carbon::parse($newsItem['publishedAt']),
        ];
    }

    /**
     * Get last news from gnews api.
     * @param string $lang
     * @return array [id, title, url, image, publishedAt]
     */
    public function getLastNews(string $lang = 'en', int $page = 1): array
    {
        /** @var array */
        $result = [];

        try {
            /** @var \Illuminate\Http\Client\Response|\GuzzleHttp\Promise\PromiseInterface */
            $response = Http::timeout(10)->retry(3, 100)
                ->get($this->baseUrl . 'top-headlines', [
                    'lang'     => $lang,
                    'max'      => 10,
                    'apikey'   => $this->apiKey,
                    'page'     => $page
                ]);

            if ($response->failed()) {
                if ($response->status() !== 403 && $response->status() !== 403) {
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


    /**
     * Mock data for get last news.
     * @param string $lang
     * @param int $page
     * @return array [external_id, title, url, image, published_at]
     */
    public function getLastNewsMock(string $lang = 'en', int $page = 1): array
    {
        $items = [
            [
                'id' => "mock-{$page}-1",
                'title' => "Mock news {$page}-1",
                'url' => "https://example.test/{$page}/1",
                'image' => "https://example.test/images/{$page}-1.jpg",
                'publishedAt' => Carbon::now()->subMinutes($page)->toIso8601String(),
            ],
        ];

        $result = [];
        foreach ($items as $item) {
            $result[] = $this->serializeItem($item);
        }

        return array_values(array_filter($result, function (array $item): bool {
            return count($item) > 0;
        }));
    }

}