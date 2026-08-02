import "../css/app.css";

import { initMenu } from "./modules/menu.js";
import { initCookieConsent } from "./modules/cookie-consent.js";
import { initHeroCarousel } from "./modules/hero-carousel.js";
import { initVideoFacade } from "./modules/video-facade.js";
import { initLightbox } from "./modules/lightbox.js";
import { initCopy } from "./modules/copy.js";

// Not deferred to DOMContentLoaded: GTM holds its tags for 500 ms waiting for
// the stored consent decision, and a module script already runs after the
// document is parsed, so document.body is there.
initCookieConsent();

document.addEventListener("DOMContentLoaded", () => {
    initMenu();
    initHeroCarousel();
    initVideoFacade();
    initLightbox();
    initCopy();
});
