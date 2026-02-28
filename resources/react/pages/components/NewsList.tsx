import { useEffect, useState, useRef, useLayoutEffect } from 'react';
import { useWindowVirtualizer } from '@tanstack/react-virtual';
import { NewsItem } from "../NewsPage";
import { NewsListItem } from './NewsListItem';


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
}: NewsListProps & { virtualizerRef?: React.RefObject<any> }) {
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
        estimateSize: () => 160,
        overscan: 5,
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
                    const translateY = virtualItem.start - virtualizer.options.scrollMargin;

                    return (
                        <NewsListItem
                            key={virtualItem.key}
                            newsItem={newsItem}
                            isExpanded={isExpanded}
                            isLastItem={isLastItem}
                            index={virtualItem.index}
                            translateY={translateY}
                            onMeasureElement={virtualizer.measureElement}
                            onToggleNews={onToggleNews}
                            onLastNewsRef={onLastNewsRef}
                            formatPublishedAt={formatPublishedAt}
                        />
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
