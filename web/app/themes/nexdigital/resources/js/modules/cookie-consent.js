/**
 * Cookie consent banner — vanilla-cookieconsent v3.
 *
 * Opt-in mode: nothing beyond the strictly necessary category runs until the
 * visitor says so. Two ways to gate a third-party script:
 *
 *   1. Markup — the library flips these on itself, no JS needed:
 *      <script type="text/plain" data-category="analytics" src="..."></script>
 *
 *   2. Code — listen for the event this module dispatches:
 *      document.addEventListener("nexdigital:consent", (e) => {
 *          if (e.detail.categories.includes("analytics")) { ... }
 *      });
 *
 * Any element carrying data-cc="show-preferencesModal" reopens the settings
 * dialog; the footer link uses it. Required by GDPR — consent must be as easy
 * to withdraw as it was to give.
 *
 * Google Tag Manager is driven from here through Consent Mode v2 — see
 * syncGoogleConsent() and inc/analytics.php.
 */

import * as CookieConsent from "vanilla-cookieconsent";

/** <script type="application/json"> emitted by inc/consent.php. */
const CONFIG_ID = "nexdigital-consent-config";

/** Used when the WP privacy page is unset — see consent.php for the same path. */
const FALLBACK_PRIVACY_URL = "/ochrana-osobnych-udajov/";

/**
 * Bump when the categories or the policy text materially change: every visitor
 * is then re-asked instead of silently keeping a stale consent.
 */
const REVISION = 1;

/** Re-ask after roughly six months, in line with EDPB guidance. */
const EXPIRES_AFTER_DAYS = 182;

function settings() {
    const el = document.getElementById(CONFIG_ID);

    if (!el) {
        return {};
    }

    try {
        return JSON.parse(el.textContent) || {};
    } catch {
        return {};
    }
}

/**
 * Translate our categories into Google Consent Mode v2 signals.
 *
 * inc/analytics.php loads GTM with every identifying storage type denied and a
 * 500 ms `wait_for_update` hold; this is the update it waits for. When no
 * container id is configured `gtag` never exists and this is a no-op.
 *
 * The dataLayer event is there so tags that are not consent-mode aware (a Meta
 * pixel, say) can use it as their trigger instead.
 */
function syncGoogleConsent(categories) {
    const analytics = categories.includes("analytics") ? "granted" : "denied";
    const marketing = categories.includes("marketing") ? "granted" : "denied";

    if (typeof window.gtag === "function") {
        window.gtag("consent", "update", {
            analytics_storage: analytics,
            ad_storage: marketing,
            ad_user_data: marketing,
            ad_personalization: marketing,
            personalization_storage: marketing,
        });
    }

    window.dataLayer?.push({
        event: "cookie_consent_update",
        cookie_consent_categories: categories,
    });
}

/**
 * Reveal the floating settings button.
 *
 * PHP prints it with `hidden` — it shares the bottom-left corner with the
 * consent banner, so it may only appear once a decision has been made and the
 * banner is gone. `inline-flex` is added rather than shipped in the markup
 * because Tailwind's display utilities all sit in the same layer, so `hidden`
 * and `inline-flex` on one element would resolve by stylesheet order.
 */
function revealPreferencesButton() {
    document.querySelectorAll('[data-cc="show-preferencesModal"]').forEach((el) => {
        el.classList.remove("hidden");
        el.classList.add("inline-flex");
    });
}

/**
 * Let the rest of the site react without importing the library. `detail` holds
 * the accepted category ids, so consumers never touch the cookie themselves.
 */
function emit() {
    const { acceptedCategories: categories } = CookieConsent.getUserPreferences();

    revealPreferencesButton();
    syncGoogleConsent(categories);

    document.dispatchEvent(
        new CustomEvent("nexdigital:consent", { detail: { categories } })
    );
}

