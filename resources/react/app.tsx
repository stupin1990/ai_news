import '../js/bootstrap';
import '../css/app.css';
import { StrictMode, type ComponentType } from 'react';
import { createRoot } from 'react-dom/client';
import { AuthPage, NewsPage, ResetPasswordPage, VerifyEmailPage } from './pages';
import type { InitialPagePayload, PageName } from './types';

const initialPage: InitialPagePayload = window.__INITIAL_PAGE__ ?? {
    page: 'NewsPage',
    props: {},
};

const pages: Record<PageName, ComponentType<any>> = {
    AuthPage,
    ResetPasswordPage,
    VerifyEmailPage,
    NewsPage,
};

const rootElement = document.getElementById('app');

if (rootElement === null) {
    throw new Error('App root element not found.');
}

const PageComponent = pages[initialPage.page] ?? null;
const root = createRoot(rootElement);

if (PageComponent === null) {
    root.render(
        <div className="flex min-h-screen items-center justify-center p-6 text-center text-sm">
            Unknown page: {initialPage.page}
        </div>
    );
} else {
    root.render(
        <StrictMode>
            <PageComponent {...initialPage.props} />
        </StrictMode>
    );
}