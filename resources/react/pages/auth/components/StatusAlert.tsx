interface StatusAlertProps {
    status: string;
}

export function StatusAlert({ status }: StatusAlertProps) {
    return (<div className="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-600 dark:bg-green-900/20 dark:text-green-400">
                {status}
            </div>);
}
