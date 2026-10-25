#!/usr/bin/env python3
"""
Builds the landing's responsive images from the JPEG screenshots in
public/landing/ (written by `LANDING_SHOTS=1 node tests/e2e/crawl.mjs`):
AVIF and WebP at 960 and 1440 px wide, next to the originals, which stay
as the fallback. Run it after regenerating the screenshots.

    python3 scripts/landing-images.py

Needs Pillow with AVIF and WebP support (pip install pillow).
"""
from pathlib import Path

from PIL import Image

LANDING = Path(__file__).resolve().parent.parent / 'public' / 'landing'
WIDTHS = (960, 1440)
FORMATS = {'avif': {'quality': 55, 'speed': 4}, 'webp': {'quality': 80, 'method': 6}}


def main() -> None:
    for jpg in sorted(LANDING.glob('*.jpg')):
        with Image.open(jpg) as opened:
            source = opened.convert('RGB')
        for width in WIDTHS:
            image = source if source.width <= width else source.resize(
                (width, round(source.height * width / source.width)), Image.LANCZOS)
            for ext, options in FORMATS.items():
                image.save(LANDING / f'{jpg.stem}-{width}.{ext}', ext.upper(), **options)
        made = sorted(LANDING.glob(f'{jpg.stem}-[0-9]*.*'))
        print(jpg.name, '→', ', '.join(f'{p.name} {p.stat().st_size // 1024} KB' for p in made))


if __name__ == '__main__':
    main()
