<?php

namespace App\Console\Commands;

use App\Jobs\GetAiNewsContent;
use App\Models\News;
use Illuminate\Console\Command;

class TestAiGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-ai-generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get last news from api';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $item = News::find(351);
        GetAiNewsContent::dispatchSync($item);

        return self::SUCCESS;
    }
}
