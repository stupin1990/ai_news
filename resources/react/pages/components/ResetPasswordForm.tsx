export interface ResetPasswordProps {
    token: string;
    email: string,
    csrfToken: string;
    routes: {
        update: string;
        login: string;
    }
}

export function ResetPasswordForm({token, email, csrfToken, routes}: ResetPasswordProps) {

    return (<form method="POST" action={routes.update} className="flex w-fit flex-col gap-4">
                <input type="hidden" name="_token" value={csrfToken} />
                <input type="hidden" name="token" value={token} />
                <input type="hidden" name="email" value={email} />

                <div>
                    <label htmlFor="password" className="mb-1 block text-sm font-medium">New Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        className="w-75 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]"
                    />
                    <p className="mt-1 text-xs text-gray-500 dark:text-[#A1A09A]">Minimum 8 characters</p>
                </div>

                <div>
                    <label htmlFor="password_confirmation" className="mb-1 block text-sm font-medium">Confirm Password</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        className="w-75 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-[#3E3E3A] dark:bg-[#161615]"
                    />
                </div>

                <div className="mt-2 flex items-center gap-3">
                    <button
                        type="submit"
                        className="cursor-pointer rounded-md bg-[#1b1b18] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                    >
                        Reset Password
                    </button>
                    <a
                        href={routes.login}
                        className="text-sm text-gray-500 hover:text-gray-700 dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        &larr; Back to login
                    </a>
                </div>
            </form>);
}