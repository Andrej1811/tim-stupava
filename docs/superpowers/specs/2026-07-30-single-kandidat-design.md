# Profil kandidáta — `single-kandidat.php` + naplnenie obsahu

Dátum: 2026-07-30
Stav: schválený návrh, pripravený na implementačný plán

## Cieľ

Kandidáti (`kandidat` CPT) majú od 2026-07-28 verejné URL na `/kandidati/<slug>/`, ktoré vracajú HTTP 200 a nevykreslia nič — chýba šablóna. Karta kandidáta na úvodnej stránke odkazuje práve tam, takže každé kliknutie na „Profil kandidáta" končí na prázdnej stránke.

Tento dokument popisuje šablónu profilu a doplnenie obsahu dvanástim existujúcim záznamom.

## Východiskový stav (overený, nie predpokladaný)

Dvanásť `kandidat` záznamov už v databáze je, ID 35–46, publikovaných, s vyplneným `ts_cislo` (1–12), `ts_pozicia` a `ts_je_lider` na Novisedlákovi (ID 37).

| pole | stav |
|---|---|
| featured image | vyplnené u 11 z 12 — WebP 900×1200, `kandidat-N.webp`. **Vladislav Obadal (č. 7) nemá dodanú ani jednu fotku.** |
| `ts_kratke_bio` | len Novisedlák |
| `post_content` | prázdny u všetkých dvanástich |
| `ts_foto_portret` | prázdne u všetkých dvanástich |
| `ts_email`, `ts_facebook` | prázdne u všetkých dvanástich |
| `ts_video` | len Novisedlák — a je to **demo hodnota** (`youtube.com/watch?v=aqz-KE-bpKQ`, Big Buck Bunny), nie skutočná video-vizitka |

Zdrojové fotografie v `resources/sources/drive-download/` (gitignored) sú dvoch druhov: `Studio_retouched_*.jpg` (ateliér na bielom) a `foto-NN.jpg` (v prostredí). Štúdiové sú už importované ako featured images, takže sa znovu neimportujú.

