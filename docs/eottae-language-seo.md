# Eottae Language SEO Preparation

Phase 3 stores language metadata without changing routing.

- Supported prefixes: `/ko/`, `/en/`, `/ja/`, `/zh/`
- Post authoring language is stored separately from auto-translated cache.
- Business available languages are metadata for filtering and badges.
- Auto-translated post cache must not be indexed as SEO content by default.
- Future SEO pages should use manually reviewed fixed content and opt in per language.

Use `eottae_lang_seo_config()` as the central switch when adding language-prefixed routing later.
