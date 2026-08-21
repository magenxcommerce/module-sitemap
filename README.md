# Magenx_Sitemap

Rewrites the URLs Magento writes into `sitemap.xml` so they match a **headless
storefront's** routes instead of Luma's.

Magento builds sitemap entries from `url_rewrite.request_path` plus the store's
base URL, which yields Luma paths:

```
men/tops.html
men/tops/blue-jacket.html
about-us
```

The Next.js storefront serves something else entirely — a locale prefix on every
URL, a `product` / `category` segment, and no `.html` suffix:

```
/en/category/men/tops
/en/product/blue-jacket
/en/about-us
```

Core Magento has no setting for any of that, so an unmodified sitemap advertises
URLs that all 404. This module adds the missing settings and applies them during
sitemap generation.

## How it works

`Magento\Sitemap\Model\ItemProvider\*::getItems()` is the only public seam in
sitemap generation (`Sitemap::_getUrl()` and `_getStoreBaseUrl()` are protected),
so one `after` plugin per provider — store URL, category, product, CMS page —
wraps each returned item in a decorator that reports the rewritten URL and
delegates everything else to the core item. Core files are untouched, and the
base URL still comes from `web/*/base_url` as usual.

Per item (`Model/UrlRewriter`):

1. Absolute URLs are returned unchanged.
2. The catalog URL suffix (`.html`) is stripped from product and category paths.
3. A product path is reduced to its last segment (its `url_key`).
4. The entity path prefix (`product` / `category` / CMS) is prepended.
5. The store path prefix (the storefront locale, e.g. `en`) is prepended.
6. The store URL item and the configured CMS home page collapse to the store
   root — `/en`, never `/en/home`.

All settings are read at store-view scope, which is what makes one prefix per
locale possible.

## Configuration

**Stores → Configuration → Magenx → Sitemap URLs**, ACL resource
`Magenx_Sitemap::config`.

| Path | Default | Meaning |
| --- | --- | --- |
| `magenx_sitemap/general/enabled` | `0` | Master switch. Off = core URLs unchanged. |
| `magenx_sitemap/general/store_path_prefix` | *(empty)* | Leading segment for this store view, normally the locale (`en`, `de`, …). |
| `magenx_sitemap/general/strip_url_suffix` | `1` | Strip `catalog/seo/{product,category}_url_suffix`. |
| `magenx_sitemap/category/path_prefix` | `category` | Segment before the category path. |
| `magenx_sitemap/product/path_prefix` | `product` | Segment before the product URL key. |
| `magenx_sitemap/product/url_key_only` | `1` | Drop the category path from product URLs. |
| `magenx_sitemap/cms/path_prefix` | *(empty)* | Segment before the CMS page identifier. |

Ships **off**: the prefixes are pre-filled for this storefront, but the store
path prefix is store-view specific, and rewriting with an unset prefix would
publish URLs the storefront cannot serve.

## Install

```bash
composer require magenx/module-sitemap
bin/magento module:enable Magenx_Sitemap
bin/magento setup:upgrade
bin/magento cache:flush
```

Then, per store view: set **Store Path Prefix** to the storefront locale and set
**Rewrite Sitemap URLs** to *Yes*. Regenerate under **Marketing → SEO & Search →
Site Map**, and spot-check that one product, one category, one CMS page and the
home page entry each return 200 on the storefront.

## Caveats

- **One sitemap row per store view** is required in *Marketing → Site Map*, each
  with its own filename. A storefront that advertises a single `/sitemap.xml`
  (e.g. from `robots.txt`) wants that file to be a sitemap index.
- **Batch generation is not covered.** `Magento\Sitemap\Model\Batch\Sitemap`
  (opt-in, streams products straight from the resource model) bypasses the
  product item provider, so product URLs would not be rewritten there. Standard
  generation — the admin button and the `sitemap_generate` cron job — is.
- **Product image URLs are untouched**; they keep Magento's media base URL.
- Magento must be able to write the sitemap path, and the storefront origin must
  serve that file — both deployment concerns outside this module.
