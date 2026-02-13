<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_categories', 'cat_id', 'user_id');
    }

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_categories', 'cat_id', 'news_id');
    }
}
