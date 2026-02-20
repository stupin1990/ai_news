export function getCsrfToken(): string {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    if (!csrfToken) {
        throw new Error('CSRF token meta tag is missing.');
    }

    return csrfToken;
}