# Profil kandidáta — implementačný plán

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Dať `kandidat` CPT funkčnú šablónu profilu a doplniť dvanástim existujúcim záznamom chýbajúci obsah.

**Architecture:** `single-kandidat.php` je tenký orchestrátor, ktorý číta polia cez `Fields\field()` a deleguje na tri template-party (hero, telo, CTA). Rozhodovanie „nahraný súbor vyhráva nad odkazom" sa vyťahuje z `candidate-card.php` do čistej funkcie `Video\resolve()`, ktorá je jediná časť tejto práce pokrytá automatizovaným testom — zvyšok sú šablóny, ktoré sa overujú cez HTTP proti lokálnemu serveru.

**Tech Stack:** WordPress 7.0 na Bedrocku, PHP 8.3, Secure Custom Fields 6.9.2, Tailwind v4 cez Vite, Pest 4 (bez WordPress bootstrapu), WP-CLI.

## Global Constraints

- PHP 8.3+, každý nový súbor začína `declare(strict_types=1);` a `if (!defined('ABSPATH')) { exit; }`
- Šablóny čítajú polia **výhradne** cez `Fields\field()` / `Fields\option()`, nikdy cez holé `get_field()` — jedinou výnimkou je surová hodnota oembed poľa, ktorá musí byť obalená v `function_exists('get_field')`
- Každý výstup je escapovaný: `esc_html()`, `esc_url()`, `esc_attr()`
- Farebné role z návrhového systému: `teal-*` **len** na tmavých `brand-*` povrchoch, `accent-*` (magenta) sa v tejto práci nepoužije vôbec — je vyhradená pre darovacie CTA v hlavičke a pätičke
- Texty rozhrania cez `__()` / `esc_html_e()` s textovou doménou `nexdigital`
- Lucide ikony ako inline SVG na mriežke 24×24, žiadny ikonový font
- `leading-*` musí sedieť na tom istom elemente ako `text-*` — Tailwind v4 registruje `--tw-leading` s `inherits: false`, takže z rodiča nekaskáduje
- Lint pred každým commitom: `composer lint` (Pint)
- Lokálny beh: `php -S localhost:8000 -t web` v jednom termináli, `npm run dev` v `web/app/themes/nexdigital` v druhom. Keď stránka príde bez CSS, zmaž `web/app/themes/nexdigital/public/hot`

---

### Task 1: `Video\resolve()` — rozhodnutie súbor vs. odkaz

Dnes je toto rozhodnutie napísané inline v `candidate-card.php:39-47`. Profil by bol druhá kópia. Vyťahuje sa do `inc/video.php` a rozdeľuje na dve funkcie: `resolve()` je čistá a testovateľná, `source()` je tenký adaptér, ktorý na ňu privedie hodnoty z WordPressu.

**Files:**
- Modify: `web/app/themes/nexdigital/inc/video.php`
- Test: `tests/Unit/VideoTest.php` (create)

**Interfaces:**
- Consumes: existujúca `NexDigital\Theme\Video\embed_url(string $url): ?string`
- Produces:
  - `NexDigital\Theme\Video\resolve(mixed $file, string $link): ?array` — vracia `['url' => string, 'file' => bool]` alebo `null`
  - `NexDigital\Theme\Video\source(int $post_id): ?array` — to isté, načítané z príspevku

- [ ] **Step 1: Write the failing test**

Vytvor `tests/Unit/VideoTest.php`. Pest v tomto projekte **nemá WordPress bootstrap** — `phpunit.xml.dist` načítava len `vendor/autoload.php`. Preto sa `inc/video.php` načíta ručne a `ABSPATH` sa musí definovať skôr, inak súbor na svojej stráži zavolá `exit` a testy tíško skončia.

```php
<?php

declare(strict_types=1);

use function NexDigital\Theme\Video\resolve;

beforeAll(function () {
    // inc/video.php sa ukončí, keď ABSPATH nie je definovaná — je to jeho
    // ochrana pred priamym HTTP prístupom. V teste ju len potrebujeme prejsť.
    if (!defined('ABSPATH')) {
        define('ABSPATH', __DIR__);
    }

    require_once __DIR__ . '/../../web/app/themes/nexdigital/inc/video.php';
});

test('nahraný súbor vyhráva nad odkazom na YouTube', function () {
    $result = resolve(
        ['url' => 'https://example.test/app/uploads/vizitka.mp4'],
        'https://www.youtube.com/watch?v=aqz-KE-bpKQ'
    );

    expect($result)->toBe([
        'url'  => 'https://example.test/app/uploads/vizitka.mp4',
        'file' => true,
    ]);
});

test('samotný odkaz na YouTube sa prevedie na adresu prehrávača', function () {
    $result = resolve(null, 'https://www.youtube.com/watch?v=aqz-KE-bpKQ');

    expect($result['file'])->toBeFalse()
        ->and($result['url'])->toContain('youtube-nocookie.com/embed/aqz-KE-bpKQ');
});

test('bez videa vracia null', function () {
    expect(resolve(null, ''))->toBeNull();
});

test('nerozpoznaný odkaz vracia null, nie polovičný výsledok', function () {
    // Tlačidlo, ktoré nič neotvorí, je horšie než žiadne tlačidlo.
    expect(resolve(null, 'https://example.test/nieco.html'))->toBeNull();
});

test('prázdne pole súboru prepadne na odkaz', function () {
    // SCF vracia pole aj vtedy, keď editor súbor odobral.
    $result = resolve(['url' => ''], 'https://vimeo.com/123456789');

    expect($result['file'])->toBeFalse()
        ->and($result['url'])->toContain('player.vimeo.com/video/123456789');
});

test('false z deaktivovaného SCF nespôsobí chybu', function () {
    expect(resolve(false, ''))->toBeNull();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/pest tests/Unit/VideoTest.php`
Expected: FAIL — `Call to undefined function NexDigital\Theme\Video\resolve()`

- [ ] **Step 3: Write minimal implementation**

Pripoj na koniec `web/app/themes/nexdigital/inc/video.php`:

