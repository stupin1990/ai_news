export type PageName = 'AuthPage' | 'ResetPasswordPage' | 'VerifyEmailPage' | 'NewsPage';

export interface InitialPagePayload {
    page: PageName;
    props: Record<string, unknown>;
}

declare global {
    interface Window {
        __INITIAL_PAGE__?: InitialPagePayload;
    }
}

export {};