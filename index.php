<?php
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$images = [];

foreach (new DirectoryIterator('.') as $file) {
    if ($file->isFile()) {
        $extension = strtolower($file->getExtension());
        if (in_array($extension, $allowed_types)) {
            $filename = $file->getFilename();
            $dimensions = getimagesize($filename);
            if ($dimensions) {
                $images[] = [
                    'name' => $filename,
                    'width' => $dimensions[0],
                    'height' => $dimensions[1],
                    'ratio' => $dimensions[0] / $dimensions[1]
                ];
            }
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
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            grid-auto-rows: 200px;
            grid-auto-flow: dense;
            gap: 20px;
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
            grid-row: auto / span 1;
        }
        .gallery-item.wide {
            grid-column: auto / span 2;
        }
        .gallery-item.tall {
            grid-row: auto / span 2;
        }
        .gallery-item.large {
            grid-column: auto / span 2;
            grid-row: auto / span 2;
        }
        .gallery-item:hover {
            transform: scale(1.02);
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
        <?php foreach($images as $image): 
            $class = '';
            if ($image['ratio'] >= 1.7) $class = 'wide';
            elseif ($image['ratio'] <= 0.7) $class = 'tall';
            elseif ($image['ratio'] >= 1.2 && $image['width'] > 1200) $class = 'large';
        ?>
        <div class="gallery-item <?= $class ?>" onclick="showLightbox('<?= htmlspecialchars($image['name']) ?>')">
            <img loading="lazy" src="<?= htmlspecialchars($image['name']) ?>" alt="">
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
