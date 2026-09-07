<?php
// Snazzy Gallery — a single-file, zero-dependency media gallery.
// Drop this file into any web directory of images/videos and open it in a browser.
// Requires PHP 7.0+.

$allowed_types = [
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'],
    'video' => ['mp4', 'webm', 'mov']
];
$mime = [
    'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
    'gif' => 'image/gif',  'webp' => 'image/webp', 'avif' => 'image/avif',
    'svg' => 'image/svg+xml',
    'mp4' => 'video/mp4',  'webm' => 'video/webm', 'mov'  => 'video/quicktime',
];
$ignore  = ['thumbs.db', '.ds_store', 'desktop.ini'];
$self    = basename(__FILE__);
$media   = [];
$skipped = [];   // extension => count

// getimagesize() only reads the file header, but skip it for very large
// directories so the page still renders quickly.
$scan_dimensions = true;

$entries = [];
foreach (new DirectoryIterator(__DIR__) as $file) {
    if (!$file->isFile()) continue;
    $name     = $file->getFilename();
    $basename = strtolower($name);
    if ($name === $self) continue;
    if ($basename === '' || $basename[0] === '.' || strpos($basename, 'favicon.') === 0 || in_array($basename, $ignore, true)) continue;
    $entries[$name] = $file->getPathname();
}
if (count($entries) > 400) $scan_dimensions = false;

foreach ($entries as $name => $path) {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (in_array($ext, $allowed_types['image'], true)) {
        $item = ['type' => 'image', 'src' => $name, 'url' => rawurlencode($name), 'mime' => $mime[$ext]];
        if ($scan_dimensions && $ext !== 'svg') {
            $size = @getimagesize($path);
            if ($size && $size[0] > 0 && $size[1] > 0) {
                $item['w'] = $size[0];
                $item['h'] = $size[1];
            }
        }
        $media[] = $item;
    } elseif (in_array($ext, $allowed_types['video'], true)) {
        $media[] = ['type' => 'video', 'src' => $name, 'url' => rawurlencode($name), 'mime' => $mime[$ext]];
    } elseif ($ext !== 'php' && $ext !== 'md' && $ext !== '') {
        // Record the format only, never the filename — the gallery is often
        // served from a directory that also holds files the visitor shouldn't enumerate.
        $skipped[$ext] = (isset($skipped[$ext]) ? $skipped[$ext] : 0) + 1;
    }
}

// Regular closures — compatible with PHP 7.0+ (no short arrow fn syntax).
usort($media, function ($a, $b) { return strnatcasecmp($a['src'], $b['src']); });
ksort($skipped, SORT_NATURAL | SORT_FLAG_CASE);
$skipped_total = array_sum($skipped);

