"""
Replace legacy cyan accent colors across source files under the repo root.
Usage: python scripts/experimental/python/tmp_purge_color.py
"""
from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[3]
TARGET_EXT = ('.css', '.js', '.php', '.blade.php')

replacements = [
    (r'#00f2ff', '#0ea5e9'),
    (r'rgba\(0,\s*242,\s*255,', 'rgba(14, 165, 233,'),
    (r'rgba\(0,\s*243,\s*255,', 'rgba(14, 165, 233,'),
    (r'rgb\(0,\s*242,\s*255\)', 'rgb(14, 165, 233)'),
    (r'rgb\(0,\s*243,\s*255\)', 'rgb(14, 165, 233)'),
]

match_count = 0
file_count = 0

for path in ROOT.rglob('*'):
    if not path.is_file():
        continue
    try:
        rel_parts = path.relative_to(ROOT).parts
    except ValueError:
        continue
    if any(p in rel_parts for p in ('vendor', 'node_modules', '.git')):
        continue
    if 'storage' in rel_parts and 'framework' in rel_parts and 'views' in rel_parts:
        continue
    if not str(path).endswith(TARGET_EXT):
        continue
    try:
        content = path.read_text(encoding='utf-8')
    except (OSError, UnicodeDecodeError):
        continue
    new_content = content
    for pattern, replacement in replacements:
        new_content = re.sub(pattern, replacement, new_content, flags=re.IGNORECASE)
    if new_content != content:
        path.write_text(new_content, encoding='utf-8')
        file_count += 1
        match_count += sum(content.count(x) for x in ('#00f2ff', '0, 242, 255', '0,242,255', '0, 243, 255', '0,243,255'))

print(f"Modified {file_count} files (~{match_count} hint matches) under {ROOT}")
