/**
 * Hero carousel.
 *
 * The track is a scroll-snap row, so swiping and keyboard scrolling already
 * work without this script — it only wires the arrows and dots, and mirrors the
 * position back into them. A single-slide hero never renders the controls, so
 * this module finds nothing to do and exits.
 */

const REDUCED_MOTION = window.matchMedia("(prefers-reduced-motion: reduce)");

export function initHeroCarousel() {
    const carousels = document.querySelectorAll("[data-hero-carousel]");

    carousels.forEach((carousel) => {
        const track = carousel.querySelector("[data-hero-track]");
        const slides = track ? Array.from(track.children) : [];

        if (!track || slides.length < 2) {
            return;
        }

        const dots = Array.from(carousel.querySelectorAll("[data-hero-dot]"));
        const prev = carousel.querySelector("[data-hero-prev]");
        const next = carousel.querySelector("[data-hero-next]");

        let current = 0;

        const setActive = (index) => {
            current = index;

            dots.forEach((dot, i) => {
                const active = i === index;
                dot.dataset.active = String(active);

                if (active) {
                    dot.setAttribute("aria-current", "true");
                } else {
                    dot.removeAttribute("aria-current");
                }
            });
        };

        const goTo = (index) => {
            // Wrap around: a campaign banner that dead-ends on the last slide
            // reads as broken rather than as finished.
            const target = (index + slides.length) % slides.length;

            track.scrollTo({
                left: slides[target].offsetLeft - track.offsetLeft,
                behavior: REDUCED_MOTION.matches ? "auto" : "smooth",
            });

            setActive(target);
        };

        prev?.addEventListener("click", () => goTo(current - 1));
        next?.addEventListener("click", () => goTo(current + 1));

        dots.forEach((dot, index) => {
            dot.addEventListener("click", () => goTo(index));
        });

        // Scrolling is the source of truth — swipes and momentum scrolling move
        // the track without ever calling goTo().
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        setActive(slides.indexOf(entry.target));
                    }
                });
            },
            { root: track, threshold: 0.6 }
        );

        slides.forEach((slide) => observer.observe(slide));

        carousel.addEventListener("keydown", (event) => {
            if (event.key === "ArrowLeft") {
                event.preventDefault();
                goTo(current - 1);
            }

            if (event.key === "ArrowRight") {
                event.preventDefault();
                goTo(current + 1);
            }
        });

        setActive(0);
    });
}
