<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'cat_id',
        'title',
        'slug',
        'image',
        'source_url',
        'external_id',
        'content',
        'ai_content',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }
}
