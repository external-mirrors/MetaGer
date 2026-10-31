/**
 * When the result page asks /meta/loadMore again.
 *
 * Engines answer within seconds, so for the first ten polls the page asks once a second, as it
 * always has. After that the only thing worth waiting for is Serper's late links
 * (Search\LateRedirectResolution), which the server reports as `resolving` and which arrive
 * either within seconds or after about half a minute; for those every two seconds is plenty, and
 * halves what the wait costs the server. The server closes that window itself after 45 seconds and
 * answers `finished`; the poll limit is only a backstop.
 */
export const ENGINE_INTERVAL_MS = 1000;
export const ENGINE_POLLS = 10;
export const RESOLVE_INTERVAL_MS = 2000;
export const MAX_POLLS = 30;

/**
 * @param {number} polls how many polls have been answered so far
 * @param {object|null} data the last answer
 * @returns {number|null} milliseconds until the next poll, or null to stop
 */
export function nextPollDelay(polls, data) {
    if (!data || data.finished) {
        return null;
    }
    if (polls < ENGINE_POLLS) {
        return ENGINE_INTERVAL_MS;
    }
    if (data.resolving && polls < MAX_POLLS) {
        return RESOLVE_INTERVAL_MS;
    }
    return null;
}