function h($s) { return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$nonce = base64_encode(random_bytes(16));

// Inline favicon so the gallery stays a single file: a small tiled-grid mark.
$favicon = 'data:image/svg+xml,' . rawurlencode(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">'
    . '<rect width="32" height="32" rx="6" fill="#0f0f0f"/>'
    . '<rect x="6" y="6" width="9" height="9" rx="2" fill="#ededed"/>'
    . '<rect x="17" y="6" width="9" height="9" rx="2" fill="#8a8a8a"/>'
    . '<rect x="6" y="17" width="9" height="9" rx="2" fill="#8a8a8a"/>'
    . '<rect x="17" y="17" width="9" height="9" rx="2" fill="#ededed"/>'
    . '</svg>'
);

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
// The listing changes whenever files are added or removed — always revalidate.
header('Cache-Control: no-cache');
header(
    "Content-Security-Policy: default-src 'none'; img-src 'self' data:; media-src 'self'; "
    . "style-src 'nonce-$nonce'; script-src 'nonce-$nonce'; "
    . "base-uri 'none'; form-action 'none'; frame-ancestors 'none'"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= h(basename(__DIR__)) ?></title>
    <link rel="icon" href="<?= h($favicon) ?>">
    <style nonce="<?= $nonce ?>">
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 12px;
            background: #0f0f0f;
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            min-height: 100dvh;
        }
        .gallery {
            columns: 2 140px;
            column-gap: 12px;
            padding: 12px;
        }
        @media (min-width: 600px)  { .gallery { columns: 3 150px; } }
        @media (min-width: 900px)  { .gallery { columns: 4 160px; } }
        @media (min-width: 1200px) { .gallery { columns: 6 180px; } }
        .gallery-item {
            position: relative;
            display: block;
            width: 100%;
            padding: 0;
            border: none;
            font: inherit;
            color: inherit;
            -webkit-appearance: none;
            appearance: none;
            background: #1a1a1a;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.5);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
            margin-bottom: 12px;
            break-inside: avoid;
        }
        .gallery-item:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 24px rgba(0,0,0,0.7);
        }
        .gallery-item:focus-visible {
            outline: 2px solid #6ab7ff;
            outline-offset: 2px;
        }
        .gallery-item img,
        .gallery-item video {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
            background: #111;
            pointer-events: none;
        }
        /* Fallback height so video cards aren't zero-height on iOS before metadata loads */
        .gallery-item video { min-height: 120px; }
        .play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .play-icon {
            width: 52px;
            height: 52px;
            background: rgba(0,0,0,0.55);
            border: 2px solid rgba(255,255,255,0.55);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            padding-left: 3px;
            backdrop-filter: blur(4px);
            transition: transform 0.2s, background 0.2s, border-color 0.2s;
        }
        .gallery-item:hover .play-icon {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.9);
            transform: scale(1.1);
        }
        .empty {
            text-align: center;
            color: #444;
            padding: 80px 20px;
        }
        .empty p { margin: 6px 0; font-size: 16px; }
        .empty .hint { font-size: 13px; color: #333; margin-top: 12px; }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.93);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .lightbox.open { display: flex; }
        .lb-img,
        .lb-vid {
            /* Subtract nav button footprint so media never slides under arrows */
            max-width: calc(100vw - 120px);
            max-width: calc(100dvw - 120px);
            max-height: 88vh;
            max-height: 88dvh;
            object-fit: contain;
            display: none;
            border-radius: 3px;
            user-select: none;
        }
        .lb-vid { background: #000; }
        .lb-img.show,
        .lb-vid.show { display: block; }
        .lb-btn {
            position: fixed;
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            cursor: pointer;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s;
            font-size: 24px;
            line-height: 1;
        }
        .lb-btn:hover { background: rgba(255,255,255,0.22); }
        .lb-btn:focus-visible { outline: 2px solid #6ab7ff; }
        .lb-close { top: 14px; right: 14px; font-size: 20px; }
        .lb-prev  { left: 14px;  top: 50%; transform: translateY(-50%); font-size: 30px; padding-right: 2px; }
        .lb-next  { right: 14px; top: 50%; transform: translateY(-50%); font-size: 30px; padding-left:  2px; }
        .lb-prev.hide, .lb-next.hide { display: none; }
        .lb-counter {
            position: fixed;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255,255,255,0.4);
            font-size: 13px;
            pointer-events: none;
            letter-spacing: 0.5px;
        }
        .lb-hint {
            position: fixed;
            bottom: 14px;
            right: 16px;
            color: rgba(255,255,255,0.2);
            font-size: 11px;
            pointer-events: none;
            letter-spacing: 0.2px;
        }

        /* Skipped */
        .skipped-wrap { width: 100%; text-align: center; margin-top: 32px; }
        .skipped-btn {
            background: none;
            color: #444;
            border: none;
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 12px;
            cursor: pointer;
            transition: color 0.2s, background 0.2s;
        }
        .skipped-btn:hover { color: #888; background: rgba(255,255,255,0.06); }
        .skipped-btn:focus-visible { outline: 2px solid #6ab7ff; }
        .skipped-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 1100;
        }
        .skipped-backdrop.open { display: block; }
        .skipped-modal {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #1c1c1c;
            color: #bbb;
            border-radius: 10px;
            padding: 20px 24px 20px 20px;
            min-width: 240px;
            max-width: 88vw;
            max-height: 55vh;
            overflow-y: auto;
            box-shadow: 0 8px 40px rgba(0,0,0,0.6);
        }
        .skipped-modal h4 { margin: 0 0 12px; padding-right: 18px; font-size: 14px; color: #eee; font-weight: 500; }
        .skipped-modal ul {
            margin: 0;
            padding-left: 16px;
            font-size: 12px;
            line-height: 2;
            word-break: break-all;
        }
        .skipped-modal .x {
            position: absolute;
            top: 10px; right: 12px;
            background: none; border: none;
            font-size: 18px; color: #555; cursor: pointer;
        }
        .skipped-modal .x:hover { color: #ccc; }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: 0.01ms !important; }
            .gallery-item:hover,
            .gallery-item:hover .play-icon { transform: none; }
        }
    </style>
</head>
<body>
<main>
<?php if (empty($media)): ?>
    <div class="empty">
        <p>No media found in this directory.</p>
        <p class="hint">Supported: jpg &middot; png &middot; gif &middot; webp &middot; avif &middot; svg &middot; mp4 &middot; webm &middot; mov</p>
    </div>
<?php else: ?>
    <div class="gallery">
        <?php foreach ($media as $idx => $item): ?>
        <button type="button" class="gallery-item" data-idx="<?= $idx ?>"
                aria-label="Open <?= h(pathinfo($item['src'], PATHINFO_FILENAME)) ?>">
            <?php if ($item['type'] === 'image'): ?>
                <img loading="lazy" decoding="async" src="<?= $item['url'] ?>"<?php
                    if (isset($item['w'])) echo ' width="' . $item['w'] . '" height="' . $item['h'] . '"';
                ?> alt="<?= h(pathinfo($item['src'], PATHINFO_FILENAME)) ?>">
            <?php else: ?>
                <video preload="metadata" muted playsinline>
                    <source src="<?= $item['url'] ?>#t=0.1" type="<?= h($item['mime']) ?>">
                </video>
                <span class="play-overlay"><span class="play-icon">&#9654;</span></span>
            <?php endif; ?>
        </button>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</main>

<?php if ($skipped_total > 0): ?>
    <div class="skipped-wrap">
        <button type="button" class="skipped-btn" id="skipped-open">
            <?= $skipped_total ?> file<?= $skipped_total !== 1 ? 's' : '' ?> skipped
        </button>
    </div>
    <div class="skipped-backdrop" id="skipped-backdrop">
        <div class="skipped-modal" role="dialog" aria-modal="true" aria-label="Skipped files">
            <button type="button" class="x" id="skipped-close" aria-label="Close">&times;</button>
            <h4>Skipped (unsupported format)</h4>
            <ul>
                <?php foreach ($skipped as $ext => $count): ?>
                    <li><?= h($ext) ?><?= $count > 1 ? ' &times; ' . $count : '' ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<div class="lightbox" id="lb" role="dialog" aria-modal="true" aria-label="Media viewer">
    <button type="button" class="lb-btn lb-close" data-act="close" aria-label="Close">&times;</button>
    <button type="button" class="lb-btn lb-prev" data-act="prev" aria-label="Previous">&#8249;</button>
    <img class="lb-img" src="data:," alt="">
    <video class="lb-vid" controls playsinline preload="none"></video>
    <button type="button" class="lb-btn lb-next" data-act="next" aria-label="Next">&#8250;</button>
    <span class="lb-counter" aria-live="polite"></span>
    <span class="lb-hint">&#8592; &#8594; &nbsp;&nbsp; esc</span>
</div>

<script nonce="<?= $nonce ?>">
(function () {
    "use strict";
    var media  = <?= json_encode($media, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    if (!Array.isArray(media)) media = [];
    var cur      = -1;
    var lb       = document.getElementById('lb');
    var img      = lb.querySelector('.lb-img');
    var vid      = lb.querySelector('.lb-vid');
    var counter  = lb.querySelector('.lb-counter');
    var swiped   = false;
    var lastFocused = null;

    function preload(i) {
        var m = media[i];
        if (m && m.type === 'image') { var p = new Image(); p.src = m.url; }
    }

    // Reflect the open item in the URL fragment (#p3) so links are shareable and
    // survive a refresh. replaceState never fires hashchange, so there is no loop.
    function syncHash() {
        var want = cur >= 0 ? '#p' + (cur + 1) : location.pathname + location.search;
        try {
            if (history.replaceState) history.replaceState(null, '', want);
            else if (cur >= 0) location.hash = 'p' + (cur + 1);
        } catch (e) { /* file:// or blocked — deep links just won't update */ }
    }

    function openLb(i) {
        if (i < 0 || i >= media.length) return;
        if (!lb.classList.contains('open')) lastFocused = document.activeElement;
        cur = i;
        render();
        lb.classList.add('open');
        document.body.style.overflow = 'hidden';
        lb.querySelector('.lb-close').focus();
    }

    function closeLb() {
        lb.classList.remove('open');
        document.body.style.overflow = '';
        vid.pause();
        vid.removeAttribute('src');
        vid.load();
        img.removeAttribute('src');
        cur = -1;
        syncHash();
        if (lastFocused && lastFocused.focus) lastFocused.focus();
    }

    function go(i) {
        cur = (i + media.length) % media.length;
        render();
    }

    function nav(d) {
        if (media.length < 2) return;
        go(cur + d);
    }

    function render() {
        var item = media[cur];
        var n    = media.length;
        vid.pause();
        if (item.type === 'image') {
            img.src = item.url;
            img.alt = item.src.replace(/\.[^.]+$/, '');
            img.classList.add('show');
            vid.classList.remove('show');
            vid.removeAttribute('src');
            vid.load();
        } else {
            vid.src = item.url;
            vid.load();
            vid.classList.add('show');
            img.classList.remove('show');
            img.removeAttribute('src');
        }
        counter.textContent = n > 1 ? (cur + 1) + ' / ' + n : '';
        lb.querySelector('.lb-prev').classList.toggle('hide', n <= 1);
        lb.querySelector('.lb-next').classList.toggle('hide', n <= 1);
        preload((cur + 1) % n);
        preload((cur - 1 + n) % n);
        syncHash();
    }

    // --- Grid ---
    var gallery = document.querySelector('.gallery');
    if (gallery) {
        gallery.addEventListener('click', function (e) {
            var btn = e.target.closest('.gallery-item');
            if (btn) openLb(+btn.getAttribute('data-idx'));
        });
    }

    // --- Lightbox controls ---
    lb.addEventListener('click', function (e) {
        if (swiped) { swiped = false; return; }
        var act = e.target.closest('[data-act]');
        if (act) {
            if (act.dataset.act === 'close') closeLb();
            else if (act.dataset.act === 'prev') nav(-1);
            else if (act.dataset.act === 'next') nav(1);
            return;
        }
        if (e.target === lb) closeLb();
    });

    document.addEventListener('keydown', function (e) {
        if (!lb.classList.contains('open')) return;
        // Let the video element keep native ArrowLeft/Right seeking when focused.
        var onVideo = e.target && e.target.tagName === 'VIDEO';
        if (e.key === 'Escape')            { closeLb(); }
        else if (e.key === 'Tab')          { trapFocus(e); }
        else if (onVideo)                  { return; }
        else if (e.key === 'ArrowRight')   { nav(1); }
        else if (e.key === 'ArrowLeft')    { nav(-1); }
        else if (e.key === 'Home')         { go(0); }
        else if (e.key === 'End')          { go(media.length - 1); }
    });

    function trapFocus(e) {
        // querySelectorAll is in DOM order (close, prev, video, next); the nav
        // buttons carry .hide when there's only one item. offsetParent can't be
        // used here — the buttons are position:fixed, so it is always null.
        var f = Array.prototype.filter.call(
            lb.querySelectorAll('.lb-close, .lb-prev, .lb-vid.show, .lb-next'),
            function (el) { return !el.classList.contains('hide'); }
        );
        if (!f.length) return;
        var first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
        else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
    }

    // --- Touch / swipe ---
    var tx = null;
    lb.addEventListener('touchstart', function (e) {
        if (e.target && e.target.tagName === 'VIDEO') { tx = null; return; }
        if (e.touches.length === 1) tx = e.touches[0].clientX;
    }, { passive: true });
    lb.addEventListener('touchend', function (e) {
        if (tx !== null && e.changedTouches.length === 1) {
            var dx = e.changedTouches[0].clientX - tx;
            if (Math.abs(dx) > 50 && media.length > 1) {
                nav(dx < 0 ? 1 : -1);
                swiped = true;
            }
            tx = null;
        }
    });

    // --- Deep links (#p3) ---
    function hashIndex() {
        var m = /^#p(\d+)$/.exec(location.hash);
        if (!m) return -1;
        var i = parseInt(m[1], 10) - 1;
        return (i >= 0 && i < media.length) ? i : -1;
    }
    window.addEventListener('hashchange', function () {
        var i = hashIndex();
        if (i >= 0) {
            if (!lb.classList.contains('open')) openLb(i);
            else if (i !== cur) go(i);
        } else if (lb.classList.contains('open')) {
            closeLb();
        }
    });
    (function () { var i = hashIndex(); if (i >= 0) openLb(i); })();

    // --- Skipped-files panel ---
    var sOpen = document.getElementById('skipped-open');
    if (sOpen) {
        var backdrop = document.getElementById('skipped-backdrop');
        sOpen.addEventListener('click', function () { backdrop.classList.add('open'); });
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop || e.target.id === 'skipped-close') backdrop.classList.remove('open');
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') backdrop.classList.remove('open');
        });
    }
})();
</script>
</body>
</html>