V téme **neexistuje `prose`** — `@tailwindcss/typography` nie je nainštalovaný, takže trieda `prose max-w-none` v [single.php:29](../../../web/app/themes/nexdigital/single.php#L29) je dnes mŕtva a dlhší text sa vykresľuje bez akejkoľvek typografie.

## Architektúra

`single-kandidat.php` je tenký orchestrátor v štýle `front-page.php`: prečíta polia cez `Fields\field()` a deleguje na template-party. Markup do neho nepatrí.

| súbor | zmena | prečo |
|---|---|---|
| `single-kandidat.php` | nový | orchestrátor |
| `template-parts/candidate-hero.php` | nový | split hero |
| `template-parts/candidate-cta.php` | nový | voličská výzva |
| `template-parts/video-facade.php` | nový | dnes je facade napísaná inline v `candidate-card.php`; profil by bola tretia kópia |
| `inc/video.php` → `Video\source()` | nová funkcia | rozhodnutie „nahraný súbor vyhráva nad odkazom" je dnes duplikované v karte |
| `template-parts/candidate-card.php` | refactor | prepnutie na `Video\source()` a nový partial |
| `resources/css/app.css` → `.rich-text` | nový komponent | typografia životopisu |
| `single.php` | oprava | výmena mŕtveho `prose` za `.rich-text` |
| `inc/fields/candidate.php` | úprava textu | inštrukcia poľa `ts_foto_portret` |

### `Video\source(int $post_id): ?array`

Vracia `['url' => string, 'file' => bool]`, alebo `null` keď kandidát video nemá. Nahraný súbor (`ts_video_subor`) vyhráva nad odkazom (`ts_video`), lebo nepotrebuje tretiu stranu. Odkaz prechádza cez existujúcu `embed_url()`, ktorá pri nerozpoznanom vstupe vráti `null` — a to je signál nevykresliť tlačidlo vôbec, lebo tlačidlo, ktoré nič neotvorí, je horšie než žiadne.

Surové `get_field('ts_video', $id, false)` ostáva obalené v `function_exists()`, aby deaktivácia SCF naďalej degradovala na prázdny obsah namiesto fatálu.

### `template-parts/video-facade.php`

Argumenty: `video` (pole z `Video\source()`), `title` (string, prístupný názov), `size` (`'sm'` | `'lg'`).

Vykreslí len tlačidlo a štítok „Video-vizitka". Obal `[data-video-facade]` aj poster (portrét) ostávajú na volajúcom, lebo v karte aj v profile je posterom iný obrázok v inom pomere. JavaScript sa nemení — `video-facade.js` počúva na `[data-video-play]` / `[data-video-file]` a nahrádza obsah najbližšieho `[data-video-facade]`.

## Šablóna

### Návratový odkaz

Nad hero, na `sand-50`: „← Späť na kandidátov" na `get_post_type_archive_link('kandidat')`. Vykreslí sa len keď archív existuje. Toto je jediná cesta späť — pás „ďalší kandidáti" ani prev/next navigácia v šablóne zámerne nie sú.

### Hero — split

Mriežka `lg:grid-cols-2`, `lg:min-h-[34rem]`.

**Ľavý stĺpec, `bg-brand-950`, biely text**, zvislo centrovaný, `p-6 sm:p-8 lg:p-12`:

- riadok: ballot číslo v kruhu (`border-2 border-teal-400`, `font-black`) + `ts_pozicia` ako drobný verzálkový eyebrow v `teal-400`
- `<h1>` s menom, `font-black`, `text-4xl` → `lg:text-6xl`, `leading-[1.05]`
- `ts_kratke_bio` v `slate-200`
- kontaktný riadok: e-mail a Facebook ako pilulky `border-white/40`; každá sa vykreslí len keď je pole vyplnené, celý riadok zmizne keď nie je ani jedno

**Pravý stĺpec, `bg-sand-100`**: featured image, `object-cover object-top`. Keď kandidát fotku nemá, nastúpi ten istý písmenkový fallback, aký už používa `candidate-card.php` — kvôli Obadalovi to nie je hypotetický prípad. Keď má video, stĺpec dostane `data-video-facade` a facade tlačidlo vo veľkosti `lg`.

**Mobil**: portrét je vizuálne prvý (`order-first lg:order-none`), pomer `aspect-4/5` — rovnaká hodnota, akú už používa featured karta, takže fotka 3:4 nezaberie 133 vw. V DOM ostáva panel prvý, takže čítačka obrazovky číta meno pred obrázkom.

Farby sledujú pravidlá z návrhového systému: teal len na tmavom brand povrchu, magenta sa tu nemíňa vôbec.

### Životopis

`site-container max-w-3xl py-16`, obsah z klasického editora vykreslený cez `the_content()` v obale `.rich-text`. Keď je obsah prázdny, sekcia sa nevykreslí — prázdny nadpis „O kandidátovi" je horší než jeho absencia.

`.rich-text` je vlastný komponent v `@layer components`, nie `@tailwindcss/typography`. Dôvod je konkrétny: životopis je odsek, zoznam a občas medzinadpis, takže plugin by pridal závislosť a niekoľko kilobajtov CSS pre pravidlá, ktoré tu nikto nepoužije — a téma už raz mala problém s tým, že neošetrené WordPress štýly prebíjajú utility triedy. Vlastný komponent pod našou kontrolou je predvídateľnejší.

### Voličské CTA

Pás `bg-brand-950` s **fotkou v prostredí (`ts_foto_portret`) ako pozadím** pod prekryvom `bg-brand-950/85`. Bez fotky ostáva plocha jednofarebná, layout sa nemení.

Obsah: eyebrow v `teal-400`, nadpis s ballot číslom vysádzaným veľkým stupňom, jedna veta o krúžkovaní, a dve tlačidlá — „Všetci kandidáti" (archív) a „Náš program" (stránka `program`, ID 7). Každé tlačidlo sa vynechá, keď sa jeho URL nepodarí zostaviť.

Texty sú v šablóne natvrdo, nie v poliach. Je to chrome opakujúci sa na dvanástich stránkach, nie obsah, ktorý klient skladá — rovnaká úvaha ako pri pätičke.

**Zmena oproti dnešku:** pole `ts_foto_portret` má v inštrukcii „používa sa v hlavičke profilu kandidáta". Hlavičku zabral štúdiový portrét, takže inštrukcia sa prepíše na skutočné miesto použitia. Fotka schovaná za tmavým panelom pri 8 % viditeľnosti je premrhaná; nad výzvou „Krúžkujte číslo 3" má prácu.

## Naplnenie obsahu

Dvanásť záznamov už existuje, takže nejde o seed, ale o doplnenie chýbajúcich polí. Skript beží cez WP-CLI (`wp eval-file`, `--path=web/wp`) a **žije v scratchpade, nie v repozitári** — je to jednorazová operácia nad lokálnou databázou.

### Fotografie v prostredí

Pre každého kandidáta sa vyberie **prvá vodorovná** `foto-*.jpg` v jeho priečinku. Výber musí kontrolovať orientáciu, nie brať prvý súbor: Adela Urbánová má ako `foto-32.jpg` portrét 4000×6000 a orezanie na 3:2 by z neho urobilo pás bez hlavy.

| č. | kandidát | súbor |
|---|---|---|
| 1 | Ladislav Hajdu | `foto-38.jpg` |
| 2 | Štefan Haulík | `foto-46.jpg` |
| 3 | Peter Novisedlák | `foto-55.jpg` |
| 4 | Sofia Piatnica | `foto-40.jpg` |
| 5 | Adela Urbánová | `foto-33.jpg` (`foto-32` je na výšku) |
| 6 | Ľubomír Bugala | `foto-29.jpg` |
| 7 | Vladislav Obadal | — dodaných nula fotografií |
| 8 | Patrik Kollaroci | `foto-22.jpg` |
| 9 | Marek Lacka | `foto-36.jpg` |
| 10 | Matej Piatnica | `foto-44.jpg` |
| 11 | Juraj Vachálek | `foto-34.jpg` |
| 12 | Václav Měšťan | `foto-42.jpg` |

Spracovanie: `sips` centrovaný orez na 3:2, potom `cwebp -q 82` na 1800×1200. Zdroje sú okolo 6000×4000, čiže orez je minimálny. Import cez `media_handle_sideload()`, ID sa zapíše do `ts_foto_portret` (pole vracia ID, nie pole).

Existujúca príloha 20 („Hero — Peter Novisedlák v parku") sa nepoužije — je orezaná na 1200×1200 pre hero karusel, nie na 3:2.

### Texty

**Peter Novisedlák (č. 3)** dostane skutočný životopis postavený na faktoch z programového dokumentu — je to úradujúci primátor a tie fakty existujú.

**Zvyšných jedenásť** sú reálne, menom uvedené osoby, o ktorých nemáme životopisné údaje. Vymyslený životopis skutočného človeka na kampaňovom webe nie je otázka štýlu, ale tvrdenie, ktoré si niekto overí. Preto:

- placeholder text nesie **viditeľnú značku priamo v texte** (prefix `[UKÁŽKOVÝ TEXT]`), takže je rozpoznateľný v administrácii aj na obrazovke a nemôže sa nepozorovane dostať do produkcie
- text je zámerne neurčitý — hovorí o zapojení do života mesta, nie o konkrétnej práci, škole či rodine
- každý takýto záznam dostane meta `_ts_placeholder = 1`, takže jediný `wp post list --meta_key=_ts_placeholder` pred spustením ukáže, čo klient ešte neprepísal
- Novisedlák túto značku nedostane

`ts_pozicia` ostáva ako je („Kandidát do zastupiteľstva"). Je to pravdivé a stručné; skutočné povolania nemáme a vymýšľať ich by bol ten istý problém.

`ts_email` a `ts_facebook` sa **nevypĺňajú**. Kontaktný riadok v hero paneli sa jednoducho nevykreslí — a to je zároveň test, že podmienené vykresľovanie funguje. Vymyslená e-mailová adresa reálnej osoby je horšia než žiadna.

### Demo hodnota na odstránenie pred spustením

`ts_video` na Novisedlákovi (ID 37) obsahuje odkaz na Big Buck Bunny. Patrí k tej istej skupine ukážkových dát ako seedovaný IBAN, telefón a logá partnerov — pred spustením musí zmiznúť.

## Overenie

1. `/kandidati/mgr-peter-novisedlak-mba/` vykreslí hero s číslom 3, video facade a fotku v prostredí v CTA
2. `/kandidati/vladislav-obadal/` vykreslí písmenkový fallback namiesto portrétu, jednofarebné CTA, a nespadne
3. profil bez videa nevykreslí tlačidlo prehrávania
4. kliknutie na play nahradí poster prehrávačom a sieťová požiadavka na YouTube odíde až po kliknutí
5. deaktivácia SCF → profil vráti 200 bez fatálu, s menom a fotkou, bez poľových údajov
6. `candidate-card.php` po refactore naďalej funguje na úvodnej stránke v oboch variantoch
7. mobil: portrét je nad menom, DOM poradie čítačky ostáva meno → portrét

## Vedome vynechané

- pás „ďalší kandidáti" a prev/next navigácia — návrat drží odkaz hore
- samostatná video sekcia — video žije na portréte v hero, rovnako ako na featured karte
- editovateľné texty CTA — chrome, nie obsah
- `archive-kandidat.php` — samostatná úloha; profil naň len odkazuje
