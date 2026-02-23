<?php

namespace App\Http\Controllers;

use App\Enums\NewsStatus;
use App\Models\Category;
use App\Models\News;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class NewsController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $categories = CategoryRepository::getAllCategories();
        $selectedCategoryIds = CategoryRepository::getUserSelectedCategoryIds($request->user());
        $newsPaginator = $this->buildNewsPaginator($selectedCategoryIds, 1);

        return $this->react('NewsPage', [
            'appName' => config('app.name', 'Ai News'),
            'userName' => (string) $request->user()?->name,
            'categories' => $categories,
            'selectedCategoryIds' => $selectedCategoryIds,
            'initialNews' => $this->transformPaginator($newsPaginator),
            'routes' => [
                'logout' => route('logout'),
                'newsFeed' => route('news.feed'),
                'saveCategorySelection' => route('news.categories.update'),
            ],
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id', 'distinct'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $page = (int) ($validated['page'] ?? 1);
        $selectedCategoryIds = collect($validated['category_ids'] ?? [])
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        $newsPaginator = $this->buildNewsPaginator($selectedCategoryIds, $page);

        return response()->json($this->transformPaginator($newsPaginator));
    }

    public function updateCategories(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_ids' => ['array'],
            'category_ids.*' => ['integer', 'exists:categories,id', 'distinct'],
        ]);

        $selectedCategoryIds = [];
        if (!empty($validated['category_ids'])) {
            $selectedCategoryIds = collect($validated['category_ids'])
                ->map(static fn (mixed $id): int => (int) $id)
                ->values()
                ->all();
        }

        $request->user()?->categories()->sync($selectedCategoryIds);

        Cache::put('NewsController:userCategories_' . $request->user()->id, $selectedCategoryIds, now()->addHour());

        return response()->json([
            'selectedCategoryIds' => $selectedCategoryIds,
        ]);
    }

    /**
     * @param array<int, int> $selectedCategoryIds
     */
    private function buildNewsPaginator(array $selectedCategoryIds, int $page): LengthAwarePaginator
    {
        return News::query()
            ->with(['categories:id,name'])
            ->where('status', '=', NewsStatus::DONE->value)
            ->when(
                $selectedCategoryIds !== [],
                fn ($query) => $query->whereHas('categories', fn ($categoriesQuery) => $categoriesQuery->whereIn('categories.id', $selectedCategoryIds))
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(20, ['id','title','image', 'source_url', 'ai_content', 'published_at'], 'page', $page);
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, page: int, hasMore: bool}
     */
    private function transformPaginator(LengthAwarePaginator $newsPaginator): array
    {
        return [
            'items' => collect($newsPaginator->items())
                ->map(fn (News $news): array => [
                    'id' => $news->id,
                    'title' => $news->title,
                    'image' => $news->image,
                    'aiContent' => $news->ai_content,
                    'sourceUrl' => $news->source_url,
                    'publishedAt' => $news->published_at?->toIso8601String(),
                    'categories' => $news->categories
                        ->map(fn (Category $category): array => [
                            'id' => $category->id,
                            'name' => $category->name,
                        ])
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all(),
            'page' => $newsPaginator->currentPage(),
            'hasMore' => $newsPaginator->hasMorePages(),
        ];
    }
}
