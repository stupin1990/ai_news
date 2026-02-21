import { FormProps } from "../AuthPage";

export function RegisterForm({routes, csrfToken, old, switchView}: FormProps) {

    return (<form method="POST" action={routes.registerSubmit} className="flex w-fit flex-col gap-4">
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
                <div className="mt-2 flex items-center gap-3">
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
                    &larr; Back
                </button>
                </div>
            </form>);
}