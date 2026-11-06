/**
 * Goes to a URL returned by the API. A redirect to the current page that only changes the #hash would
 * not reload, so it reloads explicitly (e.g. after posting a review the page must show it).
 */
export function navigate(target) {
    const next = new URL(target, window.location.href);
    const isSamePage = next.pathname === window.location.pathname && next.search === window.location.search;
    if (!isSamePage) {
        window.location.assign(next.href);
        return;
    }
    window.history.replaceState(null, '', next.href);
    window.location.reload();
}
