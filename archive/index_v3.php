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
        } elseif ($extension !== 'php') {
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
            columns: 1 250px;
            column-gap: 20px;
            padding: 20px;
        }
        @media (min-width: 600px) {
            .gallery {
                columns: 2 250px;
            }
        }
        @media (min-width: 900px) {
            .gallery {
                columns: 3 250px;
            }
        }
        @media (min-width: 1200px) {
            .gallery {
                columns: 4 250px;
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
            max-width: 90%;
            max-height: 90vh;
            margin: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            object-fit: contain;
        }
        .skipped-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #222;
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 10px 18px;
            font-size: 14px;
            opacity: 0.7;
            cursor: pointer;
            z-index: 1100;
            transition: opacity 0.2s;
        }
        .skipped-btn:hover {
            opacity: 1;
        }
        .skipped-modal {
            display: none;
            position: fixed;
            bottom: 70px;
            right: 24px;
            background: #fff;
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
    <div class="lightbox" onclick="this.style.display='none'">
        <img src="" alt="">
    </div>

    <?php if (count($skipped) > 0): ?>
        <button class="skipped-btn" onclick="document.querySelector('.skipped-modal').style.display='block'">
            Show Skipped Files
        </button>
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

    <script>
        function showLightbox(imageSrc) {
            const lightbox = document.querySelector('.lightbox');
            const lightboxImg = lightbox.querySelector('img');
            lightboxImg.src = imageSrc;
            lightbox.style.display = 'block';
        }
    </script>
</body>
</html>
