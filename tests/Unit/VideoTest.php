<?php

declare(strict_types=1);

use function NexDigital\Theme\Video\resolve;

beforeAll(function () {
    // inc/video.php exits when ABSPATH is undefined — that is its guard against
    // direct HTTP access. Here we only need to get past it.
    if (!defined('ABSPATH')) {
        define('ABSPATH', __DIR__);
    }

    require_once __DIR__ . '/../../web/app/themes/nexdigital/inc/video.php';
});

test('nahraný súbor vyhráva nad odkazom na YouTube', function () {
    $result = resolve(
        ['url' => 'https://example.test/app/uploads/vizitka.mp4'],
        'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
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
