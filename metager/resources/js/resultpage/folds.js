/**
 * Replace the result list with `html`, reopening every host fold that was open.
 *
 * The folds are native <details> (Search\HostCrowding), and replacing the list
 * through innerHTML renders them closed again. Matched by host rather than by
 * position: a late engine can move a host's fold up or down the list.
 */
export function replaceKeepingFoldsOpen(container, html) {
    const open = new Set(
        [...container.querySelectorAll("details.result-more-from-host[open]")].map((fold) => fold.dataset.host)
    );

    container.innerHTML = html;

    for (const fold of container.querySelectorAll("details.result-more-from-host")) {
        if (open.has(fold.dataset.host)) {
            fold.open = true;
        }
    }
}
