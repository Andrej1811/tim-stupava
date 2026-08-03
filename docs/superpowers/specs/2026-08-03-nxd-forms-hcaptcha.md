# nxd-forms: hCaptcha — špecifikácia na neskôr

Dohodnuté 2026-08-03, zatiaľ neimplementované. Zadanie od Andreja: keď admin
zadá kľúče, hCaptcha sa automaticky aktivuje na všetkých formulároch, widget
sa vykreslí nad tlačidlom odoslať.

## Návrh

**Admin (plugin).** Nexdigital Forms → Settings, nová sekcia hCaptcha:
Site key + Secret key. Secret šifrovať existujúcim
`NXD_Form_Mailer::encrypt()` / `decrypt()` (AES-256-CTR cez `wp_salt('auth')`).
Kľúče vyplnené = zapnuté všade, prázdne = vypnuté. Žiadny ďalší prepínač.
Uloženie do options (`nxd_hcaptcha_settings` alebo rozšíriť existujúce
settings option).

**Frontend — deľba práce, ktorá sa ľahko zabúda:** viditeľný markup formulára
vykresľuje TÉMA (`template-parts/form.php`), plugin tlačí len skryté polia.
Preto:

- Plugin poskytne helper `nxd_form_hcaptcha(): string` — vráti
  `<div class="h-captcha" data-sitekey="…"></div>` keď je zapnuté, inak `''`.
- Plugin si sám enqueuene `https://js.hcaptcha.com/1/api.js` (defer), len keď
  je zapnuté a na stránke je formulár.
- Téma zavolá helper vo `form.php` nad submit tlačidlom. Token
  `h-captcha-response` si widget vloží do formulára sám, AJAX submit
  (FormData) ho pribalí bez ďalšej práce.

**Server (plugin handler).** Pri zapnutých kľúčoch overiť token cez
`wp_remote_post('https://api.hcaptcha.com/siteverify', ...)` — body: `secret`,
`response`, `remoteip`. Neplatný/chýbajúci token = per-field chyba v rovnakom
tvare ako validátor (kľúč napr. `hcaptcha`), nech ju `form-handler.js` vypíše
štandardne.

**JS (plugin `form-handler.js`).** Po neúspešnom submite zavolať
`hcaptcha.reset()` — token je jednorazový; bez resetu druhý pokus zlyhá aj so
správne vyplneným formulárom.

## Pasce

- **`web/app/plugins/` je gitignorovaný.** Úprava pluginu na disku sa MUSÍ
  preniesť do vlastného repa nxd-forms (bump 1.3.0 → 1.4.0), inak ju najbližší
  deploy zmaže. Rovnaká pasca ako pri `ngo-` → `nxd-` premenovaní.
- **GDPR:** hCaptcha je tretia strana (IP odchádza na Intuition Machines) —
  do `/ochrana-sukromia/` treba pridať odsek (právny základ: oprávnený záujem,
  ochrana pred spamom; tracking cookies nesadí). Stránka má aj tak dva
  `[DOPLNIŤ]` markery, doplniť naraz.
- Štýlovanie: widget je iframe, do `.form-*` slovníka sa nedá obliecť —
  akceptovať default vzhľad, prípadne `data-theme="light"` a `data-size`.
- Formuláre už majú honeypot; hCaptcha je druhá vrstva. Kľúče netreba
  vypĺňať, kým spam reálne nechodí.

## Overenie

Lokálne test kľúče: site key `10000000-ffff-ffff-ffff-000000000001`,
secret `0x0000000000000000000000000000000000000000` (oficiálne hCaptcha test
páry — widget vždy prejde). E2E: submit bez tokenu musí vrátiť chybu, s
tokenom uložiť entry; `rtk grep` na `h-captcha` v vygenerovanom HTML oboch
formulárov (Kontakt, Domov — nápad obyvateľa, keď bude nasadený).
