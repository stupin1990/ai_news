import { useEffect, useState, useRef, useLayoutEffect } from 'react';
import { useWindowVirtualizer } from '@tanstack/react-virtual';
import { NewsItem } from "../NewsPage";
import { NewsImage } from './NewsImage';


interface NewsListProps {
    newsItems: NewsItem[];
    expandedNewsIds: number[];
    isLoading: boolean;
    onToggleNews: (newsId: number) => void;
    onLastNewsRef: (node: HTMLDivElement | null) => void;
    formatPublishedAt: (value: string | null) => string;
}


export function NewsList({
    newsItems,
    expandedNewsIds,
    isLoading,
    onToggleNews,
    onLastNewsRef,
    formatPublishedAt,
    virtualizerRef,
}: NewsListProps & { virtualizerRef?: React.MutableRefObject<any> }) {
    const listRef = useRef<HTMLDivElement>(null);
    const [scrollMargin, setScrollMargin] = useState(0);

    useLayoutEffect(() => {
        if (!listRef.current) return;

        const observer = new ResizeObserver(() => {
            if (listRef.current) {
                setScrollMargin(listRef.current.offsetTop);
            }
        });

        observer.observe(document.body);

        return () => observer.disconnect();
    }, []);

    const virtualizer = useWindowVirtualizer({
        count: newsItems.length,
        estimateSize: () => 160, // Approximate height of a collapsed news item
        overscan: 5, // 5 items before and after the visible area (total ~15-20 items rendered at a time)
        scrollMargin,
    });

    useEffect(() => {
        if (virtualizerRef) {
            virtualizerRef.current = virtualizer;
        }
    }, [virtualizer, virtualizerRef]);

    return (
        <div ref={listRef} className="mt-6">
            <div
                style={{
                    height: `${virtualizer.getTotalSize()}px`,
                    width: '100%',
                    position: 'relative',
                }}
            >
                {virtualizer.getVirtualItems().map((virtualItem) => {
                    const newsItem = newsItems[virtualItem.index];
                    const isExpanded = expandedNewsIds.includes(newsItem.id);
                    const isLastItem = virtualItem.index === newsItems.length - 1;

                    return (
                        <div
                            key={virtualItem.key}
                            data-index={virtualItem.index}
                            ref={(node) => {
                                virtualizer.measureElement(node);
                                if (isLastItem) {
                                    onLastNewsRef(node);
                                }
                            }}
                            style={{
                                position: 'absolute',
                                top: 0,
                                left: 0,
                                width: '100%',
                                transform: `translateY(${virtualItem.start - virtualizer.options.scrollMargin}px)`,
                            }}
                            className="pb-4"
                        >
                            <div className="rounded border border-gray-200 p-4 dark:border-[#3E3E3A]">
                                <button
                                    type="button"
                                    className="w-full cursor-pointer text-left"
                                    onClick={() => onToggleNews(newsItem.id)}
                                >
                                    <div className="flex items-start gap-4">
                                        <div className="flex h-28 w-44 shrink-0 items-center justify-center overflow-hidden bg-black">
                                            <NewsImage src={newsItem.image} alt={newsItem.title} />
                                        </div>
                                        <div className="min-w-0">
                                            <h2 className="text-xl font-semibold">{newsItem.title}</h2>
                                            <p className="mt-2 text-sm text-gray-600 dark:text-[#A1A09A]">
                                                {newsItem.categories.map((category) => category.name).join(', ') || 'No categories'}
                                            </p>
                                            <p className="mt-2 text-sm text-gray-500 dark:text-[#A1A09A]">
                                                {formatPublishedAt(newsItem.publishedAt)}
                                            </p>
                                        </div>
                                    </div>
                                </button>

                                <div
                                    className={`grid transition-all duration-300 ease-in-out ${isExpanded ? 'mt-4 grid-rows-[1fr] opacity-100' : 'mt-0 grid-rows-[0fr] opacity-0'}`}
                                >
                                    <div className="overflow-hidden">
                                        <div className="border-t border-gray-200 pt-4 dark:border-[#3E3E3A]">
                                            <p className="whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-[#D6D6D1]">
                                                {newsItem.aiContent || 'No description'}
                                            </p>
                                            <a
                                                href={newsItem.sourceUrl}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="mt-3 inline-block text-sm text-gray-600 underline underline-offset-4 hover:text-gray-800 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                                            >
                                                Read full news
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>

            {isLoading && (
                <div className="flex justify-center py-4">
                    <div className="h-8 w-8 animate-spin rounded-full border-2 border-gray-300 border-t-[#1b1b18] dark:border-[#3E3E3A] dark:border-t-[#EDEDEC]" />
                </div>
            )}
        </div>
    );
}
