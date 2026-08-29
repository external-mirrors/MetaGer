import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

/**
 * Asset build for MetaGer.
 *
 * Replaces webpack.mix.js. Two things about this setup are deliberate and worth
 * knowing before changing them:
 *
 * 1. There is no dev server. `npm run watch` runs `vite build --watch`, which
 *    writes real files to public/build the same way laravel-mix did. Vite's dev
 *    server would serve stylesheets through a JavaScript module that injects
 *    them at runtime, and MetaGer has to render correctly with client JS
 *    disabled — a development mode where that stops being true would hide
 *    exactly the regressions tests/Browser/ProgressiveEnhancementTest exists to
 *    catch. Because no dev server runs, public/hot is never written, so
 *    Vite::asset() always resolves through the manifest.
 *
 * 2. Entry points are referenced from PHP as Vite::asset('resources/…'), i.e.
 *    by *source* path, not by output path. The pages pass those URLs into
 *    layouts as $css / $js / $darkcss arrays, which the @vite directive cannot
 *    express. The main stylesheet carries both palettes and needs no $darkcss;
 *    the spende, membership and count pages are still light/dark pairs attached
 *    with media="(prefers-color-scheme:dark)" on the link tag, which has to keep
 *    working without JS.
 *
 * Anything added to `input` below becomes reachable from PHP under its source
 * path; anything removed stops being built. Tests/Feature/AssetPipelineTest
 * keeps the two sides in sync.
 */

/**
 * vitest transforms the test files through an in-process Vite server, and
 * laravel-vite-plugin aborts whenever a server starts with CI set:
 * "You should not run the Vite HMR server in CI environments." It is right to —
 * that check exists to stop a deploy serving assets off a dev server — but the
 * unit tests are not a dev server, so the plugin is simply left out of that run.
 * The tests import plain modules from resources/js and need nothing it provides.
 *
 * package.json runs vitest with CI=1 for this reason: without it the failure
 * shows up only in the pipeline, which is exactly what happened once already.
 */
const underTest = Boolean(process.env.VITEST);

/**
 * Source maps everywhere except on the production deployment.
 *
 * The polarity is deliberate: production has to *opt out*, and it does so with
 * the same variable that already marks it as production everywhere else.
 * .gitlab-ci.yml sets APP_ENV=production only on master; review environments and
 * metager3.de run as development, so they get maps without anyone remembering to
 * ask. A bare `npm run build` on a laptop is not a deployment and gets them too.
 *
 * Only `build.sourcemap` is keyed off this — not Vite's mode. A review
 * deployment exists to behave like production, and switching mode would also
 * flip NODE_ENV, which changes what bundled dependencies compile to.
 */
const isProductionDeployment = process.env.APP_ENV === "production";

const assets = laravel({
    input: [
        // -- stylesheets --------------------------------------------
        "resources/css/noheader.css",
        "resources/less/utility.less",
        "resources/less/metager/metager.less",
        "resources/less/metager/pages/startpage/startpage.less",
        "resources/less/metager/pages/adblocker.less",
        "resources/less/metager/pages/contact.less",
        "resources/less/metager/pages/lang-selector.less",
        "resources/less/metager/pages/plugin-page.less",
        "resources/less/metager/pages/privacy.less",
        "resources/less/metager/pages/price.less",
        "resources/less/metager/pages/agb.less",
        "resources/less/metager/pages/logs.less",
        "resources/less/metager/pages/help-easy-language.less",
        "resources/less/metager/pages/prevention-information.less",
        "resources/less/metager/pages/count/style.less",
        "resources/less/metager/pages/count/style-dark.less",
        "resources/less/metager/pages/spende/base.less",
        "resources/less/metager/pages/spende/base-dark.less",
        "resources/less/metager/pages/membership/base.less",
        "resources/less/metager/pages/membership/base-dark.less",
        "resources/less/metager/pages/widget/widget.less",
        "resources/less/metager/pages/widget/widget-template.less",
        "resources/less/metager/pages/admin/logs.less",
        "resources/less/metager/pages/admin/membership.less",

        // -- scripts ------------------------------------------------
        "resources/js/utility.js",
        "resources/js/scriptResultPage.js",
        "resources/js/scriptSettings.js",
        "resources/js/imagesearch.js",
        "resources/js/startpage/app.js",
        "resources/js/lang.js",
        "resources/js/contact.js",
        "resources/js/widgets.js",
        "resources/js/membership.js",
        "resources/js/donation/base.js",
        "resources/js/admin/count.js",
        "resources/js/admin/membership.js",
    ],
    // Blade changes do not need a browser reload without a dev server.
    refresh: false,
});

export default defineConfig({
    plugins: underTest ? [] : [assets],
    test: {
        // Only the modules with tests next to them; everything else under
        // resources/js is browser glue that a DOM harness cannot say much about.
        // vite.config.test.js covers this file's own build decisions.
        include: ["resources/js/**/*.test.js", "vite.config.test.js"],
        environment: "jsdom",
    },
    build: {
        // Vite's default. Named explicitly because the build it replaces
        // targeted "firefox 50, IE 11" and shipped core-js into every bundle
        // to get there; the floor is now what the CSS already assumed.
        target: "baseline-widely-available",
        sourcemap: !isProductionDeployment,
    },
});
