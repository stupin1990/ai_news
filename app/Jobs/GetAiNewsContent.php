<?php

namespace App\Jobs;

use App\Enums\NewsStatus;
use App\Models\Category;
use App\Models\News;
use App\Services\DeepSeekService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetAiNewsContent implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    private News $item;

    /**
     * Create a new job instance.
     */
    public function __construct(News $item)
    {
        $this->item = $item;
    }

    public function uniqueId(): string
    {
        return $this->item->id;
    }

    /**
     * Execute the job.
     */
    public function handle(DeepSeekService $ds): void
    {
        $this->item->update([
            'status' => NewsStatus::AI_GENERATING,
        ]);

        /** @var array<int,string> */
        $categories = Cache::store('redis')->remember(
            'ai_news:categories',
            now()->addHour(),
            function (): array {
                return Category::pluck('name', 'id')->all();
            }
        );
        $rCategories = array_flip($categories);

        $promt = [
            [
                'role' => 'system',
                'content' => 'You are a professional news analyst. Your task is to extract the core news content from the provided text, ignoring all irrelevant parsing artifacts such as menus, ads, headers, or footers. 1. Summarize the main event in a few concise sentences. 2. Assign the news to one or more of the following categories: ' . implode(', ', $categories) . '. You must respond in a valid JSON format with the keys \"summary\" (string) and \"categories\" (array of strings). If the provided text is not a news article, return empty string in summary and categories params."'
            ],
            [
                'role' => 'system',
                'content' => $this->item->raw_content
            ]
        ];

        try {
            $response = $ds->chatCompletions($promt, true);
            if (!isset($response['summary']) || !isset($response['categories'])) {
                throw new \Exception('Invalid response format:' . json_encode($response));
            }

            if (!$response['summary']) {
                $this->item->update([
                    'status' => NewsStatus::CONTENT_ERROR,
                ]);
                Log::error('GetAiNewsContent error: Wrong content for news: ' . $this->item->id);
                return;
            }

            /** @var array<int,int> */
            $cats = [];
            foreach ($response['categories'] as $cat) {
                if (isset($rCategories[$cat])) {
                    $cats[] = $rCategories[$cat];
                }
            }

            $this->item->update([
                'ai_content' => $response['summary'],
                'status' => NewsStatus::DONE,
            ]);

            $this->item->categories()->attach($cats);
        } catch (Throwable $e) {
            $this->item->update([
                'status' => NewsStatus::AI_ERROR,
            ]);
            Log::error('GetAiNewsContent error: ' . $e->getMessage());
            $this->release();
        }
    }
}
