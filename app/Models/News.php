<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class News extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'title',
        'slug',
        'image',
        'source_url',
        'external_id',
        'content',
        'status',
        'source',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'news_categories', 'news_id', 'cat_id');
    }
}
