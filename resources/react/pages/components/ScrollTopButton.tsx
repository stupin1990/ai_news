interface ScrollTopButtonProps {
    isVisible: boolean;
    onClick: () => void;
}

export function ScrollTopButton({
    isVisible,
    onClick,
}: ScrollTopButtonProps) {
    if (!isVisible) {
        return null;
    }

    return (
        <button
            type="button"
            className="fixed right-6 bottom-6 h-12 w-12 cursor-pointer rounded border border-gray-300 bg-[#FDFDFC] text-xl shadow-sm hover:border-gray-500 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
            onClick={onClick}
            aria-label="Scroll to top"
        >
            ↑
        </button>
    );
}
