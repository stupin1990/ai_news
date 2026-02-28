export interface VerifyEmailProps {
    csrfToken: string;
    resend: string;
}

export function VerifyEmailForm({csrfToken, resend}: VerifyEmailProps) {

    return (<>
            <form method="POST" action={resend}>
                <input type="hidden" name="_token" value={csrfToken} />
                <button
                    type="submit"
                    className="w-full cursor-pointer rounded-md bg-[#1b1b18] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                >
                    Resend Verification Email
                </button>
            </form>
        </>);
}