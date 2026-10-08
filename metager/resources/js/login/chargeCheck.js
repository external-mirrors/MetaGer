/**
 * How long the sign-in waits for a balance before submitting without one.
 *
 * The form holds its submit until this answers, so without a limit a keyserver
 * that accepts the connection and then says nothing leaves the visitor pressing
 * "Anmelden" with nothing happening. Three seconds is long for an answer this
 * small and short enough not to look broken.
 */
export const CHARGE_CHECK_TIMEOUT_MS = 3000;

/**
 * What a key is worth, or `null` when that cannot be found out in time.
 *
 * `null` is never an error to the caller: the answer only decides whether to
 * ask "this key is empty, sure?", and a balance check is not worth standing
 * between a visitor and their account.
 *
 * An AbortController with its own timer rather than `AbortSignal.timeout()`:
 * the latter runs on a clock fake timers cannot advance, which would leave the
 * one case that matters most here untestable.
 *
 * @param {string} endpoint the key API, ending in a slash
 * @param {string} key
 * @returns {Promise<number|null>}
 */
export async function fetchCharge(endpoint, key) {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), CHARGE_CHECK_TIMEOUT_MS);

    try {
        const response = await fetch(endpoint + encodeURIComponent(key), {
            headers: { Accept: "application/json" },
            signal: controller.signal,
        });
        if (!response.ok) {
            return null;
        }
        const charge = (await response.json()).charge;
        return typeof charge === "number" ? charge : null;
    } catch {
        return null;
    } finally {
        clearTimeout(timer);
    }
}
