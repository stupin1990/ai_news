interface NewArticlesBadgeProps {
    isVisible: boolean;
    onClick: () => void;
}

export function NewArticlesBadge({
    isVisible,
    onClick,
}: NewArticlesBadgeProps) {
    if (!isVisible) {
        return null;
    }

    return (
        <button
            type="button"
            className="fixed top-1/2 right-6 -translate-y-1/2 cursor-pointer rounded-full bg-green-600 px-3 py-1 text-sm font-medium text-white shadow-sm hover:bg-green-700"
            onClick={onClick}
        >
            new articles
        </button>
    );
}