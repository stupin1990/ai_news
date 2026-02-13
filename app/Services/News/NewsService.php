<?php

namespace App\Services\News;

use Illuminate\Support\Carbon;

abstract class NewsService
{
    protected string $apiKey;
    public static string $name;
    public static string $lastNewsUrl;


    public function __construct()
    {
        $this->apiKey = config('services.' . static::$name . '.apikey');
    }

    /**
     * Serialize news item to model format
     * @param array $newsItem
     * 
     * @return array [source, external_id, title, slug, source_url, image, published_at]
     */
    abstract function serializeItem(array $newsItem): array;

    /**
     * Get last news
     * @param string $lang
     * @return array[] [source, external_id, title, slug, source_url, image, published_at]
     */
    abstract function getLastNews(string $lang = 'en'): array;


    /**
     * Mock data for get last news
     * @return array[] [source, external_id, title, slug, source_url, image, published_at]
     */
    abstract static function getLastNewsMock(): array;

}