import { getCsrfToken } from '../utils/csrf';

interface VerifyEmailPageProps {
    appName: string;
    message?: string;
    routes: {
        resend: string;
        logout: string;
    };
}

export function VerifyEmailPage({
    appName,
    message,
    routes,
}: VerifyEmailPageProps) {
    const csrfToken = getCsrfToken();

    return (
        <div className="flex min-h-screen items-center justify-center">
            <div className="w-full max-w-sm p-8 text-center">
                <h1 className="mb-4 text-3xl font-semibold">{appName}</h1>
                <p className="mb-6 text-sm text-gray-600 dark:text-[#A1A09A]">
                    Please verify your email address by clicking the link we sent to your inbox.
                </p>

                {message ? (
                    <div className="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-600 dark:bg-green-900/20 dark:text-green-400">
                        {message}
                    </div>
                ) : null}

                <form method="POST" action={routes.resend}>
                    <input type="hidden" name="_token" value={csrfToken} />
                    <button
                        type="submit"
                        className="w-full cursor-pointer rounded-md bg-[#1b1b18] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                    >
                        Resend Verification Email
                    </button>
                </form>

                <form method="POST" action={routes.logout} className="mt-4">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <button
                        type="submit"
                        className="cursor-pointer text-sm text-gray-500 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        Logout
                    </button>
                </form>
            </div>
        </div>
    );
}