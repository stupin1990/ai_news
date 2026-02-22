import { Category } from "../NewsPage";


interface CategoryFilterProps {
    categories: Category[];
    activeCategoryIds: number[];
    isFilterOpen: boolean;
    onToggleFilter: () => void;
    onToggleCategory: (categoryId: number) => void;
    onResetCategory: () => void;
}

export function CategoryFilter({
    categories,
    activeCategoryIds,
    isFilterOpen,
    onToggleFilter,
    onToggleCategory,
    onResetCategory
}: CategoryFilterProps) {
    return (
        <>
            <button
                type="button"
                className="cursor-pointer text-sm font-medium text-gray-600 underline underline-offset-4 hover:text-gray-800 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                onClick={onToggleFilter}
            >
                Select Categories
            </button>

            <div
                className={`grid transition-all duration-300 ease-in-out ${isFilterOpen ? 'mt-4 grid-rows-[1fr] opacity-100' : 'mt-0 grid-rows-[0fr] opacity-0'}`}
            >
                <div className="overflow-hidden">
                    <div className="flex flex-wrap gap-2 border-b border-gray-200 pb-4 dark:border-[#3E3E3A]">
                        {categories.map((category) => {
                            const isActive = activeCategoryIds.includes(category.id);

                            return (
                                <button
                                    key={category.id}
                                    type="button"
                                    className={`cursor-pointer rounded border px-3 py-1 text-sm transition ${isActive
                                        ? 'border-[#1b1b18] bg-[#1b1b18] text-[#FDFDFC] dark:border-[#EDEDEC] dark:bg-[#EDEDEC] dark:text-[#0a0a0a]'
                                        : 'border-gray-300 text-gray-700 hover:border-gray-500 dark:border-[#3E3E3A] dark:text-[#A1A09A] dark:hover:border-[#A1A09A]'
                                    }`}
                                    onClick={() => onToggleCategory(category.id)}
                                >
                                    {category.name}
                                </button>
                            );
                        })}
                        {activeCategoryIds.length 
                        ? <button
                                type="button"
                                className="cursor-pointer text-sm font-light text-gray-600 hover:text-gray-800 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                                onClick={onResetCategory}
                            >
                                Reset
                            </button>
                        : null}
                    </div>
                </div>
            </div>
        </>
    );
}
