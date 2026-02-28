interface ErrorsAlertProps {
    errors: string[];
}

export function ErrorsAlert({ errors }: ErrorsAlertProps) {
    return (<div className="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400">
                {errors.map((error) => (
                    <p key={error}>{error}</p>
                ))}
            </div>);
}
