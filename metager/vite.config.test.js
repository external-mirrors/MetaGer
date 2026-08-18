import { afterEach, describe, expect, it, vi } from "vitest";

/**
 * The build config's own decisions.
 *
 * These are the settings nobody looks at again once they work, and both of the
 * failure modes are silent: a production deployment that publishes source maps
 * gives away more than it should and pays for the bytes, while a review
 * deployment without them is simply harder to debug than it needed to be, and
 * neither shows up as a broken page.
 *
 * vite.config.js reads process.env at module scope, so each case has to import
 * it fresh.
 */
async function loadConfig(appEnv) {
    vi.resetModules();

    if (appEnv === undefined) {
        vi.stubEnv("APP_ENV", undefined);
    } else {
        vi.stubEnv("APP_ENV", appEnv);
    }

    return (await import("./vite.config.js")).default;
}

afterEach(() => {
    vi.unstubAllEnvs();
});

describe("source maps", () => {
    it("are left out of the production deployment", async () => {
        const config = await loadConfig("production");

        expect(config.build.sourcemap).toBe(false);
    });

    it("ship on the review and development deployments", async () => {
        // .gitlab-ci.yml gives review environments and metager3.de this value.
        const config = await loadConfig("development");

        expect(config.build.sourcemap).toBe(true);
    });

    it("ship when nothing says which environment this is", async () => {
        // A bare `npm run build`, e.g. on a laptop or in the node container.
        // Production is the case that has to announce itself.
        const config = await loadConfig(undefined);

        expect(config.build.sourcemap).toBe(true);
    });
});

describe("build target", () => {
    it("does not fall back to a legacy browser baseline", async () => {
        // The laravel-mix build this replaced compiled for "firefox 50, IE 11"
        // and shipped core-js into every bundle to reach it.
        const config = await loadConfig("production");

        expect(config.build.target).toBe("baseline-widely-available");
    });
});
