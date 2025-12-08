from pathlib import Path
lines=Path('public/js/app.js').read_text(errors='ignore').splitlines()
for idx in range(420,520):
    print(f"{idx+1:04d}: {lines[idx]}")
