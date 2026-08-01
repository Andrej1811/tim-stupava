/**
 * Gallery lightbox.
 *
 * Written here rather than pulled in as a dependency: the whole need is one
 * dialog, two arrows and a keyboard handler, and a library would ship a focus
 * trap, a zoom engine and a theming layer this site never uses.
 *
 * Built on <dialog>, which gives the modal semantics, the focus trap, the
 * backdrop and Escape for free — everything a hand-rolled overlay gets wrong.
 *
 * Markup contract: a container with [data-lightbox] holding links whose href is
 * the full-size image. Without JavaScript those links still open the photograph,
 * so the gallery degrades to what it was before this file existed.
 */

/** Preload a neighbour so arrowing through does not flash an empty frame. */
function preload(src) {
    if (src) {
        new Image().src = src;
    }
}

class Lightbox {
    constructor(container) {
        this.items = [...container.querySelectorAll("[data-lightbox-item]")];
        this.index = 0;

        if (this.items.length === 0) {
            return;
        }

        this.build();

        this.items.forEach((item, i) => {
            item.addEventListener("click", (event) => {
                event.preventDefault();
                this.open(i);
            });
        });
    }

    build() {
        this.dialog = document.createElement("dialog");
        this.dialog.className =
            "lightbox m-0 h-full max-h-full w-full max-w-full bg-transparent p-0 backdrop:bg-brand-950/90";

        // Static literal — nothing is interpolated into it. Everything that
        // varies per photograph is set later through textContent and .src.
        // Controls use the theme's .btn-overlay component so they match the
        // carousel arrows rather than being styled twice.
        this.dialog.innerHTML = `
            <div class="lightbox-frame relative flex h-full w-full flex-col">
                <div class="flex items-center justify-between gap-4 p-4 text-white">
                    <p data-lightbox-counter class="text-sm font-bold tabular-nums"></p>
                    <button type="button" data-lightbox-close class="btn-overlay">
                        <span class="sr-only">Zavrieť</span>
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex min-h-0 flex-1 items-center justify-center px-4 pb-4">
                    <img data-lightbox-image alt="" class="lightbox-image max-h-full max-w-full object-contain" />
                </div>

                <p data-lightbox-caption class="px-4 pb-6 text-center text-sm text-slate-300"></p>

                <button type="button" data-lightbox-prev
                    class="btn-overlay absolute left-2 top-1/2 -translate-y-1/2 sm:left-4">
                    <span class="sr-only">Predchádzajúca fotografia</span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </button>

                <button type="button" data-lightbox-next
                    class="btn-overlay absolute right-2 top-1/2 -translate-y-1/2 sm:right-4">
                    <span class="sr-only">Ďalšia fotografia</span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>
            </div>
        `;

        document.body.append(this.dialog);

        this.image = this.dialog.querySelector("[data-lightbox-image]");
        this.caption = this.dialog.querySelector("[data-lightbox-caption]");
        this.counter = this.dialog.querySelector("[data-lightbox-counter]");

        const prev = this.dialog.querySelector("[data-lightbox-prev]");
        const next = this.dialog.querySelector("[data-lightbox-next]");
        const single = this.items.length < 2;

        // One photograph needs no arrows, and hidden is better than disabled —
        // a disabled control still asks the reader to work out why.
        prev.hidden = single;
        next.hidden = single;

        prev.addEventListener("click", () => this.show(this.index - 1, -1));
        next.addEventListener("click", () => this.show(this.index + 1, 1));
        this.dialog
            .querySelector("[data-lightbox-close]")
            .addEventListener("click", () => this.dialog.close());

        this.dialog.addEventListener("keydown", (event) => {
            if (event.key === "ArrowRight") {
                event.preventDefault();
                this.show(this.index + 1, 1);
            } else if (event.key === "ArrowLeft") {
                event.preventDefault();
                this.show(this.index - 1, -1);
            }
        });

        // Clicking the backdrop closes. The dialog element fills the viewport,
        // so the test is whether the click landed on the dialog itself rather
        // than on anything inside it.
        this.dialog.addEventListener("click", (event) => {
            if (event.target === this.dialog) {
                this.dialog.close();
            }
        });

        // A modal <dialog> already blocks scrolling in some browsers and not in
        // others; pinning the body makes it consistent.
        this.dialog.addEventListener("close", () => {
            document.body.style.overflow = "";
            this.items[this.index]?.focus();
        });
    }

    open(index) {
        document.body.style.overflow = "hidden";
        this.show(index);
        this.dialog.showModal();
    }

    /**
     * Wraps around, so the arrows never dead-end.
     *
     * `direction` is 0 on open and ±1 when arrowing, which decides which side
     * the next photograph slides in from.
     */
    show(index, direction = 0) {
        const total = this.items.length;
        this.index = ((index % total) + total) % total;

        const item = this.items[this.index];
        const full = item.getAttribute("href");
        const label = item.dataset.lightboxCaption || "";

        if (direction !== 0) {
            // Park the image at the offset, let one frame paint, then release
            // it — without the reflow the browser coalesces both states and
            // nothing moves.
            this.image.classList.add(direction > 0 ? "is-entering-next" : "is-entering-prev");
            void this.image.offsetWidth;
            this.image.classList.remove("is-entering-next", "is-entering-prev");
        }

        this.image.src = full;
        this.image.alt = label;
        this.caption.textContent = label;
        this.caption.hidden = label === "";
        this.counter.textContent = total > 1 ? `${this.index + 1} / ${total}` : "";

        preload(this.items[(this.index + 1) % total]?.getAttribute("href"));
        preload(this.items[(this.index - 1 + total) % total]?.getAttribute("href"));
    }
}

export function initLightbox() {
    // <dialog> is the whole mechanism; without it the links stay plain links to
    // the image, which is a perfectly good fallback.
    if (typeof HTMLDialogElement === "undefined") {
        return;
    }

    document.querySelectorAll("[data-lightbox]").forEach((container) => new Lightbox(container));
}
