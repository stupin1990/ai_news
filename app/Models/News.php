<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\NewsStatus;

class News extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'slug',
        'image',
        'source_url',
        'external_id',
        'raw_content',
        'ai_content',
        'status',
        'source',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => NewsStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'news_categories', 'news_id', 'cat_id');
    }
}
