<?php
$allowed_types = [
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'],
    'video' => ['mp4', 'webm', 'ogg', 'mov']
];
$ignore = ['thumbs.db', '.ds_store', 'desktop.ini'];
$media = [];
$skipped = [];

foreach (new DirectoryIterator('.') as $file) {
    if (!$file->isFile()) continue;
    $basename = strtolower($file->getFilename());
    if ($basename[0] === '.' || strpos($basename, 'favicon') === 0 || in_array($basename, $ignore)) continue;
    $ext = strtolower($file->getExtension());
    if (in_array($ext, $allowed_types['image'])) {
        $media[] = ['type' => 'image', 'src' => $file->getFilename()];
    } elseif (in_array($ext, $allowed_types['video'])) {
        $media[] = ['type' => 'video', 'src' => $file->getFilename()];
    } elseif ($ext !== 'php' && $ext !== 'md' && $ext !== '') {
        $skipped[] = ['name' => $file->getFilename(), 'ext' => $ext];
    }
}

usort($media, fn($a, $b) => strnatcasecmp($a['src'], $b['src']));
usort($skipped, fn($a, $b) => strnatcasecmp($a['name'], $b['name']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(basename(getcwd())) ?></title>
    <link rel="icon" href="favicon.png">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 12px;
            background: #0f0f0f;
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        .gallery {
            columns: 2 140px;
            column-gap: 12px;
            padding: 12px;
        }
        @media (min-width: 600px) { .gallery { columns: 3 150px; } }
        @media (min-width: 900px) { .gallery { columns: 4 160px; } }
        @media (min-width: 1200px) { .gallery { columns: 6 180px; } }
        .gallery-item {
            position: relative;
            display: block;
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
        .gallery-item img, .gallery-item video {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
            background: #111;
            pointer-events: none;
        }
        .play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .play-icon {
            width: 48px;
            height: 48px;
            background: rgba(0,0,0,0.55);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            padding-left: 3px;
            backdrop-filter: blur(4px);
            transition: transform 0.2s, background 0.2s;
        }
        .gallery-item:hover .play-icon {
            background: rgba(255,255,255,0.2);
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
        .lb-img, .lb-vid {
            max-width: 90vw;
            max-height: 88vh;
            object-fit: contain;
            display: none;
            border-radius: 3px;
            user-select: none;
        }
        .lb-vid { max-width: min(90vw, 960px); background: #000; }
        .lb-img.show, .lb-vid.show { display: block; }
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
        .lb-close { top: 14px; right: 14px; font-size: 20px; }
        .lb-prev { left: 14px; top: 50%; transform: translateY(-50%); font-size: 30px; padding-right: 2px; }
        .lb-next { right: 14px; top: 50%; transform: translateY(-50%); font-size: 30px; padding-left: 2px; }
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
        .skipped-modal h4 { margin: 0 0 12px; font-size: 14px; color: #eee; font-weight: 500; }
        .skipped-modal ul { margin: 0; padding-left: 16px; font-size: 12px; line-height: 2; }
        .skipped-modal .x {
            position: absolute;
            top: 10px; right: 12px;
            background: none; border: none;
            font-size: 18px; color: #555; cursor: pointer;
        }
        .skipped-modal .x:hover { color: #ccc; }
    </style>
</head>
<body>
<?php if (empty($media)): ?>
    <div class="empty">
        <p>No media found in this directory.</p>
        <p class="hint">Supported: jpg &middot; png &middot; gif &middot; webp &middot; avif &middot; mp4 &middot; webm &middot; mov</p>
    </div>
<?php else: ?>
    <div class="gallery">
        <?php foreach ($media as $idx => $item): ?>
        <div class="gallery-item" onclick="openLb(<?= $idx ?>)">
            <?php if ($item['type'] === 'image'): ?>
                <img loading="lazy" src="<?= htmlspecialchars($item['src']) ?>" alt="">
            <?php else: ?>
                <video preload="metadata">
                    <source src="<?= htmlspecialchars($item['src']) ?>">
                </video>
                <div class="play-overlay"><div class="play-icon">&#9654;</div></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($skipped)): ?>
    <div class="skipped-wrap">
        <button class="skipped-btn" onclick="document.querySelector('.skipped-backdrop').classList.add('open')">
            <?= count($skipped) ?> file<?= count($skipped) !== 1 ? 's' : '' ?> skipped
        </button>
    </div>
    <div class="skipped-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
        <div class="skipped-modal">
            <button class="x" onclick="this.closest('.skipped-backdrop').classList.remove('open')">&times;</button>
            <h4>Skipped (unsupported format)</h4>
            <ul>
                <?php foreach ($skipped as $f): ?>
                    <li><?= htmlspecialchars($f['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<div class="lightbox" id="lb">
    <button class="lb-btn lb-close" onclick="closeLb()">&times;</button>
    <button class="lb-btn lb-prev" onclick="nav(-1)">&#8249;</button>
    <img class="lb-img" src="" alt="">
    <video class="lb-vid" controls preload="none"></video>
    <button class="lb-btn lb-next" onclick="nav(1)">&#8250;</button>
    <span class="lb-counter"></span>
</div>

<script>
    const media = <?= json_encode(array_map(fn($m) => ['type' => $m['type'], 'src' => $m['src']], $media)) ?>;
    let cur = -1;

    function openLb(i) { cur = i; render(); document.getElementById('lb').classList.add('open'); }

    function closeLb() {
        const lb = document.getElementById('lb');
        lb.classList.remove('open');
        const v = lb.querySelector('.lb-vid');
        v.pause(); v.removeAttribute('src'); v.load();
    }

    function nav(d) { cur = (cur + d + media.length) % media.length; render(); }

    function render() {
        const lb = document.getElementById('lb');
        const img = lb.querySelector('.lb-img');
        const vid = lb.querySelector('.lb-vid');
        const item = media[cur];
        vid.pause();
        if (item.type === 'image') {
            img.src = item.src;
            img.classList.add('show'); vid.classList.remove('show');
            vid.removeAttribute('src'); vid.load();
        } else {
            vid.src = item.src; vid.load();
            vid.classList.add('show'); img.classList.remove('show');
            img.src = '';
        }
        const n = media.length;
        lb.querySelector('.lb-counter').textContent = n > 1 ? `${cur + 1} / ${n}` : '';
        lb.querySelector('.lb-prev').classList.toggle('hide', n <= 1);
        lb.querySelector('.lb-next').classList.toggle('hide', n <= 1);
    }

    document.getElementById('lb').addEventListener('click', e => { if (e.target.id === 'lb') closeLb(); });

    document.addEventListener('keydown', e => {
        if (!document.getElementById('lb').classList.contains('open')) return;
        if (e.key === 'ArrowRight') nav(1);
        else if (e.key === 'ArrowLeft') nav(-1);
        else if (e.key === 'Escape') closeLb();
    });

    let tx = null;
    const lb = document.getElementById('lb');
    lb.addEventListener('touchstart', e => { if (e.touches.length === 1) tx = e.touches[0].clientX; }, {passive: true});
    lb.addEventListener('touchend', e => {
        if (tx !== null && e.changedTouches.length === 1) {
            const dx = e.changedTouches[0].clientX - tx;
            if (Math.abs(dx) > 50 && media.length > 1) nav(dx < 0 ? 1 : -1);
            tx = null;
        }
    });
</script>
</body>
</html>
