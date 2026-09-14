# Local documentation website

Static HTML. Open `index.html` in a browser — no PHP server required.

## Open

Double-click `index.html` in this folder, or from the package root:

```bash
php bin/docs-build.php
```

Then open `docs-site/index.html`.

## Optional server

```bash
php bin/docs-serve.php
```

Open http://localhost:8000

## Rebuild

Guides come from `docs/*.md`. The API page and search index are generated from `src/` PHPDoc and the command catalogs.

```bash
php bin/docs-build.php
```
