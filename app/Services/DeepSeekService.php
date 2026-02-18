<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DeepSeekService
{
    private string $apiKey;
    private string $chatCompletionsUrl = 'https://api.deepseek.com/chat/completions';
    private string $model = 'deepseek-chat';

    public function __construct()
    {
        $this->apiKey = config('services.deepseek.apikey');
    }

    public function chatCompletions(array $promt, bool $json = false): string|array
    {
        /** @var array */
        $post = [
            'messages' => $promt,
            'model' => $this->model
        ];

        if ($json) {
            $post['response_format'] = ['type' => 'json_object'];
        }

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post($this->chatCompletionsUrl, $post);

        $response->throw();

        $data = $response->json();

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid chat completions response:' . json_encode($data));
        }

        $result = $data['choices'][0]['message']['content'];

        if ($json) {
            return json_decode($result, true);
        }

        return $data;
    }
}