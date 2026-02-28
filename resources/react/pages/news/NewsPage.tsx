import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { getCsrfToken } from '../../utils/csrf';
import { CategoryFilter, NewArticlesBadge, NewsHeader, NewsList, ScrollTopButton } from './components';

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

    const toTimestamp = useCallback((value: string | null): number | null => {
        if (value === null) {
            return null;
        }

        const timestamp = new Date(value).getTime();

        return Number.isNaN(timestamp) ? null : timestamp;
    }, []);

    const getLatestPublishedAt = useCallback((items: NewsItem[]): string | null => {
        let latestPublishedAt: string | null = null;
        let latestTimestamp: number | null = null;

        items.forEach((item) => {
            const itemTimestamp = toTimestamp(item.publishedAt);

            if (itemTimestamp === null) {
                return;
            }

            if (latestTimestamp === null || itemTimestamp > latestTimestamp) {
                latestTimestamp = itemTimestamp;
                latestPublishedAt = item.publishedAt;
            }
        });

        return latestPublishedAt;
    }, [toTimestamp]);

    const csrfToken = useMemo(() => getCsrfToken(), []);
    const [isFilterOpen, setIsFilterOpen] = useState(false);
    const [activeCategoryIds, setActiveCategoryIds] = useState<number[]>(selectedCategoryIds);
    const [newsItems, setNewsItems] = useState<NewsItem[]>(initialNews.items);
    const [latestPublishedAt, setLatestPublishedAt] = useState<string | null>(() => getLatestPublishedAt(initialNews.items));
    const [hasNewArticles, setHasNewArticles] = useState(false);
    const [page, setPage] = useState(initialNews.page);
    const [hasMore, setHasMore] = useState(initialNews.hasMore);
    const [isLoading, setIsLoading] = useState(false);
    const [expandedNewsIds, setExpandedNewsIds] = useState<number[]>([]);
    const [showScrollTop, setShowScrollTop] = useState(false);
    const observerRef = useRef<IntersectionObserver | null>(null);
    const newsFeedAbortRef = useRef<AbortController | null>(null);
    const saveCategoryAbortRef = useRef<AbortController | null>(null);
    const pollAbortRef = useRef<AbortController | null>(null);
    const categoriesDebounceTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const firstNewsIdRef = useRef<number | null>(initialNews.items[0]?.id ?? null);
    const virtualizerRef = useRef<any>(null);

    const buildNewsFeedParams = useCallback((
        targetPage: number,
        categoryIds: number[],
        anchorNewsId: number | null = null,
    ): URLSearchParams => {
        const params = new URLSearchParams();

        params.set('page', String(targetPage));

        if (targetPage > 1 && anchorNewsId !== null) {
            params.set('anchor_news_id', String(anchorNewsId));
        }

        categoryIds.forEach((categoryId) => {
            params.append('category_ids[]', String(categoryId));
        });

        return params;
    }, []);

    const requestNewsFeed = useCallback(async (
        params: URLSearchParams,
        signal: AbortSignal,
    ): Promise<NewsPayload | null> => {
        const response = await fetch(`${routes.newsFeed}?${params.toString()}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            signal,
        });

        if (!response.ok) {
            return null;
        }

        return response.json() as Promise<NewsPayload>;
    }, [routes.newsFeed]);

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
            const anchorNewsId = firstNewsIdRef.current;
            const params = buildNewsFeedParams(targetPage, categoryIds, anchorNewsId);
            const payload = await requestNewsFeed(params, abortController.signal);

            if (newsFeedAbortRef.current !== abortController || payload === null) {
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
    }, [activeCategoryIds, buildNewsFeedParams, requestNewsFeed]);

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

    const scheduleCategorySync = useCallback((categoryIds: number[]): void => {
        if (categoriesDebounceTimerRef.current !== null) {
            clearTimeout(categoriesDebounceTimerRef.current);
        }

        setHasNewArticles(false);

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

    const checkForNewArticles = useCallback(async (): Promise<void> => {
        const knownLatestTimestamp = toTimestamp(latestPublishedAt);

        if (knownLatestTimestamp === null) {
            return;
        }

        pollAbortRef.current?.abort();
        const abortController = new AbortController();
        pollAbortRef.current = abortController;

        try {
            const params = buildNewsFeedParams(1, activeCategoryIds);
            const payload = await requestNewsFeed(params, abortController.signal);

            if (pollAbortRef.current !== abortController || payload === null) {
                return;
            }

            const fetchedLatestPublishedAt = getLatestPublishedAt(payload.items);
            const fetchedLatestTimestamp = toTimestamp(fetchedLatestPublishedAt);

            if (fetchedLatestTimestamp !== null && fetchedLatestTimestamp > knownLatestTimestamp) {
                setHasNewArticles(true);
            }
        } catch (error) {
            if (error instanceof DOMException && error.name === 'AbortError') {
                return;
            }

            throw error;
        } finally {
            if (pollAbortRef.current === abortController) {
                pollAbortRef.current = null;
            }
        }
    }, [activeCategoryIds, buildNewsFeedParams, getLatestPublishedAt, latestPublishedAt, requestNewsFeed, toTimestamp]);

    const refreshFeedFromTop = useCallback((): void => {
        window.scrollTo({ top: 0 });
        setHasNewArticles(false);
        setExpandedNewsIds([]);
        void fetchNews(1, true, activeCategoryIds);
    }, [activeCategoryIds, fetchNews]);

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

    const toggleNews = useCallback((newsId: number): void => {
        setExpandedNewsIds((currentIds) => (
            currentIds.includes(newsId)
                ? currentIds.filter((id) => id !== newsId)
                : [...currentIds, newsId]
        ));
    }, []);

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


    useEffect(() => {
        const onScroll = (): void => {
            setShowScrollTop(window.scrollY > 300);
        };

        onScroll();
        window.addEventListener('scroll', onScroll);

        return (): void => {
            window.removeEventListener('scroll', onScroll);

            if (categoriesDebounceTimerRef.current !== null) {
                clearTimeout(categoriesDebounceTimerRef.current);
            }

            newsFeedAbortRef.current?.abort();
            saveCategoryAbortRef.current?.abort();
            pollAbortRef.current?.abort();
            observerRef.current?.disconnect();
        };
    }, []);


    useEffect(() => {
        setLatestPublishedAt(getLatestPublishedAt(newsItems));
        firstNewsIdRef.current = newsItems[0]?.id ?? null;
    }, [getLatestPublishedAt, newsItems]);


    useEffect(() => {
        const intervalId = window.setInterval(() => {
            void checkForNewArticles();
        }, 5 * 60 * 1000);

        return (): void => {
            window.clearInterval(intervalId);
            pollAbortRef.current?.abort();
        };
    }, [checkForNewArticles]);

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
                    virtualizerRef={virtualizerRef}
                />
            </main>
            <ScrollTopButton
                isVisible={showScrollTop}
                onClick={() => {
                    window.scrollTo({ top: 0 });
                }}
            />
            <NewArticlesBadge
                isVisible={hasNewArticles}
                onClick={refreshFeedFromTop}
            />
        </>
    );
}