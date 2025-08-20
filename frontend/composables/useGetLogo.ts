export function getLogo(url: string | null, name: string): string {
    if (url && url.trim() !== '') {
        return url
    }

    const encodedName = encodeURIComponent(name)
    return `https://ui-avatars.com/api/?name=${encodedName}&background=random&size=128&rounded=true`
}