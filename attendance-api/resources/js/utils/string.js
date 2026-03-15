export function ucFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

export function strSlug(text) {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-");
}