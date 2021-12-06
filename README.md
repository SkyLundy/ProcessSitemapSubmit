# ProcessWire Sitemap Submit Module
**This module is still in development/testing. Use with caution and test!.**
This module submits (pings) search engines when changes to pages will affect a sitemap to notify them that the sitemap should be re-parsed. These events include:

- New page is published - New URL created
- Existing page is unpublished
- Existing page is saved
- Existing page is moved
- Existing page is deleted
- Existing page is restored

Module also:
- Checks if sitemap.xml exists
- Logs submissions to sitemap-submit log
- Sitemap URL can be specified, defaults to site-url.com/sitemap.xml
- Templates can be excluded for pages created/saved that should not be pinged
- Hidden pages do not trigger a ping.
- Currently supports Bing and Google

## Recommended companion module:
MarkupSitemap automatically generates a multi-language capable sitemap.xml file. ProcessSitemapSubmit is aware of this module when installed and handles clearing cached sitemaps to ensure that search engines always see the latest changes. More info available here [MarkupSitemap](https://processwire.com/modules/markup-sitemap/).

## Roadmap
In November 2021 Google announced that they are reviewing the [IndexNow](https://www.indexnow.org) protocol. This is a standard that has already been adopted by Bing and Yandex. If this is adopted by Google it will be worth updating the module with the ability to use this method.
