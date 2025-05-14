<?php
$allowed_types = [
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'video' => ['mp4', 'webm', 'ogg']
];
$media = [];

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
