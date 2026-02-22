interface NewsHeaderProps {
    appName: string;
    userName: string;
    logoutRoute: string;
    csrfToken: string;
}

export function NewsHeader({
    appName,
    userName,
    logoutRoute,
    csrfToken,
}: NewsHeaderProps) {
    return (
        <header className="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-[#3E3E3A]">
            <h1 className="text-xl font-semibold">{appName}</h1>
            <div className="flex items-center gap-4">
                <span className="text-sm text-gray-600 dark:text-[#A1A09A]">{userName}</span>
                <form method="POST" action={logoutRoute}>
                    <input type="hidden" name="_token" value={csrfToken} />
                    <button
                        type="submit"
                        className="cursor-pointer text-sm text-gray-500 underline underline-offset-4 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        Logout
                    </button>
                </form>
            </div>
        </header>
    );
}
