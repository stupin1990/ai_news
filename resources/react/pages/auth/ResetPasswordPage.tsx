import { getCsrfToken } from '../../utils/csrf';
import { ErrorsAlert, ResetPasswordForm } from './components';

interface ResetPasswordPageProps {
    appName: string;
    token: string;
    email: string;
    errors?: string[];
    routes: {
        update: string;
        login: string;
    };
}

export function ResetPasswordPage({
    appName,
    token,
    email,
    errors = [],
    routes,
}: ResetPasswordPageProps) {
    const csrfToken = getCsrfToken();

    return (
        <div className="flex min-h-screen justify-center">
            <div className="p-8">
                <h1 className="mb-8 text-center text-3xl font-semibold">{appName}</h1>

                {errors.length ? <ErrorsAlert errors={errors} /> : null}

                <ResetPasswordForm token={token} email={email} csrfToken={csrfToken} routes={routes} />;
            </div>
        </div>
    );
}