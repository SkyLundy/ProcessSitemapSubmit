<?php namespace ProcessWire;

/**
 * This class handles clearing sitemap caches which may exist from known sources
 */

class MarkupSitemapTools extends Wire {

  /**
   * Name of cache where sitemaps are stored
   * @var string
   */
  private $sitemapCacheName = 'MarkupCache/MarkupSitemap';

  /**
   * Clears the sitemap cache for MarkupSitemap
   * @return bool  Success/fail
   */
  public function clearSitemapCache(): bool {
    // Always check in case this method was called without checking if
    // the module is installed
    $moduleInstalled = $this->moduleInstalled();
    $cacheCleared = false;

    // If the module is presen, check if method is availablet
    if ($moduleInstalled) {
      $cacheCleared = $this->clearSitemapCacheByModuleMethod();
    }

    // If method is not available, then attempt to destroy it directly
    if (!$cacheClearMethodExists || !$cacheCleared) {
      $cacheCleared = $this->clearSitemapCacheDirectly();
    }

    return $cacheCleared;
  }

  /**
   * Returns
   * @return [type] [description]
   */
  public function moduleInstalled(): bool {
    return $this->modules->isInstalled('MarkupSitemap');
  }

  /////////////////////////
  // MarkupSitemap tools //
  /////////////////////////

  /**
   * Attempts to call the removeSitemapCache method if it exists in the module
   * @return bool  Success/fail
   */
  private function clearSitemapCacheByModuleMethod(): bool {
    $moduleInstalled = $this->moduleInstalled();
    $cacheCleared = false;

    if ($moduleInstalled) {
      $markupSitemapModule = $this->modules->get('MarkupSitemap');
      $cacheClearMethodExists = method_exists($markupSitemapModule, 'removeSitemapCache');

      $cacheCleare = $markupSitemapModule->removeSitemapCache();
    }

    return $cacheCleared;
  }

  /**
   * Manually clears the markup cache
   * This replicates the cache clearing functionality of MarkupSitemap
   * @return bool  Success/fail
   */
  private function clearSitemapCacheDirectly(): bool {
    $removed = false;

    try {
      $cachePath = $this->config->paths->cache . $this->$sitemapCacheName;
      $removed = (bool) CacheFile::removeAll($cachePath, true);
    } catch (\Exception $e) {
      $removed = false;
    }

    return $removed;
  }
}