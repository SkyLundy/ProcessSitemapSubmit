# ProcessWire Auto Sitemap Submit Module
**This module is still in development is incomplete. Please wait for a formal release before using.**
This module tracks changes to pages that have an effect on a website's sitemap and then notifies
(pings) search engines. These events include:

- New page is published
- Existing page is saved, published, unpublished, moved, deleted, restored

- Checks if sitemap.xml exists
- Results of pings are logged to auto-sitemap-submit log
- Warning message provided if page is published or saved and sitemap.xml is not found
- Sitemap URL can be specified, defaults to site-url.com/sitemap.xml
- Templates can be excluded for pages created/saved that should not be pinged
- Hidden pages do not trigger a ping.
- Currently supports Bing and Google

## Recommended companion module:
This module automatically generates a sitemap.xml URL. Miltilanguage capable.
[MarkupSitemap](https://processwire.com/modules/markup-sitemap/)

## Roadmap
In November 2021 Google announced that they are reviewing the [IndexNow](https://www.indexnow.org)
protocol. This is a standard that has already been adopted by Bing and Yandex. If this is adopted
by Google it will be worth updating the module with new abilities, namely:
- When a tracked page event occurs, send sitemap.xml ping
- If the tracked page event results in content indexable changes (not deleted, unpublished, etc.), also send a
  PageIndex hit.

Google being Google, we won't have more information on this until it actually happens, if it does...
