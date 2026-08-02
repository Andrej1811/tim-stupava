# Kontext pre nové vlákno — Novinky (blog)

Odovzdávací dokument. Stav k 2026-08-02, vetva `feat/candidate-profile`, posledný commit `c6fde95`.

## Projekt v jednej vete

Kampaňový web úradujúceho primátora Stupavy pre komunálne voľby 2026. Bedrock + WordPress 7.0, vlastná téma `nexdigital`, Tailwind v4 cez Vite, Secure Custom Fields. **Kampaň sa volá „Pre Stupavu" (prestupavu.sk)** — premenovaná 2026-08-02 z „Tím Stupava"; adresár repa a slug témy ostali staré, to je v poriadku.

Projektová pamäť je v `~/.claude/projects/-Users-andrejkarkus-www-tim-stupava/memory/`. Pred prácou si prečítaj aspoň `MEMORY.md`, `tim-stupava-site-content.md`, `scf-block-recipe.md` a `tim-stupava-ui-components.md`.

## Čo už stojí

Hotové sú všetky šablóny okrem blogu: `archive-kandidat.php`, `single-kandidat.php`, `page-program.php`, `page-vysledky.php`, `single-projekt.php`, `page.php`, `front-page.php`.

Stránky poskladané z SCF blokov: Domov, Kontakt, Podpora. Zásady ochrany súkromia sú próza. V DB je 12 kandidátov a 50 projektov (29 dokončených).

## Stav blogu — východisko

| vec | stav |
|---|---|
| `page_for_posts` | stránka **Novinky, ID 8** — už nastavená |
| príspevky | **jediný**, `Hello world!` (ID 1), zvyšok WordPress default |
| kategórie | jedna (Nezaradené), žiadne štítky |
| `index.php` | generický zoznam, `site-container py-10`, mriežka `template-parts/content.php` |
| `archive.php` | to isté, len s `the_archive_title()` |
| `single.php` | nadpis, dátum, náhľad, `the_content()` v `.rich-text`, `max-w-3xl` |
| `template-parts/content.php` | karta s `aspect-video` náhľadom, dátumom a excerptom |

Všetky tri šablóny sú **pôvodné rýchle skice**, nie navrhnuté — používajú `neutral-*` farby, ktoré v palete témy vôbec nie sú (paleta je `brand` / `accent` / `teal` / `sand` / `slate` / `ink`). Karta v `content.php` dostala len jednu opravu: `object-top`, aby portrét v 16:9 boxe neprišiel o tvár.

## Čo treba rozhodnúť ako prvé

1. **Načo Novinky v kampani sú.** Buď tlačové správy a reakcie na dianie, alebo doplnenie odpočtu (fotoreport z otvorenia ihriska), alebo oboje. Rozhodnutie určí, či stačí jeden typ príspevku alebo treba rubriky.
2. **Súvis s projektmi.** Väzba `novinka → projekt` by umožnila na detaile projektu ukázať „čo sa v ňom deje" a naopak. Taxonómia `oblast` už existuje a je použitá na projektoch.
3. **Zaradenie na úvodnú.** Pôvodný plán klienta počítal s pásom „najnovšie Novinky" na Domove. **Nie je postavený** a rozhodli sme, že má zmysel až keď budú aspoň tri skutočné príspevky.
4. **Autorstvo a dátumy.** V kampani je dátum pri príspevku dvojsečný — starý článok navrchu pôsobí, že sa nič nedeje.

## Pravidlá, ktoré platia a nemá zmysel ich znovu objavovať

**Tlačidlá a formuláre sa neštýlujú utilitami.** V `app.css` je komponentový slovník: `.btn` + `.btn-primary` / `.btn-accent` / `.btn-outline` / `.btn-light` / `.btn-ghost`, k tomu `.btn-sm`, `.btn-block`, `.btn-icon`, `.btn-overlay`, `.link-arrow`, a `.form-*` sada. Nové UI ich skladá.

**Magenta je rozpočet, nie farba.** `.btn-accent` patrí výhradne darovacej výzve (hlavička, pätička, stránka Podpora). Blog na ňu nesiahne.

**`.rich-text`** je vlastný komponent pre dlhý text z editora — `@tailwindcss/typography` v projekte nie je a trieda `prose` je mŕtva.

**Sekčné bloky**: `inc/blocks/<name>.php` drží registráciu, field group aj render callback; markup ide do `template-parts/<name>-section.php`, ktorý číta len `$args`. Seeduje sa cez `wp eval-file` s `"mode": "edit"`. `page.php` rozpozná blokovú stránku podľa prefixu `acf/ts-` a vykreslí ju na plnú šírku.

**Pasce, ktoré ma tu už stáli čas:**

- V render callbacku bloku `get_field()` **bez ID** mieri na kontext bloku, nie na príspevok. Podávaj `get_the_ID()`.
- `use function` na konštantu je fatálna chyba — treba `use const`.
- `wp eval-file` obaľuje súbor do `eval()`, takže `declare(strict_types=1)` ani `const` na najvyššej úrovni tam nesmú byť.
- Hodnotu SCF poľa nastavuj cez `update_field()`, nie `wp post meta update` — inak sa nezapíše kľúč poľa a admin ukáže pole prázdne.
- `composer lint` (Pint) padá na 31 existujúcich súborov témy kvôli presetu, ktorý si protirečí so štýlom kódu. **Nie je to brána, nespúšťaj fixer** — píš v štýle okolia.

## Ako overujem prácu

Server: `php -S localhost:8000 -t web` a v `web/app/themes/nexdigital` `npm run dev` (alebo `npm run build`). Keď príde stránka bez CSS, zmaž `public/hot`.

Šablóny nemajú automatizované testy — Pest v projekte **nemá WordPress bootstrap**, takže sa testuje len čistá logika (dnes `Video\resolve()`, 7 testov). Šablóny overujem cez HTTP a **vždy kontrolujem PHP hlásenia**, lebo stránka vracia 200 aj s fatálnou chybou:

```bash
curl -s -o /tmp/x.html -w '%{http_code}' http://localhost:8000/novinky/
grep -ciE 'fatal error|Warning:|Notice:' /tmp/x.html   # musí byť 0
```

## Vecné poznámky ku kampani

Web stojí na tom, že tvrdenia sú overiteľné — pri každom projekte je uvedená reálna fáza povoľovania a nadhodnotený postup je jediné, čo si oponent overí. Rovnaká latka platí pre blog.

Placeholderový obsah nesie viditeľnú značku `[UKÁŽKOVÝ TEXT]` a meta `_ts_placeholder`, aby sa nedal nepozorovane vypublikovať — 11 kandidátov a zásady ochrany súkromia ju stále majú. Ak budeš seedovať ukážkové novinky, drž sa toho istého.

**Nevymýšľaj citáty ani výroky pripísané menovaným osobám.** Preto má repeater vyjadrení na stránke Podpora zámerne prázdny obsah.