```php
/**
 * Pick the video a candidate actually has.
 *
 * An uploaded file beats a link: it needs no third-party player at all. The
 * link goes through embed_url(), which returns null for anything it cannot
 * turn into a player URL — and null here is the signal to render no play
 * button, because a button that opens nothing is worse than no button.
 *
 * Kept separate from source() so the decision can be tested without a
 * WordPress bootstrap.
 *
 * @param mixed  $file SCF file field value; an array with a url when set.
 * @param string $link Raw oembed field value — the page URL, not the iframe.
 * @return array{url: string, file: bool}|null
 */
function resolve(mixed $file, string $link): ?array {
    $uploaded = is_array($file) ? trim((string) ($file['url'] ?? '')) : '';

    if ($uploaded !== '') {
        return ['url' => $uploaded, 'file' => true];
    }

    $embed = embed_url($link);

    return $embed === null ? null : ['url' => $embed, 'file' => false];
}

/**
 * The same decision, for a candidate post.
 *
 * The oembed field's rendered value is an iframe this site never prints, so
 * the raw value is requested with get_field()'s third argument set to false.
 * That is the one bare get_field() call in the theme, hence the guard.
 *
 * @return array{url: string, file: bool}|null
 */
function source(int $post_id): ?array {
    $link = function_exists('get_field')
        ? (string) (get_field(\NexDigital\Theme\Fields\key('video'), $post_id, false) ?? '')
        : '';

    return resolve(\NexDigital\Theme\Fields\field('video_subor', $post_id), $link);
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./vendor/bin/pest tests/Unit/VideoTest.php`
Expected: PASS, 6 testov

- [ ] **Step 5: Lint and commit**

```bash
composer lint
git add tests/Unit/VideoTest.php web/app/themes/nexdigital/inc/video.php
git commit -m "refactor(video): extract the file-beats-link decision into Video\\resolve()"
```

---

### Task 2: Facade tlačidlo ako template-part

Markup facade (tlačidlo, prehrávacia ikona, štítok) je dnes zapísaný priamo v `candidate-card.php`. Profil ho potrebuje tiež, v inej veľkosti. Vyťahuje sa do partialu; obal `[data-video-facade]` aj poster ostávajú na volajúcom, lebo v karte aj v profile je posterom iný obrázok.

**Files:**
- Create: `web/app/themes/nexdigital/template-parts/video-facade.php`
- Modify: `web/app/themes/nexdigital/template-parts/candidate-card.php`

**Interfaces:**
- Consumes: `Video\source()` z Tasku 1
- Produces: partial `template-parts/video-facade` s argumentmi `video` (`array{url,file}`), `title` (string), `size` (`'sm'`|`'lg'`)

- [ ] **Step 1: Create the partial**

Vytvor `web/app/themes/nexdigital/template-parts/video-facade.php`:

```php
<?php
/**
 * Video facade — the play button, not the player.
 *
 * The player URL sits in a data attribute and nothing is fetched until the
 * visitor clicks; resources/js/modules/video-facade.js then replaces the
 * contents of the nearest [data-video-facade] ancestor. That ancestor and the
 * poster behind it belong to the caller, because the card and the profile use
 * different images at different aspect ratios.
 *
 * Expected args:
 *   video array{url: string, file: bool}  From Video\source().
 *   title string                          Accessible name of the video.
 *   size  string                          'sm' (card) or 'lg' (profile).
 *
 * @package NexDigital
 */

declare(strict_types=1);

$video = is_array($args['video'] ?? null) ? $args['video'] : null;

if ($video === null || trim((string) ($video['url'] ?? '')) === '') {
    return;
}

$title = (string) ($args['title'] ?? '');
$large = ($args['size'] ?? 'sm') === 'lg';

// An uploaded file plays in a native <video>; a link needs an iframe. The JS
// picks the tag by which attribute it finds, so the name carries the meaning.
$attribute = ($video['file'] ?? false) ? 'data-video-file' : 'data-video-play';

$button = $large ? 'h-20 w-20' : 'h-16 w-16';
$icon = $large ? 'h-9 w-9' : 'h-7 w-7';
?>

<button
    type="button"
    class="group absolute inset-0 flex items-center justify-center bg-brand-950/25 transition hover:bg-brand-950/10"
    <?php echo esc_attr($attribute); ?>="<?php echo esc_url($video['url']); ?>"
    data-video-title="<?php echo esc_attr($title); ?>"
>
    <span class="sr-only"><?php esc_html_e('Prehrať video-vizitku', 'nexdigital'); ?></span>
    <span class="<?php echo esc_attr($button); ?> flex items-center justify-center rounded-full bg-white text-brand-700 shadow-lg transition group-hover:scale-105">
        <svg class="<?php echo esc_attr($icon); ?> ml-1" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M8 5.14v13.72a1 1 0 0 0 1.54.84l10.3-6.86a1 1 0 0 0 0-1.68L9.54 4.3A1 1 0 0 0 8 5.14Z" />
        </svg>
    </span>
</button>

<span class="pointer-events-none absolute bottom-4 left-4 rounded bg-brand-950/80 px-3 py-1.5 text-[0.6875rem] font-bold uppercase tracking-[0.12em] text-teal-400 backdrop-blur">
    <?php esc_html_e('Video-vizitka', 'nexdigital'); ?>
</span>
```

- [ ] **Step 2: Point the card at the new helpers**

V `web/app/themes/nexdigital/template-parts/candidate-card.php` nahraď blok riadkov 36–47 (komentár `// Two ways to have a video…` a všetky štyri priradenia `$video_file`, `$video_file_url`, `$video_embed`, `$video`, `$video_attr`) jediným riadkom:

```php
$video = source($post_id);
```

a doplň import k existujúcim `use function` deklaráciám na začiatku súboru:

```php
use function NexDigital\Theme\Video\source;
```

Import `use function NexDigital\Theme\Video\embed_url;` zmaž — karta ju už priamo nevolá.

- [ ] **Step 3: Swap the inline facade markup for the partial**

