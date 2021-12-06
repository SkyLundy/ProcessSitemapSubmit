<?php namespace ProcessWire;

/**
 * This class handles clearing sitemap caches which may exist from known sources
 */

class MarkupSitemapTools extends Wire {

  /**
   * Name of cache where sitemaps are stored
   * @var string
   */
  private $sitemapCachePath = 'MarkupCache/MarkupSitemap';

  /**
   * Key used to identify MarkupSitemap cache
   * @var string
   */
  private $cacheKey = 'MarkupSitemap';

  /**
   * Clears the sitemap cache for MarkupSitemap
   * @return bool Success/Fail
   */
  public function clearSitemapCache(): bool {
    // Always check in case this method was called without checking if
    // the module is installed
    $moduleInstalled = $this->moduleInstalled();
    $cacheCleared = false;

    // If the module is presen, check if method is availablet
    if ($moduleInstalled) {
      $cacheClearMethodExists = $this->cacheClearMethodExists();

      if ($cacheClearMethodExists) {
        $cacheCleared = $this->clearSitemapCacheByModuleMethod();
      }

      if (!$cacheClearMethodExists) {
        $cacheCleared = $this->clearSitemapCacheDirectly();
      }
    }

    return $cacheCleared;
  }

  /**
   * Determines whether the MarkupSitemap module is installed
   * @return bool
   */
  public function moduleInstalled(): bool {
    return $this->modules->isInstalled('MarkupSitemap');
  }

  /////////////////////////
  // MarkupSitemap tools //
  /////////////////////////

  /**
   * Calls cache clear method from module.
   * NOTE: This does not check for the existence of the module or availability of the method, ensure
   *       that both of these conditions are met before calling this method.
   * @return bool  Success/fail
   */
  private function clearSitemapCacheByModuleMethod(): bool {
    return $this->modules->get('MarkupSitemap')->removeSitemapCache();
  }

  /**
   * Manually clears the markup cache
   * NOTE: This does not check for the existence of the module or availability of the method, ensure
   *       that both of these conditions are met before calling this method.
   * @return bool Success/Fail
   */
  private function clearSitemapCacheDirectly(): bool {
    $moduleConfig = $this->modules->getModuleConfigData('MarkupSitemap');
    $cacheMethod = $moduleConfig->cache_method ?: 'MarkupCache';

    // Attempt to fetch sitemap from cache
    $cache = $moduleConfig->cache_method === 'WireCache' ? $this->cache : $this->modules->MarkupCache;

    // Checks if sitemap is cached by geting data from cache if it exists.
    $sitemapIsCached = !!$cache->get($this->cacheKey);

    if ($sitemapIsCached) {
      // If WireCache, destroy it
      if ($cacheMethod === 'WireCache') {
        $removed = (bool) $cache->deleteFor($this->cacheKey);
      }

      // If MarkupCache, destroy it
      if ($cacheMethod === 'MarkupCache') {
        try {
          $removed = (bool) CacheFile::removeAll($this->cachePath, true);
        } catch (\Exception $e) {
          $removed = false;
        }
      }
    }

    // If the cache doesn't exist, return true since, for all intents and purposes, it was cleared
    if (!$sitemapIsCached) {
      $removed = true;
    }

    return $removed;
  }

  /**
   * Checks for existence of accessible module method that clears the sitemap cache
   * @return bool
   */
  private function cacheClearMethodExists(): bool {
    $markupSitemapModule = $this->modules->get('MarkupSitemap');

    return method_exists($markupSitemapModule, 'removeSitemapCache');
  }
}