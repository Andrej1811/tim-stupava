import "../css/app.css";

import { initMenu } from "./modules/menu.js";
import { initCookieConsent } from "./modules/cookie-consent.js";

document.addEventListener("DOMContentLoaded", () => {
    initMenu();
    initCookieConsent();
});
