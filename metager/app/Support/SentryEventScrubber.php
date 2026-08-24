<?php

namespace App\Support;

use Sentry\Event;

/**
 * Sentry's RequestIntegration attaches the full request URL and query string to
 * every event unconditionally -- `send_default_pii` only gates IP, cookies and
 * headers, not the URL. MetaGer's search terms travel in the query string (the
 * `eingabe` parameter, see App\Search\QueryParser), so an unscrubbed event would
 * carry a user's search query straight to GlitchTip. The privacy policy
 * (lang/*\/privacy.php) promises GlitchTip never receives "the content of any
 * forms or messages you submit through our services"; this is what keeps that
 * true. Wired in as `before_send` / `before_send_transaction` in config/sentry.php.
 */
class SentryEventScrubber
{
    public static function scrub(Event $event): Event
    {
        $request = $event->getRequest();

        if (isset($request['url']) && is_string($request['url'])) {
            $request['url'] = strtok($request['url'], '?');
        }

        unset($request['query_string'], $request['data']);

        $event->setRequest($request);

        return $event;
    }
}
