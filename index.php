<?php
$allowed_types = [
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'video' => ['mp4', 'webm', 'ogg']
];
$media = [];
$skipped = [];

foreach (new DirectoryIterator('.') as $file) {
    if ($file->isFile()) {
        $extension = strtolower($file->getExtension());
        if (in_array($extension, $allowed_types['image'])) {
            $media[] = [
                'type' => 'image',
                'src' => $file->getFilename()
            ];
        } elseif (in_array($extension, $allowed_types['video'])) {
            $media[] = [
                'type' => 'video',
                'src' => $file->getFilename()
            ];
        } elseif (
            $extension !== 'php' &&
            $extension !== 'md' &&
            $extension !== '' // skip files with no extension
        ) {
            $skipped[] = [
                'name' => $file->getFilename(),
                'ext' => $extension
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snazzy Gallery</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .gallery {
            columns: 2 140px;
            column-gap: 12px;
            padding: 12px;
        }
        @media (min-width: 600px) {
            .gallery {
                columns: 3 150px;
            }
        }
        @media (min-width: 900px) {
            .gallery {
                columns: 4 160px;
            }
        }
        @media (min-width: 1200px) {
            .gallery {
                columns: 6 180px;
            }
        }
        .gallery-item {
            display: block;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
            margin-bottom: 20px;
            break-inside: avoid;
        }
        .gallery-item:hover {
            transform: scale(1.02);
        }
        .gallery-item img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
            background: #eee;
        }
        .gallery-item video {
            width: 100%;
            height: auto;
            display: block;
            background: #222;
            border: none;
            outline: none;
            border-radius: 0;
        }
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 1000;
            cursor: pointer;
        }
        .lightbox img {
            max-width: 92%;
            max-height: 92vh;
            margin: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            object-fit: contain;
        }
        .skipped-btn {
            display: inline-block;
            margin: 32px auto 0 auto;
            background: none;
            color: #888;
            border: none;
            border-radius: 20px;
            padding: 6px 12px;
            font-size: 13px;
            opacity: 0.5;
            cursor: pointer;
            transition: opacity 0.2s, color 0.2s;
            z-index: 1;
            box-shadow: none;
        }
        .skipped-btn:hover {
            opacity: 0.9;
            color: #222;
            background: #f0f0f0;
        }
        .skipped-btn:active {
            opacity: 1;
        }
        .skipped-btn-container {
            width: 100%;
            text-align: center;
        }
        .skipped-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #ffff;
            color: #222;
            border-radius: 8px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.18);
            padding: 18px 22px 18px 18px;
            min-width: 260px;
            max-width: 90vw;
            max-height: 60vh;
            overflow-y: auto;
            z-index: 1200;
        }
        .skipped-modal h4 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .skipped-modal ul {
            margin: 0;
            padding-left: 18px;
            font-size: 13px;
        }
        .skipped-modal .close {
            position: absolute;
            top: 8px;
            right: 12px;
            background: none;
            border: none;
            font-size: 18px;
            color: #888;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="gallery">
        <?php foreach($media as $item): ?>
        <div class="gallery-item" onclick="<?php if($item['type']==='image'): ?>showLightbox('<?= htmlspecialchars($item['src']) ?>')<?php else: ?>null<?php endif; ?>">
            <?php if ($item['type'] === 'image'): ?>
                <img loading="lazy" src="<?= htmlspecialchars($item['src']) ?>" alt="">
            <?php else: ?>
                <video controls preload="metadata" poster="" tabindex="0">
                    <source src="<?= htmlspecialchars($item['src']) ?>">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($skipped) > 0): ?>
        <div class="skipped-btn-container">
            <button class="skipped-btn" onclick="document.querySelector('.skipped-modal').style.display='block'">
                Show Skipped Files
            </button>
        </div>
        <div class="skipped-modal">
            <button class="close" onclick="this.parentElement.style.display='none'">&times;</button>
            <h4>Skipped Files (Unsupported):</h4>
            <ul>
                <?php foreach($skipped as $file): ?>
                    <li><?= htmlspecialchars($file['name']) ?> <span style="color:#888;">(<?= htmlspecialchars($file['ext']) ?>)</span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class="lightbox" onclick="this.style.display='none'">
        <img src="" alt="">
    </div>

    <script>
        // Collect image sources for navigation
        const images = [
            <?php foreach($media as $item): if($item['type']==='image'): ?>
                "<?= htmlspecialchars($item['src']) ?>",
            <?php endif; endforeach; ?>
        ];
        let currentIndex = -1;

        function showLightbox(imageSrc) {
            const lightbox = document.querySelector('.lightbox');
            const lightboxImg = lightbox.querySelector('img');
            currentIndex = images.indexOf(imageSrc);
            lightboxImg.src = imageSrc;
            lightbox.style.display = 'block';
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            const lightbox = document.querySelector('.lightbox');
            if (lightbox.style.display === 'block' && images.length > 1) {
                if (e.key === 'ArrowRight') {
                    currentIndex = (currentIndex + 1) % images.length;
                    lightbox.querySelector('img').src = images[currentIndex];
                } else if (e.key === 'ArrowLeft') {
                    currentIndex = (currentIndex - 1 + images.length) % images.length;
                    lightbox.querySelector('img').src = images[currentIndex];
                } else if (e.key === 'Escape') {
                    lightbox.style.display = 'none';
                }
            }
        });

        // Touch swipe navigation
        let touchStartX = null;
        document.querySelector('.lightbox').addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                touchStartX = e.touches[0].clientX;
            }
        });
        document.querySelector('.lightbox').addEventListener('touchend', function(e) {
            if (touchStartX !== null && e.changedTouches.length === 1) {
                let touchEndX = e.changedTouches[0].clientX;
                let dx = touchEndX - touchStartX;
                if (Math.abs(dx) > 50 && images.length > 1) {
                    if (dx < 0) { // swipe left
                        currentIndex = (currentIndex + 1) % images.length;
                    } else { // swipe right
                        currentIndex = (currentIndex - 1 + images.length) % images.length;
                    }
                    this.querySelector('img').src = images[currentIndex];
                }
                touchStartX = null;
            }
        });
    </script>
</body>
</html>
