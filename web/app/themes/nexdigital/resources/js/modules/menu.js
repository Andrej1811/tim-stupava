/**
 * Mobile navigation panel.
 *
 * The panel is plain markup that is visible without JS — the `hidden` class is
 * only ever applied here, so a failed script leaves the menu open and usable
 * rather than unreachable.
 */

const OPEN_LABEL = "Otvoriť menu";
const CLOSE_LABEL = "Zavrieť menu";

export function initMenu() {
    const toggle = document.querySelector("[data-menu-toggle]");
    const panel = document.querySelector("[data-menu-panel]");

    if (!toggle || !panel) {
        return;
    }

    const iconOpen = toggle.querySelector("[data-menu-icon-open]");
    const iconClose = toggle.querySelector("[data-menu-icon-close]");
    const label = toggle.querySelector(".sr-only");
    const desktop = window.matchMedia("(min-width: 64rem)");

    const setState = (open) => {
        panel.classList.toggle("hidden", !open);
        toggle.setAttribute("aria-expanded", String(open));
        iconOpen?.classList.toggle("hidden", open);
        iconClose?.classList.toggle("hidden", !open);

        if (label) {
            label.textContent = open ? CLOSE_LABEL : OPEN_LABEL;
        }
    };

    const isOpen = () => toggle.getAttribute("aria-expanded") === "true";

    setState(false);

    toggle.addEventListener("click", () => setState(!isOpen()));

    // Escape closes the panel and returns focus to the control that opened it.
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && isOpen()) {
            setState(false);
            toggle.focus();
        }
    });

    // Following a link inside the panel navigates; collapse so the panel is not
    // left open behind an in-page anchor target.
    panel.addEventListener("click", (event) => {
        if (event.target.closest("a")) {
            setState(false);
        }
    });

    // Resizing past the desktop breakpoint reveals the horizontal nav; drop the
    // panel so it cannot reappear when shrinking back down.
    desktop.addEventListener("change", (event) => {
        if (event.matches) {
            setState(false);
        }
    });
}
