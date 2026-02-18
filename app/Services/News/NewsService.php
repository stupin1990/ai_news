<?php

namespace App\Services\News;

use Illuminate\Support\Carbon;

/**
 * @phpstan-type serializedItem array{source:string,external_id:string,title:string,slug:string,source_url:string,image:string,published_at:string}
 */
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
     * @return serializedItem
     */
    abstract function serializeItem(array $newsItem): array;

    /**
     * Get last news
     * @param string $lang
     * @return list<serializedItem>
     */
    abstract function getLastNews(string $lang = 'en'): array;


    /**
     * Mock data for get last news
     * @return list<serializedItem>
     */
    abstract static function getLastNewsMock(): array;

}