export function initCookieConsent() {
    const { privacyUrl = FALLBACK_PRIVACY_URL } = settings();

    // Embeds and tag managers live outside this bundle but still need to ask
    // "may I run?" — expose the API rather than have them read the cookie.
    window.CookieConsent = CookieConsent;

    CookieConsent.run({
        mode: "opt-in",
        revision: REVISION,
        autoShow: true,
        // No cookie wall: the site stays readable while the banner is open.
        disablePageInteraction: false,
        hideFromBots: true,

        cookie: {
            name: "tim_stupava_consent",
            path: "/",
            sameSite: "Lax",
            expiresAfterDays: EXPIRES_AFTER_DAYS,
        },

        guiOptions: {
            consentModal: {
                layout: "box wide",
                position: "bottom left",
                // Accept and reject get the same visual weight — a dark-pattern
                // "reject" is treated as invalid consent by the ÚOOÚ.
                equalWeightButtons: true,
                flipButtons: false,
            },
            preferencesModal: {
                layout: "box",
                position: "right",
                equalWeightButtons: true,
                flipButtons: false,
            },
        },

        categories: {
            necessary: {
                enabled: true,
                readOnly: true,
            },
            analytics: {
                autoClear: {
                    cookies: [
                        { name: /^_ga/ },
                        { name: "_gid" },
                        { name: /^_clck|^_clsk/ },
                    ],
                },
            },
            marketing: {
                autoClear: {
                    cookies: [{ name: "_fbp" }, { name: "fr" }, { name: /^_gcl_/ }],
                },
            },
        },

        language: {
            default: "sk",
            translations: {
                sk: {
                    consentModal: {
                        title: "Vážime si vaše súkromie",
                        description:
                            "Nevyhnutné súbory cookie zabezpečujú základné fungovanie tohto webu. Ďalšie nám pomáhajú merať návštevnosť a zlepšovať obsah — použijeme ich len s vaším súhlasom, ktorý môžete kedykoľvek odvolať.",
                        acceptAllBtn: "Prijať všetko",
                        acceptNecessaryBtn: "Odmietnuť",
                        showPreferencesBtn: "Nastaviť podrobne",
                        footer: `<a href="${privacyUrl}">Ochrana osobných údajov</a>`,
                    },
                    preferencesModal: {
                        title: "Nastavenia súkromia",
                        acceptAllBtn: "Prijať všetko",
                        acceptNecessaryBtn: "Odmietnuť všetko",
                        savePreferencesBtn: "Uložiť nastavenia",
                        closeIconLabel: "Zavrieť",
                        serviceCounterLabel: "služba|služby",
                        sections: [
                            {
                                title: "Ako pracujeme so súbormi cookie",
                                description:
                                    "Cookies sú malé súbory, ktoré si web ukladá vo vašom prehliadači. Nižšie si viete zapnúť alebo vypnúť jednotlivé kategórie. Voľbu vieme kedykoľvek zmeniť cez odkaz „Nastavenia cookies“ v pätičke.",
                            },
                            {
                                title: "Nevyhnutné",
                                description:
                                    "Potrebné na základné fungovanie webu — zapamätanie si vašej voľby v tomto okne, bezpečnosť formulárov a prihlásenie do administrácie. Bez nich by web nefungoval, preto ich nie je možné vypnúť.",
                                linkedCategory: "necessary",
                            },
                            {
                                title: "Analytické",
                                description:
                                    "Anonymná štatistika návštevnosti: ktoré stránky ľudí zaujímajú a odkiaľ na web prišli. Údaje používame súhrnne, na zlepšovanie obsahu kampane.",
                                linkedCategory: "analytics",
                            },
                            {
                                title: "Marketingové",
                                description:
                                    "Umožňujú merať účinnosť našich reklám na sociálnych sieťach a vo vyhľadávaní, a nezobrazovať tú istú reklamu dokola.",
                                linkedCategory: "marketing",
                            },
                            {
                                title: "Ďalšie informácie",
                                description: `Podrobnosti o spracúvaní osobných údajov a kontakt na nás nájdete v dokumente <a href="${privacyUrl}">Ochrana osobných údajov</a>.`,
                            },
                        ],
                    },
                },
            },
        },

        onConsent: emit,
        onChange: emit,
    });
}
