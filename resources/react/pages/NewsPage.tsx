import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { getCsrfToken } from '../utils/csrf';
import { CategoryFilter, NewsHeader, NewsList, ScrollTopButton } from './components';

export interface Category {
    id: number;
    name: string;
}

export interface NewsItem {
    id: number;
    title: string;
    image: string | null;
    aiContent: string | null;
    sourceUrl: string;
    publishedAt: string | null;
    categories: Category[];
}

interface NewsPayload {
    items: NewsItem[];
    page: number;
    hasMore: boolean;
}

interface NewsPageProps {
    appName: string;
    userName: string;
    categories: Category[];
    selectedCategoryIds: number[];
    initialNews: NewsPayload;
    routes: {
        logout: string;
        newsFeed: string;
        saveCategorySelection: string;
    };
}

export function NewsPage({
    appName,
    userName,
    categories,
    selectedCategoryIds,
    initialNews,
    routes,
}: NewsPageProps) {
    const csrfToken = useMemo(() => getCsrfToken(), []);
    const [isFilterOpen, setIsFilterOpen] = useState(false);
    const [activeCategoryIds, setActiveCategoryIds] = useState<number[]>(selectedCategoryIds);
    const [newsItems, setNewsItems] = useState<NewsItem[]>(initialNews.items);
    const [page, setPage] = useState(initialNews.page);
    const [hasMore, setHasMore] = useState(initialNews.hasMore);
    const [isLoading, setIsLoading] = useState(false);
    const [expandedNewsIds, setExpandedNewsIds] = useState<number[]>([]);
    const [showScrollTop, setShowScrollTop] = useState(false);
    const observerRef = useRef<IntersectionObserver | null>(null);
    const newsFeedAbortRef = useRef<AbortController | null>(null);
    const saveCategoryAbortRef = useRef<AbortController | null>(null);
    const categoriesDebounceTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const formatPublishedAt = useCallback((value: string | null): string => {
        if (value === null) {
            return '—';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return '—';
        }

        const pad = (unit: number): string => String(unit).padStart(2, '0');

        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
    }, []);

    const fetchNews = useCallback(async (
        targetPage: number,
        replaceItems = false,
        categoryIds: number[] = activeCategoryIds,
    ): Promise<void> => {
        newsFeedAbortRef.current?.abort();
        const abortController = new AbortController();
        newsFeedAbortRef.current = abortController;

        setIsLoading(true);

        try {
            const params = new URLSearchParams();

            params.set('page', String(targetPage));
            categoryIds.forEach((categoryId) => {
                params.append('category_ids[]', String(categoryId));
            });

            const response = await fetch(`${routes.newsFeed}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
                signal: abortController.signal,
            });

            if (newsFeedAbortRef.current !== abortController) {
                return;
            }

            if (!response.ok) {
                return;
            }

            const payload: NewsPayload = await response.json();

            if (newsFeedAbortRef.current !== abortController) {
                return;
            }

            setNewsItems((currentItems) => (replaceItems ? payload.items : [...currentItems, ...payload.items]));
            setPage(payload.page);
            setHasMore(payload.hasMore);
        } catch (error) {
            if (error instanceof DOMException && error.name === 'AbortError') {
                return;
            }

            throw error;
        } finally {
            if (newsFeedAbortRef.current === abortController) {
                newsFeedAbortRef.current = null;
                setIsLoading(false);
            }
        }
    }, [activeCategoryIds, routes.newsFeed]);

    const saveSelectedCategories = useCallback(async (categoryIds: number[]): Promise<void> => {
        saveCategoryAbortRef.current?.abort();
        const abortController = new AbortController();
        saveCategoryAbortRef.current = abortController;

        try {
            await fetch(routes.saveCategorySelection, {
                method: 'PUT',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ category_ids: categoryIds }),
                signal: abortController.signal,
            });
        } catch (error) {
            if (error instanceof DOMException && error.name === 'AbortError') {
                return;
            }

            throw error;
        } finally {
            if (saveCategoryAbortRef.current === abortController) {
                saveCategoryAbortRef.current = null;
            }
        }
    }, [csrfToken, routes.saveCategorySelection]);

    useEffect(() => {
        const onScroll = (): void => {
            setShowScrollTop(window.scrollY > 300);
        };

        onScroll();
        window.addEventListener('scroll', onScroll);

        return (): void => {
            window.removeEventListener('scroll', onScroll);
        };
    }, []);

    useEffect(() => {
        return (): void => {
            if (categoriesDebounceTimerRef.current !== null) {
                clearTimeout(categoriesDebounceTimerRef.current);
            }

            newsFeedAbortRef.current?.abort();
            saveCategoryAbortRef.current?.abort();
            observerRef.current?.disconnect();
        };
    }, []);

    const scheduleCategorySync = useCallback((categoryIds: number[]): void => {
        if (categoriesDebounceTimerRef.current !== null) {
            clearTimeout(categoriesDebounceTimerRef.current);
        }

        const categoryIdsSnapshot = [...categoryIds];

        categoriesDebounceTimerRef.current = setTimeout(() => {
            void (async (): Promise<void> => {
                setExpandedNewsIds([]);
                setNewsItems([]);
                setHasMore(true);
                setPage(1);
                setIsLoading(true);
                await saveSelectedCategories(categoryIdsSnapshot);
                await fetchNews(1, true, categoryIdsSnapshot);
            })();
        }, 1000);
    }, [fetchNews, saveSelectedCategories]);

    const toggleCategory = useCallback((categoryId: number): void => {
        const nextCategoryIds = activeCategoryIds.includes(categoryId)
            ? activeCategoryIds.filter((id) => id !== categoryId)
            : [...activeCategoryIds, categoryId];

        setActiveCategoryIds(nextCategoryIds);
        scheduleCategorySync(nextCategoryIds);
    }, [activeCategoryIds, scheduleCategorySync]);

    const resetCategory = useCallback((): void => {
        setActiveCategoryIds([]);
        scheduleCategorySync([]);
    }, [scheduleCategorySync]);

    const toggleNews = (newsId: number): void => {
        setExpandedNewsIds((currentIds) => (
            currentIds.includes(newsId)
                ? currentIds.filter((id) => id !== newsId)
                : [...currentIds, newsId]
        ));
    };

    const lastNewsRef = useCallback((node: HTMLDivElement | null): void => {
        if (isLoading || !hasMore) {
            return;
        }

        if (observerRef.current !== null) {
            observerRef.current.disconnect();
        }

        observerRef.current = new IntersectionObserver((entries) => {
            if (entries[0]?.isIntersecting === true) {
                fetchNews(page + 1);
            }
        });

        if (node !== null) {
            observerRef.current.observe(node);
        }
    }, [fetchNews, hasMore, isLoading, page]);

    return (
        <>
            <NewsHeader
                appName={appName}
                userName={userName}
                logoutRoute={routes.logout}
                csrfToken={csrfToken}
            />
            <main className="mx-auto w-full max-w-6xl p-6">
                <CategoryFilter
                    categories={categories}
                    activeCategoryIds={activeCategoryIds}
                    isFilterOpen={isFilterOpen}
                    onToggleFilter={() => setIsFilterOpen((state) => !state)}
                    onToggleCategory={toggleCategory}
                    onResetCategory={resetCategory}
                />

                <NewsList
                    newsItems={newsItems}
                    expandedNewsIds={expandedNewsIds}
                    isLoading={isLoading}
                    onToggleNews={toggleNews}
                    onLastNewsRef={lastNewsRef}
                    formatPublishedAt={formatPublishedAt}
                />
            </main>
            <ScrollTopButton
                isVisible={showScrollTop}
                onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
            />
        </>
    );
}