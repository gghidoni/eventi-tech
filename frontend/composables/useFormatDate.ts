export function formatDate(datetimeStr: string): string {
    const date = new Date(datetimeStr);
    return date.toLocaleString('it-IT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}