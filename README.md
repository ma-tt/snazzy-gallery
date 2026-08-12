# Snazzy Gallery

A beautiful, modern, and fully responsive single-file PHP gallery script.  
Just drop `index.php` into any web directory containing images and/or videos, and Snazzy Gallery will automatically display them in a stunning, masonry-style grid—no setup required!

![Snazzy Gallery Screenshot](screenshot.jpg)

---

## Features

- **Automatic Media Detection:**  
  Scans the directory for supported images and videos—no configuration needed.

- **Responsive Masonry Layout:**  
  Uses CSS columns for a Pinterest-like, gapless grid that preserves each media's original aspect ratio.

- **Image & Video Support:**  
  Supports `jpg`, `jpeg`, `png`, `gif`, `webp`, `avif`, `svg` and video formats `mp4`, `webm`, `mov`.

- **Lightbox for Images & Videos:**  
  Click any image or video to view it fullscreen. Navigate with ← → arrow keys, swipe left/right on mobile, or press Esc to close.

- **Bandwidth-Friendly Video Loading:**  
  Videos load only metadata by default; full video data is loaded only when played.

- **Skipped Files Notification:**  
  If unsupported files are found, a discreet button appears to let users view a list of skipped files and their formats.

- **No Dependencies:**  
  Pure PHP, HTML, and CSS—no database, no JavaScript frameworks, no build steps.

---

## Usage

1. **Copy `index.php`** into any directory you want to display as a gallery.
2. **Add your images and/or videos** to the same directory.
3. **Open the directory in your browser** via a local or remote web server.

That's it! The gallery will automatically display all supported media.

---

## Supported Formats

- **Images:** `jpg`, `jpeg`, `png`, `gif`, `webp`, `avif`, `svg`
- **Videos:** `mp4`, `webm`, `mov`

> Unsupported files (including PHP files) are ignored.  
> A "Show Skipped Files" button appears if any unsupported files are found.

---

## Security Notes

- Only use this script in directories where you trust the uploaded files.
- PHP files are never displayed in the gallery or in the skipped files list.

---

## License

[Snazzy Gallery](https://github.com/ma-tt/snazzy-gallery) © 2025 by [ma-tt](https://github.com/ma-tt) is licensed under [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/).

---

## Credits

Created by [ma-tt](https://github.com/ma-tt).