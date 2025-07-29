export function getPoster(url: string | null): string {
    return url ? `${url}` : `/images/no-poster.png`
}
