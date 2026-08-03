# E-mail pre klienta — odovzdanie webu na plnenie obsahu

Finálna verzia, 2026-08-03. Miesta na ručné doplnenie sú označené `«...»`.

---

**Predmet:** Pre Stupavu — web je pripravený, môžete začať plniť obsah

Dobrý deň,

web kampane je hotový a beží na adrese **https://prestupavu.sk**. Je kompletne
postavený a odladený na počítači aj na mobile — teraz je na rade obsah, ktorý
si viete dopĺňať sami.

## V akom režime web beží

Web je zatiaľ v **prípravnom režime**: funguje naplno a uvidí ho každý, komu
pošlete adresu, ale **vyhľadávače ho neindexujú** a meranie návštevnosti je
vypnuté. Obsah teda môžete pokojne dopĺňať a upravovať — kým sa spoločne
nerozhodneme web spustiť, verejnosť ho cez Google nenájde.

**Keď budete mať obsah naplnený, dajte nám prosím vedieť** — prepneme web do
ostrého režimu, čím sa zapne viditeľnosť pre vyhľadávače aj meranie
návštevnosti. Na našej strane je to niekoľkominútový úkon.

## Čo je hotové

- všetky stránky: Domov, Kandidáti aj s profilmi, Výsledky, Program, Novinky,
  Podpora, Kontakt a Ochrana súkromia
- kontaktný formulár aj formulár „Máte nápad, ako zlepšiť Stupavu?" na úvodnej
  stránke, oba so súhlasom so spracovaním údajov
- filtre na stránkach Program a Výsledky — návštevník si vie projekty pozrieť
  podľa oblasti aj podľa fázy prípravy
- cookie lišta, zabezpečenie, denné zálohy a optimalizácia rýchlosti
- administrácia v slovenčine

## Čo potrebujeme od vás

Všetok ukážkový obsah je priamo na obrazovke označený **[UKÁŽKOVÝ TEXT]**
alebo **[DOPLNIŤ]**, takže stačí ísť po týchto značkách. V poradí podľa
dôležitosti:

1. **Nastavenia webu** (v ľavom menu administrácie) — číslo transparentného
   účtu, telefón, adresa, odkazy na sociálne siete a QR kód na platbu.
   Upozorňujeme, že tam teraz sú **vymyslené ukážkové údaje** a web s nimi
   nesmie ísť von.
2. **Profily kandidátov** — krátky popis a životopis. Jedenásť z dvanástich
   kandidátov má zatiaľ ukážkový text. Ak máte video-vizitky, pridajú sa tu.
3. **Ochrana súkromia** — na štyroch miestach treba doplniť právny názov
   a sídlo prevádzkovateľa a dátum zverejnenia.
4. **Novinky** — päť ukážkových článkov nahraďte skutočnými alebo ich zmažte.
5. **Fotografie projektov** — osemnásť z dvadsiatich deviatich dokončených
   projektov nemá fotografiu. Zoznam vidíte priamo v administrácii:
   Projekty → Výsledky, stĺpec „Fotky" s červeným označením „chýbajú".
6. **Súhrnné čísla** na stránke Výsledky — tri až štyri čísla, napríklad počet
   dokončených projektov alebo preinvestovaná suma.

Jedno upozornenie: v administrácii svieti červené hlásenie od Yoast SEO
o blokovaní vyhľadávačov. **Prosíme, neklikajte naň** — je to zámerný stav
prípravného režimu a po spustení zmizne sám.

## Formuláre a e-mail

Správy z oboch formulárov chodia na **info@prestupavu.sk**. Ak potrebujete
zriadiť ďalšie schránky, napríklad pre jednotlivých kandidátov, pošlite nám
prosím zoznam požadovaných adries a pripravíme ich.

## Prístupy

**Administrácia webu**

- adresa: https://prestupavu.sk/wp/wp-admin
- prihlasovacie meno: «MENO»
- heslo: «HESLO»

Heslo si prosím po prvom prihlásení zmeňte (vpravo hore vaše meno → Upraviť
profil → Nastaviť nové heslo) a nikomu ho nepreposielajte.

**E-mailová schránka info@prestupavu.sk**

- webmail: «WEBMAIL_URL»
- prihlasovacie meno: «EMAIL_LOGIN»
- heslo: «EMAIL_HESLO»
- nastavenie v telefóne alebo v Outlooku (IMAP): server «IMAP_SERVER»,
  port «PORT»

Ak čokoľvek nebude jasné — kde sa čo vypĺňa, ako nahrať fotografie —
ozvite sa. Radi vám to ukážeme aj cez krátky hovor so zdieľanou obrazovkou.

S pozdravom
«PODPIS»

---

## Poznámky pre nás (neposielať klientovi)

- Pred odoslaním vytvoriť klientovi vlastný administrátorský účet —
  nezdieľať `andrej`.
- hCaptcha je v pluginu 1.4.0 pripravená, kľúče zámerne nevyplnené.
- Po dotestovaní GTM zmazať `NEXDIGITAL_FORCE_GTM` z `.env` + Clear Site Cache.
- Pri spustení: `.env` na `WP_ENV=production` → **Clear Site Cache** →
  overiť ako anonym → GA4 premenovať property a stream URL → Search Console
  a sitemapa.
