<?php
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$images = [];

foreach (new DirectoryIterator('.') as $file) {
    if ($file->isFile()) {
        $extension = strtolower($file->getExtension());
        if (in_array($extension, $allowed_types)) {
            $images[] = $file->getFilename();
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
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            grid-auto-flow: dense;
            grid-gap: 20px;
            padding: 20px;
        }
        .gallery-item {
            position: relative;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
            min-height: 200px;
        }
        .gallery-item:hover {
            transform: scale(1.02);
        }
        .gallery-item img {
            width: 100%;
            height: auto;
            display: block;
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
        <?php foreach($images as $image): ?>
        <div class="gallery-item" onclick="showLightbox('<?= htmlspecialchars($image) ?>')">
            <img loading="lazy" src="<?= htmlspecialchars($image) ?>" alt="">
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
