/**
 * /entfernen, with two things only a script can add.
 *
 * Both are hints on a page that is complete without them: the request kinds
 * are <details>, the table of contents is anchors, and every provider row is a
 * plain link that looks the same as its neighbours until this runs.
 */

/**
 * Opening a request kind in step 2 marks the row for it on every provider card
 * in step 3, so nobody has to read three labels on seven cards. The kind opened
 * last wins; "Strafbare Inhalte" carries no data-removal-kind because no card
 * has a row for it, and marks nothing.
 */
export function enhanceRequestKinds(root) {
  const kinds = [...root.querySelectorAll(".removal-kind")];
  const rows = [...root.querySelectorAll(".removal-provider-links [data-removal-kind]")];
  let marked = null;

  const mark = (kind) => {
    marked = kind;
    const name = kind?.dataset.removalKind;
    rows.forEach((row) => {
      row.classList.toggle("removal-row-active", name !== undefined && row.dataset.removalKind === name);
    });
  };

  kinds.forEach((kind) => {
    kind.addEventListener("toggle", () => {
      if (kind.open) {
        mark(kind);
      } else if (kind === marked) {
        mark(null);
      }
    });
  });

  // A reply from suma-crm can link straight to one kind, which the browser
  // opens alongside the one the page opens by default.
  mark(kinds.find((kind) => kind.matches(":target")) ?? kinds.find((kind) => kind.open) ?? null);
}

/** The index of the last step that has reached the reading line. */
export function currentStep(tops, line) {
  return tops.reduce((current, top, index) => (top <= line ? index : current), 0);
}

/**
 * Marks the step being read in the table of contents, which stays in view
 * beside the steps on a wide screen. aria-current is both the state and the
 * style hook, so sight and a screen reader get the same answer.
 */
export function enhanceContents(root, view = window) {
  const links = [...root.querySelectorAll(".removal-toc a")];
  const steps = links.map((link) => root.querySelector(`[id="${link.hash.slice(1)}"]`));
  if (links.length === 0 || steps.includes(null)) {
    return;
  }

  const update = () => {
    const tops = steps.map((step) => step.getBoundingClientRect().top);
    const current = currentStep(tops, view.innerHeight / 4);
    links.forEach((link, index) => {
      if (index === current) {
        link.setAttribute("aria-current", "location");
      } else {
        link.removeAttribute("aria-current");
      }
    });
  };

  let scheduled = false;
  const schedule = () => {
    if (scheduled) {
      return;
    }
    scheduled = true;
    view.requestAnimationFrame(() => {
      scheduled = false;
      update();
    });
  };

  view.addEventListener("scroll", schedule, { passive: true });
  view.addEventListener("resize", schedule, { passive: true });
  update();
}
