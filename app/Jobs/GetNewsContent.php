<?php

namespace App\Jobs;

use App\Enums\NewsStatus;
use App\Models\News;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetNewsContent implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    private News $item;

    private string $rJinaUrl = 'https://r.jina.ai/';

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
    public function handle(): void
    {
        $this->item->update([
            'status' => NewsStatus::CONTENT_PARSING,
        ]);

        try {
            /** @var \Illuminate\Http\Client\Response|\GuzzleHttp\Promise\PromiseInterface */
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.rjina.apikey'),
                'X-Return-Format' => 'markdown',
                'X-With-Links-Summary' => false,
                'X-No-Cache' => true,
                'Accept' => 'application/json',
                'X-Retain-Images' => 'none',
                'X-User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'X-Referer' => 'https://www.google.com/'
            ])
            ->timeout(60)
            ->get($this->rJinaUrl . $this->item->source_url);
            
            $response->throw();

            $data = $response->json();
            if (empty($data['data']['content'])) {
                throw new \Exception('No content:' . $this->item->source_url);
            }

            $content = $this->stripMarkdownLinks($data['data']['content']);

            $this->item->update([
                'raw_content' => $content,
                'status' => NewsStatus::CONTENT_PARSED,
            ]);

            GetAiNewsContent::dispatch($this->item)->onQueue('GetNewsAiContent');
        } catch (Throwable $e) {
            $this->item->update([
                'status' => NewsStatus::CONTENT_ERROR,
            ]);
            Log::error('GetNewsContent error: ' . $e->getMessage());
            $this->release();
        }
    }

    private function stripMarkdownLinks(string $text): string
    {
        $pattern = '/
            (?(DEFINE)
                (?<balanced_brackets> \[ (?: [^\[\]] | (?&balanced_brackets) )* \] )
                (?<balanced_parens> \( (?: [^\(\)] | (?&balanced_parens) )* \) )
            )
            (?&balanced_brackets) (?&balanced_parens)
        /ux';

        $result = preg_replace($pattern, '', $text);

        return trim($result);
    }
}
