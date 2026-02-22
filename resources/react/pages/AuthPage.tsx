import { useCallback, useEffect, useMemo, useState } from 'react';
import { getCsrfToken } from '../utils/csrf';
import { EmailForm, ErrorsAlert, LoginButtons, RegisterForm, PasswordForm, StatusAlert } from './components';

export type AuthView = 'buttons' | 'email' | 'register' | 'password';

export interface AuthRoutes {
    google: string;
    loginSubmit: string;
    registerSubmit: string;
    passwordSubmit: string;
}

export interface FormProps {
    routes: AuthRoutes;
    csrfToken: string,
    old?: OldInput,
    switchView: SwitchView
}

export interface OldInput {
    name?: string;
    email?: string;
}

export type SwitchView = (view: AuthView, pushHistory: boolean) => void;

interface AuthViewPaths {
    buttons: string;
    email: string;
    register: string;
    password: string;
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

function viewCurrentPage(currentView: AuthView, routes: AuthRoutes, csrfToken: string, old: OldInput|undefined, switchView: SwitchView ) {
    switch (currentView) {
        case 'buttons':
            return <LoginButtons routes={routes} switchView={switchView} />;
        case 'email':
            return <EmailForm routes={routes} csrfToken={csrfToken} old={old} switchView={switchView} />;
        case 'register':
            return <RegisterForm routes={routes} csrfToken={csrfToken} old={old} switchView={switchView} />;
        case 'password':
            return <PasswordForm routes={routes} csrfToken={csrfToken} old={old} switchView={switchView} />;
        default:
            return null;
    }
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
    const [currentView, setCurrentView] = useState<AuthView>(initialView);
    const [currentErrors, setCurrentErrors] = useState<string[]>(errors);
    const [currentStatus, setСurrentStatus] = useState<string | undefined>(status);

    const csrfToken = useMemo(() => getCsrfToken(), []);
    const pathToView = useMemo(() => {
        return {
            [viewPaths.buttons]: 'buttons',
            [viewPaths.email]: 'email',
            [viewPaths.register]: 'register',
            [viewPaths.password]: 'password',
        } as Record<string, AuthView>;
    }, [viewPaths]);

    const switchView: SwitchView = useCallback((view: AuthView, pushHistory: boolean): void => {
        setCurrentView(view);
        setCurrentErrors([]);
        setСurrentStatus(undefined);

        if (pushHistory && window.location.pathname !== viewPaths[view]) {
            window.history.pushState(null, '', viewPaths[view]);
        }
    }, [viewPaths]);

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
        <div className="flex min-h-screen justify-center">
            <div className="p-8">
                <h1 className="mb-8 text-center text-3xl font-semibold">{appName}</h1>

                {currentStatus ? <StatusAlert status={currentStatus} /> : null}
                {currentErrors.length ? <ErrorsAlert errors={currentErrors} /> : null}
                {viewCurrentPage(currentView, routes, csrfToken, old, switchView)}

            </div>
        </div>
    );
}