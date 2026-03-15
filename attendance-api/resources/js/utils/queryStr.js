export function getUrlParams(param, value) {
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.set(param, value);

    return currentParams.toString() ? `?${currentParams.toString()}` : '';
}

export function updateQueryString(param, value) {
    const currentUrl = new URL(window.location);
    const params = new URLSearchParams(currentUrl.search);

    params.set(param, value);
    currentUrl.search = params.toString();
    window.history.pushState({}, '', currentUrl);
}