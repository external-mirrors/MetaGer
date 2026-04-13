export default function updateProxyLinks() {
    // Get current lang identifier from the pathname (e.g. /en/, /de/, etc.)
    const pathMatch = window.location.pathname.match(/^\/(\w{2}-\w{2})(?:\/|$)/);
    const langIdentifier = pathMatch ? pathMatch[1] : null;

    // Check for novnc support: WebSockets and localStorage
    const supportsWebsockets = (function () {
        try {
            return (
                typeof window.WebSocket !== 'undefined' &&
                typeof window.localStorage !== 'undefined'
            );
        } catch (e) {
            return false;
        }
    })();

    if (!supportsWebsockets) {
        // Do not modify link actions if novnc is not supported
        return;
    }

    document.querySelectorAll('a.result-open-proxy').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            try {
                const href = link.getAttribute('href');
                const urlObj = new URL(href, window.location.origin);
                const targetUrl = urlObj.searchParams.get('url');
                if (!targetUrl) return;
                let newPath = '/proxy/#' + targetUrl;
                if (langIdentifier) {
                    newPath = '/' + langIdentifier + newPath;
                }
                window.open(newPath, 'metagerproxy');
            } catch (err) {
                // Fallback: do nothing or log error
                console.error('Proxy link error:', err);
            }
        });
    });
}