import { FormProps } from "../AuthPage";

export function PasswordForm({routes, csrfToken, old, switchView}: FormProps) {

    return (<form method="POST" action={routes.passwordSubmit} className="flex w-fit flex-col gap-4">
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
            </form>);
}