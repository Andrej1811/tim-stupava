/**
 * Copy-to-clipboard buttons.
 *
 * Used by the transparent account number: half of donors will type the IBAN
 * into their banking app by hand, the other half want it on the clipboard.
 *
 * Markup contract: [data-copy="<value>"] and an optional
 * [data-copy-done="<label>"] for the confirmation.
 */

const RESET_AFTER = 2000;

export function initCopy() {
    // Older browsers and any page served over plain HTTP have no clipboard API.
    // The IBAN is printed in full and selectable either way, so the button
    // simply does not appear rather than failing on click.
    if (!navigator.clipboard) {
        document.querySelectorAll("[data-copy]").forEach((button) => {
            button.hidden = true;
        });

        return;
    }

    document.querySelectorAll("[data-copy]").forEach((button) => {
        const original = button.textContent.trim();
        const done = button.dataset.copyDone || original;
        let timer = null;

        button.addEventListener("click", async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copy || "");
            } catch {
                // Denied permission or a non-secure context: leave the label
                // alone rather than claiming a copy that did not happen.
                return;
            }

            button.textContent = done;
            // Announce it — the label change is invisible to a screen reader
            // otherwise, and this button's whole output is that label.
            button.setAttribute("aria-live", "polite");

            clearTimeout(timer);
            timer = setTimeout(() => {
                button.textContent = original;
            }, RESET_AFTER);
        });
    });
}
