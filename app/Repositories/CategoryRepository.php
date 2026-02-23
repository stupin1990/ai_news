<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CategoryRepository 
{
    /**
     * @param User $user
     * 
     * @return array<int>
     */
    public static function getUserSelectedCategoryIds(User $user): array
    {
        /** @var array<int,string> */
        return Cache::remember(
            'NewsController:userCategories_' . $user->id,
            now()->addHour(),
            function () use ($user): array {
                return $user->categories()
                    ->select('categories.id')
                    ->orderBy('categories.id')
                    ->pluck('categories.id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->values()
                    ->all() ?? [];
            }
        );
    }

    /** 
     * @var array<int,string>
    */
    public static function getAllCategories(): array {
        return Cache::remember(
            'NewsController:categories',
            now()->addHour(),
            function (): array {
                return Category::query()
                    ->select(['id', 'name'])
                    ->orderBy('name')
                    ->get()
                    ->map(fn (Category $category): array => [
                        'id' => $category->id,
                        'name' => $category->name,
                    ])
                    ->values()
                    ->all();
            }
        );
    }
}