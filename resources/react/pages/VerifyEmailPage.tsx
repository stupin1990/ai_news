import { getCsrfToken } from '../utils/csrf';
import { StatusAlert, VerifyEmailForm } from './components';

interface VerifyEmailPageProps {
    appName: string;
    message?: string;
    resend: string;
}

export function VerifyEmailPage({
    appName,
    message,
    resend,
}: VerifyEmailPageProps) {
    const csrfToken = getCsrfToken();

    return (
        <div className="flex min-h-screen justify-center">
            <div className="w-full max-w-sm p-8 text-center">
                <h1 className="mb-4 text-3xl font-semibold">{appName}</h1>
                <p className="mb-6 text-sm text-gray-600 dark:text-[#A1A09A]">
                    Please verify your email address by clicking the link we sent to your inbox.
                </p>

                {message ? <StatusAlert status={message} /> : null}

                <VerifyEmailForm csrfToken={csrfToken} resend={resend} />
                
            </div>
        </div>
    );
}