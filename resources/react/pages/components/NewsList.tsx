import { useEffect, useState } from 'react';
import { Category, NewsItem } from "../NewsPage";


interface NewsListProps {
    newsItems: NewsItem[];
    expandedNewsIds: number[];
    isLoading: boolean;
    onToggleNews: (newsId: number) => void;
    onLastNewsRef: (node: HTMLDivElement | null) => void;
    formatPublishedAt: (value: string | null) => string;
}

type ImageStatus = 'loading' | 'success' | 'error';

interface NewsImageProps {
    src: string | null;
    alt: string;
}

function NewsImage({ src, alt }: NewsImageProps) {
    const [status, setStatus] = useState<ImageStatus>(src ? 'loading' : 'error');

    useEffect(() => {
        if (!src) {
            setStatus('error');
            return;
        }

        setStatus('loading');

        const image = new Image();

        image.onload = (): void => {
            setStatus('success');
        };

        image.onerror = (): void => {
            setStatus('error');
        };

        image.src = src;

        return (): void => {
            image.onload = null;
            image.onerror = null;
        };
    }, [src]);

    if (status !== 'success' || !src) {
        return <span className="text-xs text-white">No image</span>;
    }

    return (
        <img
            src={src}
            alt={alt}
            className="h-full w-full object-contain"
            loading="lazy"
        />
    );
}

export function NewsList({
    newsItems,
    expandedNewsIds,
    isLoading,
    onToggleNews,
    onLastNewsRef,
    formatPublishedAt,
}: NewsListProps) {
    return (
        <div className="mt-6 space-y-4">
            {newsItems.map((newsItem, index) => {
                const isExpanded = expandedNewsIds.includes(newsItem.id);
                const isLastItem = index === newsItems.length - 1;

                return (
                    <div
                        key={newsItem.id}
                        ref={isLastItem ? onLastNewsRef : null}
                        className="rounded border border-gray-200 p-4 dark:border-[#3E3E3A]"
                    >
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
                );
            })}

            {isLoading && (
                <div className="flex justify-center py-4">
                    <div className="h-8 w-8 animate-spin rounded-full border-2 border-gray-300 border-t-[#1b1b18] dark:border-[#3E3E3A] dark:border-t-[#EDEDEC]" />
                </div>
            )}
        </div>
    );
}