V tom istom súbore nahraď celý blok `<?php if ($video !== null) : ?> … <?php endif; ?>` vo `featured` vetve (dnes riadky 96–116, teda tlačidlo aj štítok „Video-vizitka") týmto:

```php
            <?php get_template_part('template-parts/video-facade', null, [
                'video' => $video,
                'title' => sprintf(__('Video-vizitka — %s', 'nexdigital'), $name),
                'size'  => 'sm',
            ]); ?>
```

Podmienka na obale ostáva nezmenená — `<?php echo $video !== null ? 'data-video-facade' : ''; ?>` funguje ďalej, lebo `$video` má naďalej hodnotu `null` alebo pole.

- [ ] **Step 4: Verify the front page still renders both card variants**

```bash
curl -s http://localhost:8000/ | grep -c 'data-video-facade'
curl -s http://localhost:8000/ | grep -o 'data-video-play="[^"]*"' | head -1
```

Expected: prvý príkaz vypíše `1` (jedna featured karta s videom — Novisedlák), druhý vypíše `data-video-play="https://www.youtube-nocookie.com/embed/aqz-KE-bpKQ?autoplay=1&#038;rel=0&#038;modestbranding=1"`.

```bash
curl -s http://localhost:8000/ | grep -c 'Profil kandidáta'
```

Expected: `1` — featured karta je stále celá.

- [ ] **Step 5: Lint and commit**

```bash
composer lint
git add web/app/themes/nexdigital/template-parts/
git commit -m "refactor(candidates): extract the video facade into a template part"
```

---

### Task 3: `.rich-text` — typografia dlhého textu

Téma nemá `@tailwindcss/typography`, takže trieda `prose max-w-none` v `single.php:29` je dnes bez účinku a životopis by sa vykreslil ako neformátovaná stena textu. Namiesto pridania pluginu dostane téma vlastný komponent — životopis je odsek, zoznam a občas medzinadpis, a téma už raz mala problém s tým, že cudzie neošetrené štýly prebíjajú utility triedy.

**Files:**
- Modify: `web/app/themes/nexdigital/resources/css/app.css`
- Modify: `web/app/themes/nexdigital/single.php:29`

**Interfaces:**
- Produces: CSS trieda `.rich-text` pre obal výstupu `the_content()`

- [ ] **Step 1: Add the component**

Do `web/app/themes/nexdigital/resources/css/app.css`, do existujúceho bloku `@layer components` (za `.site-container`, pred `.hero-track`), vlož:

```css
    /* Long-form editor output — candidate CVs, page intros.
       Not @tailwindcss/typography: the plugin ships rules for tables, figures,
       code and quotes that this site never publishes, and the theme has
       already been bitten once by unlayered styles from outside overriding
       utilities. Sitting in @layer components keeps it below anything a
       template adds on the same element. */
    .rich-text {
        color: var(--color-slate-700);
        font-size: 1rem;
        line-height: 1.75;
    }

    .rich-text > * + * {
        margin-block-start: 1.25em;
    }

    .rich-text h2,
    .rich-text h3 {
        color: var(--color-ink);
        font-weight: 900;
        letter-spacing: -0.01em;
        line-height: 1.2;
    }

    .rich-text h2 {
        font-size: 1.5rem;
        margin-block-start: 2.25em;
    }

    .rich-text h3 {
        font-size: 1.125rem;
        margin-block-start: 2em;
    }

    /* The first heading opens the section — the space above it belongs to the
       section's own padding, not to the heading. */
    .rich-text > :first-child {
        margin-block-start: 0;
    }

    .rich-text strong {
        color: var(--color-ink);
        font-weight: 700;
    }

    .rich-text a {
        color: var(--color-brand-600);
        text-decoration: underline;
        text-underline-offset: 0.2em;
    }

    .rich-text a:hover {
        color: var(--color-brand-700);
    }

    .rich-text ul,
    .rich-text ol {
        padding-inline-start: 1.5rem;
    }

    .rich-text ul {
        list-style: disc;
    }

    .rich-text ol {
        list-style: decimal;
    }

    .rich-text li + li {
        margin-block-start: 0.5em;
    }

    .rich-text li::marker {
        color: var(--color-brand-400);
    }
```

- [ ] **Step 2: Replace the dead class in single.php**

V `web/app/themes/nexdigital/single.php` nahraď na riadku 29:

```php
        <div class="prose max-w-none">
```

za:

```php
        <div class="rich-text">
```

`max-w-none` odchádza s ňou — `.rich-text` šírku nenastavuje a `<article>` už má `max-w-3xl`.

- [ ] **Step 3: Verify the CSS is generated**

Tailwind v4 tree-shakuje, takže komponent, ktorý sa nikde nepoužije, sa nevygeneruje. `@source "../../**/*.php"` pokrýva šablóny, takže po použití v `single.php` musí trieda v builde byť:

```bash
cd web/app/themes/nexdigital && npm run build && grep -c 'rich-text' public/build/assets/*.css
```

Expected: číslo väčšie než 0.

- [ ] **Step 4: Commit**

```bash
git add web/app/themes/nexdigital/resources/css/app.css web/app/themes/nexdigital/single.php
git commit -m "feat(css): add a .rich-text component for long-form editor output"
```

---

### Task 4: Hero profilu

Split hero podľa schváleného návrhu: tmavý panel s menom a údajmi vľavo, štúdiový portrét na piesku vpravo, video facade na portréte.

**Files:**
- Create: `web/app/themes/nexdigital/template-parts/candidate-hero.php`

**Interfaces:**
- Consumes: `Fields\field()`, `Video\source()` (Task 1), partial `template-parts/video-facade` (Task 2)
- Produces: partial `template-parts/candidate-hero` s jediným argumentom `post_id` (int)

- [ ] **Step 1: Create the hero part**

Vytvor `web/app/themes/nexdigital/template-parts/candidate-hero.php`:

```php
<?php
/**
 * Candidate profile hero.
 *
 * The same two-column shape as the featured card on the front page, so a
 * visitor who clicked that card lands on something that reads as the same
 * object made larger. Dark panel carries the name and the ballot number; the
 * studio portrait sits on sand and doubles as the video poster.
 *
 * Expected args:
 *   post_id int Candidate post.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Fields\field;
use function NexDigital\Theme\Video\source;

$post_id = (int) ($args['post_id'] ?? 0);

if ($post_id <= 0) {
    return;
}

$name = get_the_title($post_id);
$number = trim((string) (field('cislo', $post_id) ?? ''));
$role = trim((string) (field('pozicia', $post_id) ?? ''));
$bio = trim((string) (field('kratke_bio', $post_id) ?? ''));
$email = trim((string) (field('email', $post_id) ?? ''));
$facebook = trim((string) (field('facebook', $post_id) ?? ''));

$video = source($post_id);
?>

<section class="bg-sand-100 lg:grid lg:min-h-[34rem] lg:grid-cols-2">

    <?php // Panel first in the DOM so a screen reader reads the name before the
          // photograph; order-first moves the photograph above it on a phone,
          // where the face is what tells you you reached the right person. ?>
    <div class="flex flex-col justify-center gap-6 bg-brand-950 p-6 text-white sm:p-8 lg:p-12">
        <?php if ($number !== '' || $role !== '') : ?>
            <div class="flex items-center gap-4">
                <?php if ($number !== '') : ?>
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border-2 border-teal-400 text-2xl font-black leading-none text-teal-400">
                        <?php echo esc_html($number); ?>
                    </span>
                <?php endif; ?>

                <?php if ($role !== '') : ?>
                    <p class="text-[0.5625rem] font-bold uppercase leading-tight tracking-[0.2em] text-teal-400">
                        <?php echo esc_html($role); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h1 class="text-4xl font-black leading-[1.05] tracking-tight sm:text-5xl lg:text-6xl">
            <?php echo esc_html($name); ?>
        </h1>

        <?php if ($bio !== '') : ?>
            <p class="max-w-prose text-base leading-relaxed text-slate-200 sm:text-lg">
                <?php echo esc_html($bio); ?>
            </p>
        <?php endif; ?>

        <?php // Nothing filled in means no row at all, rather than a heading with
              // an empty space under it. ?>
        <?php if ($email !== '' || $facebook !== '') : ?>
            <div class="flex flex-wrap gap-3">
                <?php if ($email !== '') : ?>
                    <a
                        href="<?php echo esc_url('mailto:' . $email); ?>"
                        class="inline-flex items-center gap-2 rounded-md border border-white/40 px-4 py-2.5 text-sm font-bold transition hover:border-white hover:bg-white/10"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="20" height="16" x="2" y="4" rx="2" />
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                        </svg>
                        <?php echo esc_html($email); ?>
                    </a>
                <?php endif; ?>

                <?php if ($facebook !== '') : ?>
                    <a
                        href="<?php echo esc_url($facebook); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-md border border-white/40 px-4 py-2.5 text-sm font-bold transition hover:border-white hover:bg-white/10"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                        </svg>
                        <?php esc_html_e('Facebook', 'nexdigital'); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php // aspect-4/5 on a phone rather than the photo's own 3:4, which at full
          // width would be 133vw tall and push the name off the screen. ?>
    <div
        class="relative order-first aspect-4/5 overflow-hidden lg:order-none lg:aspect-auto lg:h-full"
        <?php echo $video !== null ? 'data-video-facade' : ''; ?>
    >
        <?php if (has_post_thumbnail($post_id)) : ?>
            <?php echo get_the_post_thumbnail($post_id, 'large', [
                'class'    => 'absolute inset-0 h-full w-full object-cover object-top',
                'alt'      => $name,
                'decoding' => 'async',
            ]); ?>
        <?php else : ?>
            <?php // Not every candidate has photographs delivered — Vladislav Obadal
                  // has none at all — so the letter keeps the column from collapsing. ?>
            <span class="absolute inset-0 flex items-center justify-center bg-sand-100 text-8xl font-black text-sand-300" aria-hidden="true">
                <?php echo esc_html(mb_substr($name, 0, 1)); ?>
            </span>
        <?php endif; ?>

        <?php get_template_part('template-parts/video-facade', null, [
            'video' => $video,
            'title' => sprintf(__('Video-vizitka — %s', 'nexdigital'), $name),
            'size'  => 'lg',
        ]); ?>
    </div>
</section>
```

- [ ] **Step 2: Commit**

Šablóna sa zatiaľ nikde nepoužíva, takže sa nedá overiť — overí sa v Tasku 6, keď ju `single-kandidat.php` zavolá.

```bash
composer lint
git add web/app/themes/nexdigital/template-parts/candidate-hero.php
git commit -m "feat(candidates): add the profile hero template part"
```

---

### Task 5: Voličské CTA a oprava inštrukcie poľa

Pás pod životopisom, ktorý opakuje ballot číslo — jedinú vec, ktorú si volič musí odniesť. Fotka v prostredí (`ts_foto_portret`) tu dostane svoju prácu; inštrukcia poľa, ktorá dnes sľubuje hlavičku profilu, sa prepíše na skutočné miesto použitia.

**Files:**
- Create: `web/app/themes/nexdigital/template-parts/candidate-cta.php`
- Modify: `web/app/themes/nexdigital/inc/fields/candidate.php:84-90`

**Interfaces:**
- Consumes: `Fields\field()`
- Produces: partial `template-parts/candidate-cta` s argumentmi `post_id` (int)

- [ ] **Step 1: Create the CTA part**

Vytvor `web/app/themes/nexdigital/template-parts/candidate-cta.php`:

```php
<?php
/**
 * Ballot call to action.
 *
 * The number is the one thing a voter has to carry into the booth, so it gets
 * the largest type on the page after the name. The location photograph sits
 * behind it under a heavy overlay — without one the band is simply flat, and
 * the layout does not change.
 *
 * The copy is hard-coded rather than fielded: this is chrome repeated across
 * twelve profiles, not content the client arranges — the same reasoning as the
 * footer.
 *
 * Expected args:
 *   post_id int Candidate post.
 *
 * @package NexDigital
 */

declare(strict_types=1);

use function NexDigital\Theme\Fields\field;

$post_id = (int) ($args['post_id'] ?? 0);

if ($post_id <= 0) {
    return;
}

$number = trim((string) (field('cislo', $post_id) ?? ''));
$photo = (int) (field('foto_portret', $post_id) ?? 0);

$archive = get_post_type_archive_link('kandidat');

// get_permalink(null) silently falls back to the global post, which inside the
// loop is this very candidate — so the page has to be checked before asking.
$programme_page = get_page_by_path('program');
$programme = $programme_page instanceof WP_Post ? get_permalink($programme_page) : '';
?>

<section class="relative isolate overflow-hidden bg-brand-950 py-16 text-white sm:py-20">
    <?php if ($photo > 0) : ?>
        <?php echo wp_get_attachment_image($photo, 'large', false, [
            'class'    => 'absolute inset-0 -z-10 h-full w-full object-cover',
            'alt'      => '',
            'loading'  => 'lazy',
            'decoding' => 'async',
        ]); ?>
        <?php // Heavy enough that teal on top keeps its contrast whatever the
              // photograph underneath happens to be. ?>
        <span class="absolute inset-0 -z-10 bg-brand-950/85" aria-hidden="true"></span>
    <?php endif; ?>

    <div class="site-container">
        <p class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-teal-400">
            <?php esc_html_e('Voľby do orgánov samosprávy obcí', 'nexdigital'); ?>
        </p>

        <?php if ($number !== '') : ?>
            <p class="mt-6 flex items-center gap-5">
                <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-teal-400 text-4xl font-black leading-none text-brand-950 sm:h-24 sm:w-24 sm:text-5xl">
                    <?php echo esc_html($number); ?>
                </span>
                <span class="text-3xl font-black leading-[1.05] tracking-tight sm:text-4xl">
                    <?php esc_html_e('Zakrúžkujte toto číslo', 'nexdigital'); ?>
                </span>
            </p>
        <?php endif; ?>

        <p class="mt-6 max-w-xl text-sm leading-relaxed text-slate-300 sm:text-base">
            <?php esc_html_e('Na hlasovacom lístku do mestského zastupiteľstva krúžkujete poradové čísla kandidátov. Voliť môžete len v obci svojho trvalého pobytu — hlasovací preukaz ani voľba poštou pri komunálnych voľbách neexistujú.', 'nexdigital'); ?>
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
            <?php if (is_string($archive) && $archive !== '') : ?>
                <a
                    href="<?php echo esc_url($archive); ?>"
                    class="inline-flex items-center justify-center rounded-md bg-white px-6 py-3.5 text-sm font-bold text-brand-950 transition hover:bg-teal-400"
                >
                    <?php esc_html_e('Všetci kandidáti', 'nexdigital'); ?>
                </a>
            <?php endif; ?>

            <?php if (is_string($programme) && $programme !== '') : ?>
                <a
                    href="<?php echo esc_url($programme); ?>"
                    class="inline-flex items-center justify-center rounded-md border border-white/40 px-6 py-3.5 text-sm font-bold text-white transition hover:border-white hover:bg-white/10"
                >
                    <?php esc_html_e('Náš program', 'nexdigital'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
```

- [ ] **Step 2: Correct the field instruction**

V `web/app/themes/nexdigital/inc/fields/candidate.php` nahraď inštrukciu poľa `field_ts_foto_portret` (dnes na riadku 86) tak, aby popisovala miesto, kde sa fotka naozaj použije:

```php
                'instructions' => __('Fotka z parku alebo mesta. Zobrazí sa na profile kandidáta ako pozadie výzvy „Zakrúžkujte toto číslo“ — je pod tmavým prekryvom, takže na nej záleží skôr atmosféra než detail. Bez nej ostane pás jednofarebný.', 'nexdigital'),
```

- [ ] **Step 3: Commit**

```bash
composer lint
git add web/app/themes/nexdigital/template-parts/candidate-cta.php web/app/themes/nexdigital/inc/fields/candidate.php
git commit -m "feat(candidates): add the ballot call to action"
```

---

### Task 6: `single-kandidat.php` — poskladanie

Orchestrátor. Žiadny markup okrem návratového odkazu a obalu životopisu.

**Files:**
- Create: `web/app/themes/nexdigital/single-kandidat.php`

**Interfaces:**
- Consumes: partialy `candidate-hero` (Task 4), `candidate-cta` (Task 5), trieda `.rich-text` (Task 3)

- [ ] **Step 1: Create the template**

Vytvor `web/app/themes/nexdigital/single-kandidat.php`:

```php
<?php
/**
 * Candidate profile.
 *
 * Orchestration only — the hero, the CV and the ballot call to action are
 * template parts. The back link is the single way out of a profile; there is
 * deliberately no roster strip and no previous/next pager, which would repeat
 * the grid the visitor just came from.
 *
 * @package NexDigital
 */

declare(strict_types=1);

get_header();

while (have_posts()) :
    the_post();

    $post_id = get_the_ID();
    $archive = get_post_type_archive_link('kandidat');
    ?>

    <?php if (is_string($archive) && $archive !== '') : ?>
        <nav class="bg-sand-50" aria-label="<?php esc_attr_e('Omrvinky', 'nexdigital'); ?>">
            <div class="site-container py-4">
                <a
                    href="<?php echo esc_url($archive); ?>"
                    class="group inline-flex items-center gap-2 text-sm font-bold text-slate-600 transition hover:text-brand-600"
                >
                    <svg class="h-4 w-4 transition group-hover:-translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5M11 18l-6-6 6-6" />
                    </svg>
                    <?php esc_html_e('Späť na kandidátov', 'nexdigital'); ?>
                </a>
            </div>
        </nav>
    <?php endif; ?>

    <article <?php post_class(); ?>>
        <?php get_template_part('template-parts/candidate-hero', null, ['post_id' => $post_id]); ?>

        <?php // An empty section with the heading "O kandidátovi" over nothing is
              // worse than no section, and eleven of the twelve profiles start
              // life with no CV written. ?>
        <?php if (trim(get_the_content()) !== '') : ?>
            <div class="site-container max-w-3xl py-14 sm:py-16">
                <h2 class="text-[0.5625rem] font-bold uppercase tracking-[0.2em] text-slate-500">
                    <?php esc_html_e('O kandidátovi', 'nexdigital'); ?>
                </h2>

                <div class="rich-text mt-6">
                    <?php the_content(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php get_template_part('template-parts/candidate-cta', null, ['post_id' => $post_id]); ?>
    </article>

    <?php
endwhile;

get_footer();
```

- [ ] **Step 2: Verify the leader's profile**

Server musí bežať (`php -S localhost:8000 -t web`).

```bash
curl -s http://localhost:8000/kandidati/mgr-peter-novisedlak-mba/ -o /tmp/lider.html -w '%{http_code}\n'
grep -o 'Mgr. Peter Novisedlák, MBA' /tmp/lider.html | wc -l
grep -o 'data-video-facade' /tmp/lider.html | wc -l
grep -o 'Zakrúžkujte toto číslo' /tmp/lider.html | wc -l
grep -o 'Späť na kandidátov' /tmp/lider.html | wc -l
```

Expected: `200`, potom aspoň `2` (meno je v `<title>` aj v `<h1>`), a `1`, `1`, `1`.

`grep -o | wc -l` počíta výskyty; samotné `grep -c` počíta riadky, čo pri minifikovanom alebo naopak rozlámanom HTML klame.

- [ ] **Step 3: Verify the candidate with no photograph does not break**

```bash
curl -s http://localhost:8000/kandidati/vladislav-obadal/ -o /tmp/obadal.html -w '%{http_code}\n'
grep -c 'data-video-facade' /tmp/obadal.html
grep -c 'text-8xl font-black text-sand-300' /tmp/obadal.html
grep -ci 'fatal error\|Warning:\|Notice:' /tmp/obadal.html
```

Expected: `200`, `0` (nemá video, takže žiadna facade), `1` (písmenkový fallback), `0` (žiadne PHP hlásenia).

- [ ] **Step 4: Verify a profile with neither video nor contacts**

```bash
curl -s http://localhost:8000/kandidati/ladislav-hajdu/ -o /tmp/hajdu.html -w '%{http_code}\n'
grep -c 'mailto:' /tmp/hajdu.html
grep -c 'Prehrať video-vizitku' /tmp/hajdu.html
grep -c 'O kandidátovi' /tmp/hajdu.html
```

Expected: `200`, `0`, `0`, `0` — kontaktný riadok, facade aj sekcia životopisu sú zatiaľ prázdne polia, takže sa nemajú vykresliť vôbec.

- [ ] **Step 5: Verify the theme survives without SCF**

Degradácia je overený, nie predpokladaný záväzok tejto témy — profil musí prežiť deaktiváciu pluginu.

```bash
wp --path=web/wp plugin deactivate secure-custom-fields
curl -s http://localhost:8000/kandidati/mgr-peter-novisedlak-mba/ -o /tmp/bez-scf.html -w '%{http_code}\n'
grep -oi 'fatal error' /tmp/bez-scf.html | wc -l
grep -o 'Mgr. Peter Novisedlák, MBA' /tmp/bez-scf.html | wc -l
wp --path=web/wp plugin activate secure-custom-fields
```

Expected: `200`, `0`, aspoň `2` — meno a fotka prežijú na `get_post_meta()` fallbacku v `Fields\field()`, zmizne len to, čo poskytuje SCF.

Plugin sa musí znovu aktivovať aj vtedy, keď `curl` zlyhá — ak sa kroky prerušia, spusti `wp --path=web/wp plugin activate secure-custom-fields` ručne, inak zostane zvyšok práce bez polí.

- [ ] **Step 6: Commit**

```bash
composer lint
git add web/app/themes/nexdigital/single-kandidat.php
git commit -m "feat(candidates): add the candidate profile template"
```

---

### Task 7: Fotografie v prostredí

Jedenásť kandidátov má v dodaných priečinkoch aspoň jednu vodorovnú fotku z prostredia. Výber musí kontrolovať orientáciu, nie brať prvý súbor — Adela Urbánová má ako `foto-32.jpg` portrét 4000×6000 a orez na 3:2 by z neho spravil pás bez hlavy.

**Files:**
- Create: `<scratchpad>/foto-portret.sh` (mimo repozitára — jednorazová operácia nad lokálnou databázou)

**Interfaces:**
- Consumes: pole `ts_foto_portret` z Tasku 5, príspevky ID 35–46

- [ ] **Step 1: Write the conversion and import script**

Vytvor `/private/tmp/claude-501/-Users-andrejkarkus-www-tim-stupava/1ad2742f-1a16-4e86-99f1-1ff3256e0120/scratchpad/foto-portret.sh`:

```bash
#!/usr/bin/env bash
# Import each candidate's location photograph as ts_foto_portret.
#
# Sources are ~6000x4000 (already 3:2), but not all of them — one candidate's
# first file is a portrait, so the landscape check is not optional.
set -euo pipefail

ROOT="/Users/andrejkarkus/www/tim-stupava"
SRC="$ROOT/web/app/themes/nexdigital/resources/sources/drive-download"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

# post_id:folder — Vladislav Obadal (ID 41) is absent on purpose: zero files delivered.
MAP="35:1. Ladislav Hajdu
36:2. Štefan Haulík
37:3. Peter Novisedlák
38:4. Sofia Piatnica
39:5. Adela Urbánová
40:6. Ľubomír Bugala
42:8. Patrik Kollaroci
43:9. Marek Lacka
44:10. Matej Piatnica
45:11. Juraj Vachálek
46:12. Václav Měšťan"

while IFS=: read -r id folder; do
    [ -n "$id" ] || continue

    pick=""
    for f in "$SRC/$folder"/foto-*.jpg; do
        [ -e "$f" ] || continue
        w=$(sips -g pixelWidth "$f" | awk '/pixelWidth/{print $2}')
        h=$(sips -g pixelHeight "$f" | awk '/pixelHeight/{print $2}')
        if [ "$w" -gt "$h" ]; then pick="$f"; break; fi
    done

    if [ -z "$pick" ]; then
        echo "PRESKOČENÉ $id ($folder) — žiadna vodorovná fotka"
        continue
    fi

    w=$(sips -g pixelWidth "$pick" | awk '/pixelWidth/{print $2}')
    h=$(sips -g pixelHeight "$pick" | awk '/pixelHeight/{print $2}')

    # Centre-crop to exactly 3:2 before resizing, so a 5915x3943 source is not
    # squashed a pixel. sips -c takes HEIGHT then WIDTH.
    if [ $((w * 2)) -gt $((h * 3)) ]; then
        cw=$((h * 3 / 2)); ch=$h
    else
        cw=$w; ch=$((w * 2 / 3))
    fi

    slug=$(wp --path="$ROOT/web/wp" post get "$id" --field=post_name)
    out="$TMP/$slug-v-prostredi.webp"

    sips -c "$ch" "$cw" "$pick" --out "$TMP/crop.jpg" >/dev/null
    cwebp -quiet -q 82 -resize 1800 1200 "$TMP/crop.jpg" -o "$out"

    title=$(wp --path="$ROOT/web/wp" post get "$id" --field=post_title)
    att=$(wp --path="$ROOT/web/wp" media import "$out" \
            --title="$title — v prostredí" \
            --alt="$title" \
            --porcelain)

    # update_field(), not post meta: SCF also stores the field key in _ts_foto_portret,
    # and without it the admin screen shows the field as empty.
    wp --path="$ROOT/web/wp" eval "update_field('ts_foto_portret', $att, $id);"

    echo "OK $id $title → $(basename "$pick") → príloha $att"
done <<< "$MAP"
```

- [ ] **Step 2: Run it**

```bash
chmod +x <scratchpad>/foto-portret.sh && <scratchpad>/foto-portret.sh
```

Expected: jedenásť riadkov `OK`. Adela Urbánová (ID 39) musí ukazovať `foto-33.jpg`, nie `foto-32.jpg`.

- [ ] **Step 3: Verify the imports**

```bash
cd /Users/andrejkarkus/www/tim-stupava
wp --path=web/wp eval '
foreach (range(35, 46) as $id) {
    $att = (int) get_post_meta($id, "ts_foto_portret", true);
    $key = get_post_meta($id, "_ts_foto_portret", true);
    $m = $att ? wp_get_attachment_metadata($att) : null;
    printf("%d %-28s %s %s %s\n", $id, get_the_title($id), $att ?: "-",
        $m ? $m["width"] . "x" . $m["height"] : "-", $key ?: "-");
}'
```

Expected: jedenásť riadkov s ID prílohy, rozmerom `1800x1200` a kľúčom `field_ts_foto_portret`; ID 41 (Obadal) má vo všetkých troch stĺpcoch `-`.

- [ ] **Step 4: Verify the CTA now shows the photograph**

```bash
curl -s http://localhost:8000/kandidati/adela-urbanova/ | grep -c 'bg-brand-950/85'
curl -s http://localhost:8000/kandidati/vladislav-obadal/ | grep -c 'bg-brand-950/85'
```

Expected: `1` a `0` — prekryv sa vykreslí len tam, kde je pod ním fotka.

Skript ani prevedené súbory sa necommitujú — zmenila sa len databáza a knižnica médií.

---

### Task 8: Texty profilov

Jedenásť z dvanástich kandidátov sú reálne, menom uvedené osoby, o ktorých nemáme životopisné údaje. Vymyslený životopis skutočného človeka na kampaňovom webe nie je otázka štýlu, ale tvrdenie, ktoré si niekto overí — placeholder preto nesie viditeľnú značku priamo v texte a zámerne nehovorí o práci, škole ani rodine.

**Files:**
- Create: `<scratchpad>/texty-kandidatov.php` (mimo repozitára)

**Interfaces:**
- Consumes: príspevky ID 35–46; polia `ts_kratke_bio`, `post_content`, meta `_ts_placeholder`

- [ ] **Step 1: Write the content script**

Vytvor `<scratchpad>/texty-kandidatov.php`:

```php
<?php
/**
 * Fill in candidate copy.
 *
 * Peter Novisedlák gets real text: he is the sitting mayor and the facts come
 * from the programme document. The other eleven get placeholder copy that says
 * so in its own first two words, because a fabricated biography of a named real
 * person is a claim somebody will check — not a styling detail. The placeholder
 * text deliberately mentions no job, school or family.
 *
 * Run: wp --path=web/wp eval-file <scratchpad>/texty-kandidatov.php
 */

declare(strict_types=1);

const MARKER = '[UKÁŽKOVÝ TEXT]';

$lider_bio = 'Primátor Stupavy od roku 2018. Za dve obdobia doviedol mesto k prestupnému terminálu s právoplatným územným rozhodnutím a k financovaniu z eurofondov.';

$lider_cv = <<<HTML
<p>Peter Novisedlák vedie Stupavu od roku 2018. Za dve volebné obdobia sa mesto posunulo od zámerov na papieri k projektom s právoplatnými povoleniami a zabezpečeným financovaním.</p>

<h2>Čo sa podarilo</h2>

<p>Najväčším projektom obdobia je <strong>prestupný terminál na Námestí SNP</strong> s rozpočtom 5 089 423,05 € z fondov Európskej únie a štátneho rozpočtu. Má právoplatné územné rozhodnutie a prináša 77 parkovacích miest v štyroch zónach, prístrešky pre 30 bicyklov, jedenásť osvetlených priechodov a rekonštrukciu ciest I/2 a III/1106. Práce sú naplánované na rok 2027.</p>

<p>Pripravené sú aj ďalšie celomestské projekty — nadstavba zdravotného strediska na centrum integrovanej zdravotnej starostlivosti, športovisko na Železničnej s rekonštrukciou budovy bývalej stanice, a turbo-okružná križovatka na ceste I/2, ktorá je predpokladom pre budúci obchvat mesta.</p>

<h2>Čo chce dokončiť</h2>

<p>Cieľom na nasledujúce obdobie je dostať pripravené projekty do realizácie a pokračovať v tom, čo mesto potrebuje každý deň: rozvody pitnej vody, cesty a chodníky, cyklotrasy na Lozorno a Borinku, novú telocvičňu v areáli základnej školy a obnovu Troyerovej kúrie.</p>
HTML;

$placeholder_bio = MARKER . ' Krátky popis kandidáta sa zobrazuje na karte v prehľade a na profile pod menom. Nahraďte ho jednou až dvomi vetami o tom, čomu sa kandidát v Stupave venuje.';

$placeholder_cv = <<<HTML
<p><strong>{MARKER}</strong> Tento životopis je ukážkový a treba ho prepísať. Slúži len na to, aby bolo vidieť, ako profil vyzerá s obsahom.</p>

<h2>Čo sem patrí</h2>

<p>Niekoľko odsekov o tom, čo kandidáta so Stupavou spája, čomu sa venuje a prečo kandiduje. Text píše kandidát alebo volebný tím — na webe sa zobrazí presne tak, ako ho sem napíšete.</p>

<h2>Priority</h2>

<ul>
<li>Prvá téma, ktorej sa kandidát chce v zastupiteľstve venovať</li>
<li>Druhá téma</li>
<li>Tretia téma</li>
</ul>
HTML;

$placeholder_cv = str_replace('{MARKER}', MARKER, $placeholder_cv);

$leader = 37;

foreach (range(35, 46) as $id) {
    if (get_post_type($id) !== 'kandidat') {
        printf("PRESKOČENÉ %d — nie je kandidát\n", $id);
        continue;
    }

    $is_leader = $id === $leader;

    wp_update_post([
        'ID'           => $id,
        'post_content' => $is_leader ? $lider_cv : $placeholder_cv,
    ]);

    update_field('ts_kratke_bio', $is_leader ? $lider_bio : $placeholder_bio, $id);

    if ($is_leader) {
        delete_post_meta($id, '_ts_placeholder');
    } else {
        // One `wp post list --meta_key=_ts_placeholder` before launch shows
        // everything the client has not rewritten yet.
        update_post_meta($id, '_ts_placeholder', '1');
    }

    printf("OK %d %s%s\n", $id, get_the_title($id), $is_leader ? ' (skutočný text)' : ' (ukážkový text)');
}
```

- [ ] **Step 2: Run it**

```bash
cd /Users/andrejkarkus/www/tim-stupava
wp --path=web/wp eval-file <scratchpad>/texty-kandidatov.php
```

Expected: dvanásť riadkov `OK`, z toho ID 37 označené `(skutočný text)`.

- [ ] **Step 3: Verify what still needs rewriting**

```bash
wp --path=web/wp post list --post_type=kandidat --meta_key=_ts_placeholder --fields=ID,post_title --format=csv
```

Expected: jedenásť riadkov, bez Novisedláka. Toto je zoznam, ktorý sa pred spustením webu musí vyprázdniť.

- [ ] **Step 4: Verify the profiles render the copy**

```bash
curl -s http://localhost:8000/kandidati/mgr-peter-novisedlak-mba/ -o /tmp/lider.html
grep -o 'O kandidátovi' /tmp/lider.html | wc -l
grep -o 'prestupný terminál' /tmp/lider.html | wc -l
grep -o 'UKÁŽKOVÝ TEXT' /tmp/lider.html | wc -l

curl -s http://localhost:8000/kandidati/ladislav-hajdu/ | grep -o 'UKÁŽKOVÝ TEXT' | wc -l
```

Expected: `1`, `1`, `0` u lídra — a `2` u Hajdu (značka je v krátkom bio v hero paneli aj v životopise), čo je zámer: nepublikovateľný text musí byť vidieť na obrazovke, nielen v administrácii.

- [ ] **Step 5: Note the demo video for removal**

`ts_video` na ID 37 obsahuje odkaz na Big Buck Bunny (`aqz-KE-bpKQ`). Nechá sa tam, aby bolo facade na čom testovať, ale patrí k tej istej skupine ukážkových dát ako seedovaný IBAN a logá partnerov. Over, že je stále v evidencii:

```bash
wp --path=web/wp post meta get 37 ts_video
```

Expected: `https://www.youtube.com/watch?v=aqz-KE-bpKQ`

- [ ] **Step 6: Final full check across all twelve profiles**

```bash
cd /Users/andrejkarkus/www/tim-stupava
for slug in $(wp --path=web/wp post list --post_type=kandidat --field=post_name); do
    code=$(curl -s -o /tmp/k.html -w '%{http_code}' "http://localhost:8000/kandidati/$slug/")
    err=$(grep -ci 'fatal error\|Warning:\|Notice:' /tmp/k.html || true)
    echo "$code errors:$err $slug"
done
```

Expected: dvanásť riadkov `200 errors:0`.

Skripty sa necommitujú — zmenila sa len databáza.

---

## Self-Review

**Spec coverage:**

| požiadavka zo spec | task |
|---|---|
| `Video\source()` a odstránenie duplicity | 1 |
| `template-parts/video-facade.php` + refactor karty | 2 |
| `.rich-text` a oprava mŕtveho `prose` | 3 |
| split hero, kontakty, mobilné poradie, písmenkový fallback | 4 |
| CTA s fotkou v pozadí + oprava inštrukcie poľa | 5 |
| `single-kandidat.php`, návratový odkaz, telo | 6 |
| fotky v prostredí vrátane kontroly orientácie | 7 |
| texty, značka placeholderu, `_ts_placeholder`, žiadne vymyslené kontakty | 8 |
| overenie 1–7 zo spec | 6 (kroky 2–5), 7 (krok 4), 8 (kroky 4, 6) |
| demo video na odstránenie pred spustením | 8 krok 5 |

**Type consistency:** `resolve()` a `source()` vracajú `array{url: string, file: bool}|null` v Tasku 1; partial v Tasku 2 aj hero v Tasku 4 čítajú presne `$video['url']` a `$video['file']`. Argument `size` má hodnoty `'sm'` / `'lg'` v Tasku 2 aj na oboch volaniach. `post_id` je názov argumentu vo všetkých troch partialoch.

**Zámerne bez automatizovaného testu:** šablóny. Pest v tomto projekte nemá WordPress bootstrap, takže by sa dali testovať len po pridaní celej testovacej infraštruktúry pre WordPress — to je samostatná úloha, nie súčasť tejto. Overenie šablón preto beží cez HTTP proti lokálnemu serveru a je v každom kroku napísané s očakávaným výstupom.
