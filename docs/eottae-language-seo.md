# Eottae Language SEO

Multilingual SEO uses URL prefixes, hreflang tags, and a language sitemap.

## Enable

Set in `data/eottae-secrets.local.php`:

```php
'lang_seo_enabled' => true,
```

Or environment variable `LANG_SEO_ENABLED=1`.

After enabling, run **Admin → DB upgrade** so Apache/nginx rewrite rules include language prefixes.

## URL structure

| Language | Example |
|----------|---------|
| Korean (default) | `/shop/123` |
| English | `/en/shop/123` |
| Japanese | `/ja/shop/123` |
| Chinese | `/zh/shop/123` |

Korean stays unprefixed as `x-default`. Other languages use `/en/`, `/ja/`, `/zh/`.

## Head tags

When enabled, pages output:

- `<html lang="...">` matching the active language
- `<link rel="alternate" hreflang="...">` for ko, en, ja, zh-Hans
- `<link rel="alternate" hreflang="x-default">` pointing to Korean URL
- Canonical URL with the active language prefix

## Indexing policy

Auto-translated pages in non-Korean languages are **`noindex,follow`** until an admin marks the translation as **reviewed**.

This keeps machine-translated content out of search indexes by default.

## Sitemap

Multilingual sitemap (hreflang annotations):

`/proc/eottae-sitemap-lang.php`

Register in Search Console or `robots.txt`:

```
Sitemap: https://your-domain/proc/eottae-sitemap-lang.php
```

## Language selector

When SEO is enabled, changing the site language navigates to the matching prefixed URL so bookmarks and crawlers stay consistent.
