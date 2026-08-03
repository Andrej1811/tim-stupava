/**
 * Filter the Program / Výsledky listing by oblasť and by permitting stage.
 *
 * Everything is already in the DOM — twenty-nine rows is not worth a round
 * trip — so this only hides and shows. The filter bar ships `hidden` and is
 * revealed here, which is what keeps the page honest without JavaScript: the
 * full list is there and nothing offers to filter it.
 */

const ACTIVE = ["border-brand-600", "bg-brand-600", "text-white"];
const IDLE = ["border-slate-300", "bg-white", "text-ink", "hover:border-slate-400", "hover:bg-sand-100"];

export function initProjectFilter() {
    document.querySelectorAll("[data-project-filter]").forEach(setup);
}

function setup(root) {
    const bar = root.querySelector("[data-filter-bar]");

    if (!bar) {
        return;
    }

    const chips = [...bar.querySelectorAll("[data-filter-group]")];
    const sections = [...root.querySelectorAll("[data-filter-section]")];
    const rows = [...root.querySelectorAll("[data-project]")];
    const status = root.querySelector("[data-filter-status]");
    const empty = root.querySelector("[data-filter-empty]");

    if (chips.length === 0 || rows.length === 0) {
        return;
    }

    const state = { oblast: "", stage: "" };

    bar.hidden = false;

    bar.addEventListener("click", (event) => {
        const chip = event.target.closest("[data-filter-group]");

        if (!chip || !bar.contains(chip)) {
            return;
        }

        const group = chip.dataset.filterGroup;

        // Clicking the active chip clears that group rather than doing nothing,
        // so a filter can always be undone where it was set.
        state[group] = state[group] === chip.dataset.filterValue ? "" : chip.dataset.filterValue;

        apply();
    });

    apply();

    function apply() {
        chips.forEach((chip) => {
            const on = state[chip.dataset.filterGroup] === chip.dataset.filterValue;

            chip.setAttribute("aria-pressed", on ? "true" : "false");
            ACTIVE.forEach((c) => chip.classList.toggle(c, on));
            IDLE.forEach((c) => chip.classList.toggle(c, !on));
        });

        let visible = 0;

        rows.forEach((row) => {
            const match =
                (state.oblast === "" || row.dataset.oblast === state.oblast) &&
                (state.stage === "" || row.dataset.stage === state.stage);

            row.hidden = !match;

            if (match) {
                visible += 1;
            }
        });

        // A heading over nothing reads as a bug, so a section whose rows are all
        // hidden goes with them — and its count follows the filter, not the
        // total, or the number contradicts what is under it.
        sections.forEach((section) => {
            const shown = [...section.querySelectorAll("[data-project]")].filter((row) => !row.hidden);
            const count = section.querySelector("[data-filter-count]");

            section.hidden = shown.length === 0;

            if (count) {
                count.textContent = String(shown.length);
            }
        });

        if (empty) {
            empty.classList.toggle("hidden", visible > 0);
        }

        if (status) {
            const filtered = state.oblast !== "" || state.stage !== "";

            status.textContent = filtered ? `Zobrazujeme ${visible} z ${rows.length} projektov.` : "";
        }
    }
}
