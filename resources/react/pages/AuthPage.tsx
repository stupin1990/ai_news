import { useEffect, useMemo, useState } from 'react';
import { getCsrfToken } from '../utils/csrf';

type AuthView = 'buttons' | 'email' | 'register' | 'password';

interface AuthRoutes {
    google: string;
    loginSubmit: string;
    registerSubmit: string;
    passwordSubmit: string;
}

interface AuthViewPaths {
    buttons: string;
    email: string;
    register: string;
    password: string;
}

interface OldInput {
    name?: string;
    email?: string;
}

interface AuthPageProps {
    appName: string;
    initialView: AuthView;
    status?: string;
    errors?: string[];
    old?: OldInput;
    routes: AuthRoutes;
    viewPaths: AuthViewPaths;
}

export function AuthPage({
    appName,
    initialView,
    status,
    errors = [],
    old,
    routes,
    viewPaths,
}: AuthPageProps) {
    const csrfToken = getCsrfToken();
    const [currentView, setCurrentView] = useState<AuthView>(initialView);
    const [registerPasswordError, setRegisterPasswordError] = useState<string | null>(null);

    const pathToView = useMemo(() => {
        return {
            [viewPaths.buttons]: 'buttons',
            [viewPaths.email]: 'email',
            [viewPaths.register]: 'register',
            [viewPaths.password]: 'password',
        } as Record<string, AuthView>;
    }, [viewPaths]);

    const switchView = (view: AuthView, pushHistory: boolean): void => {
        setCurrentView(view);
        setRegisterPasswordError(null);

        if (pushHistory && window.location.pathname !== viewPaths[view]) {
            window.history.pushState(null, '', viewPaths[view]);
        }
    };

    useEffect(() => {
        const onPopState = (): void => {
            const viewFromPath = pathToView[window.location.pathname] ?? 'buttons';
            setCurrentView(viewFromPath);
        };

        window.addEventListener('popstate', onPopState);

        return (): void => {
            window.removeEventListener('popstate', onPopState);
        };
    }, [pathToView]);

    return (
        <div className="flex min-h-screen items-center justify-center">
            <div className="p-8">
                <h1 className="mb-8 text-center text-3xl font-semibold">{appName}</h1>

                {status ? (
                    <div className="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-600 dark:bg-green-900/20 dark:text-green-400">
                        {status}
                    </div>
                ) : null}

                {errors.length > 0 ? (
                    <div className="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                        {errors.map((error) => (
                            <p key={error}>{error}</p>
                        ))}
                    </div>
                ) : null}

                {registerPasswordError ? (
                    <div className="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                        <p>{registerPasswordError}</p>
                    </div>
                ) : null}

                {currentView === 'buttons' ? (
                    <div className="flex w-fit flex-col gap-3">
                        <a
                            href={routes.google}
                            className="flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1D1D1A]"
                        >
                            <svg className="h-5 w-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4" /><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" /><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18A10.96 10.96 0 0 0 1 12c0 1.77.42 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" /><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" /></svg>
                            Sign in with Google
                        </a>
                        <button
                            type="button"
                            onClick={(): void => switchView('email', true)}
                            className="cursor-pointer rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1D1D1A]"
                        >
                            Sign in with Email
                        </button>
                        <button
                            type="button"
                            onClick={(): void => switchView('register', true)}
                            className="cursor-pointer rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1D1D1A]"
                        >
                            Create Account
                        </button>
                        <button
                            type="button"
                            onClick={(): void => switchView('password', true)}
                            className="cursor-pointer text-center text-sm text-gray-500 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        >
                            Forgot your password ?
                        </button>
                    </div>
                ) : null}

                {currentView === 'email' ? (
                    <form method="POST" action={routes.loginSubmit} className="flex w-fit flex-col gap-4">
                        <input type="hidden" name="_token" value={csrfToken} />
                        <div>
                            <label htmlFor="login-email" className="mb-1 block text-sm font-medium">Email</label>
                            <input
                                id="login-email"
                                name="email"
                                type="email"
                                defaultValue={old?.email ?? ''}
                                autoComplete="new-password"
                                required
                                className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]"
                            />
                        </div>
                        <div>
                            <label htmlFor="login-password" className="mb-1 block text-sm font-medium">Password</label>
                            <input
                                id="login-password"
                                name="password"
                                type="password"
                                autoComplete="new-password"
                                required
                                className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]"
                            />
                        </div>
                        <div className="mt-2 flex items-center gap-3">
                            <button
                                type="submit"
                                className="cursor-pointer rounded-md bg-[#1b1b18] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                            >
                                Sign In
                            </button>
                            <button
                                type="button"
                                onClick={(): void => switchView('buttons', true)}
                                className="cursor-pointer text-sm text-gray-500 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                            >
                                &larr; Back
                            </button>
                        </div>
                    </form>
                ) : null}

                {currentView === 'register' ? (
                    <form method="POST" action={routes.registerSubmit} className="flex w-fit flex-col gap-4">
                        <input type="hidden" name="_token" value={csrfToken} />
                        <div>
                            <label htmlFor="reg-name" className="mb-1 block text-sm font-medium">Name</label>
                            <input
                                id="reg-name"
                                name="name"
                                type="text"
                                defaultValue={old?.name ?? ''}
                                required
                                className="w-75 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]"
                            />
                        </div>
                        <div>
                            <label htmlFor="reg-email" className="mb-1 block text-sm font-medium">Email</label>
                            <input
                                id="reg-email"
                                name="email"
                                type="email"
                                defaultValue={old?.email ?? ''}
                                autoComplete="new-password"
                                required
                                className="w-75 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]"
                            />
                        </div>
                        <div>
                            <label htmlFor="reg-password" className="mb-1 block text-sm font-medium">Password</label>
                            <input
                                id="reg-password"
                                name="password"
                                type="password"
                                autoComplete="new-password"
                                required
                                className="w-75 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]"
                            />
                            <p className="mt-1 text-xs text-gray-500 dark:text-[#A1A09A]">Minimum 8 characters</p>
                        </div>
                        <div>
                            <label htmlFor="reg-password-confirmation" className="mb-1 block text-sm font-medium">Repeat Password</label>
                            <input
                                id="reg-password-confirmation"
                                name="password_confirmation"
                                type="password"
                                autoComplete="new-password"
                                required
                                className="w-75 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]"
                            />
                        </div>
                        <button
                            type="submit"
                            className="cursor-pointer self-center rounded-md bg-[#1b1b18] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                        >
                            Create Account
                        </button>
                        <button
                            type="button"
                            onClick={(): void => switchView('buttons', true)}
                            className="cursor-pointer text-center text-sm text-gray-500 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        >
                            &larr; Already have an account? Sign in
                        </button>
                    </form>
                ) : null}

                {currentView === 'password' ? (
                    <form method="POST" action={routes.passwordSubmit} className="flex w-fit flex-col gap-4">
                        <input type="hidden" name="_token" value={csrfToken} />
                        <div>
                            <label htmlFor="reg-email-pass" className="mb-1 block text-sm font-medium">Email</label>
                            <input
                                id="reg-email-pass"
                                name="email"
                                type="email"
                                defaultValue={old?.email ?? ''}
                                autoComplete="new-password"
                                required
                                className="w-75 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]"
                            />
                        </div>
                        <div className="mt-2 flex items-center gap-3">
                            <button
                                type="submit"
                                className="cursor-pointer rounded-md bg-[#1b1b18] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                            >
                                Send reset link
                            </button>
                            <button
                                type="button"
                                onClick={(): void => switchView('buttons', true)}
                                className="cursor-pointer text-sm text-gray-500 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                            >
                                &larr; Back
                            </button>
                        </div>
                    </form>
                ) : null}
            </div>
        </div>
    );
}