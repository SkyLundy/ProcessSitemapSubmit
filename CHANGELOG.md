# Sitemap Submit for ProcessWire Changelog

## 2.0
### New rewrite, new features, new module
This is a rewrite and expansion of the original AutoSitemapSubmit module. It addresses many
shortcomings of the initial project and adds a ton of features that are essential to managing
automatic sitemap.xml submission to search engines.

- New events that affect a sitemap have been added. Search engines now pinged when page is
  published, saved, moved, unpublished, deleted, or restored.
- Added ability to exclude templates of pages that shouldn't trigger a ping.
- Module does not submit if the page modified is hidden.
- Module config now checks that sitemap.xml exists and that the URL is valid with a 200 OK HTTP
  response
- HTTP requests are now made with ProcessWire's native `WireHttp` class.
- Major code update and refactoring. Added parameter type hinting, return type declaration, better
  organization, general overhaul
- Separated Module, Info, Config files
- Added awareness of [MarkupSitemap](https://processwire.com/modules/markup-sitemap/) module. Clears any cached sitemap so that new sitemaps are generated for content changes, overcomes TTL that may prevent search engines from seeing the latest changes.

## 1.0.0
### Initial release
- Very basic
- Not very good
- Could be better