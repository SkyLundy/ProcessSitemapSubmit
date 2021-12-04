<?php namespace ProcessWire;

/**
 * Module that submits sitemap.xml to search engines to trigger re-indexing
 */

require_once __DIR__ . '/classes/MarkupSitemapTools.php';

class ProcessSitemapSubmit extends Wire implements Module {

  /**
   * Name of ProcessWire log to write to
   * @var string
   */
  private const LOG_FILENAME = 'sitemap-submit';

  /**
   * Holds full URL of sitemap.xml
   * Set on module ready
   * @var string
   */
  private $sitemapUrl;

  /**
   * Holds state of sitemap.xml presence.
   * Set on module ready
   * @var bool
   */
  private $sitemapExists = false;

  /**
   * URLs to ping with sitemap data
   * @var array
   */
  private $searchEngines = [
    'Test' => 'http://renovaenergy.ngrok.io/processwire/test/sitemaps/?sitemap=%{SITEMAP_URL}'
    // 'Google' => 'https://www.google.com/ping?sitemap=%{SITEMAP_URL}',
    // 'Bing' => 'https://www.bing.com/webmaster/ping.aspx?siteMap=%{SITEMAP_URL}'
  ];

  /**
   * Hook the hooks, do the pings
   * @return void
   */
  public function ready(): void {
    if ($this->moduleShouldInit()) {
      $eventHookSelector = $this->createEventHookSelector();

      // Published, unpublished, saved, moved, restored, deleted
      $this->addHookAfter("Pages::published({$eventHookSelector})", $this, 'pushSitemap');
      $this->addHookAfter("Pages::unpublished({$eventHookSelector})", $this, 'pushSitemap');
      $this->addHookAfter("Pages::saved({$eventHookSelector})", $this, 'pushSitemapUnlessUnpublished');
      $this->addHookAfter("Pages::moved({$eventHookSelector})", $this, 'pushSitemapUnlessUnpublished');
      $this->addHookAfter("Pages::restored({$eventHookSelector})", $this, 'pushSitemapUnlessUnpublished');
      $this->addHookAfter("Pages::deleted({$eventHookSelector})", $this, 'pushSitemapUnlessUnpublished');
    }
  }

  /**
   * This creates a hook event modifier string to be used with hook definitions
   * @return string
   */
  private function createEventHookSelector(): string {
    $excludedTemplates = $this->sitemap_submit_excluded_templates;

    // Add system templates to the excluded templates from module config
    foreach ($this->templates as $template) {
      if ($template->flags & Template::flagSystem) {
        $excludedTemplates[] = $template->name;
      }
    }

    return 'template!=' . implode('|', $excludedTemplates);
  }

  /**
   * Handles logic determining if this module should initialize for the current page
   * @return bool
   */
  private function moduleShouldInit(): bool {
    $debug = $this->config->debug;

    return !$debug || ($debug && $this->sitemap_submit_when_debug_active);
  }

  /**
   * Handles a page publish hook call
   * @param  HookEvent $e Publish hook event
   * @return void
   */
  public function pushSitemap(HookEvent $e): void {

    if ($this->sitemap_url_exists) {
      $this->pushSitemapXml($e->arguments('page'));
    }

    if (!$this->sitemap_url_exists) {
      $this->noSitemapError();
    }
  }

  /**
   * Handles a page save hook call
   * @param  HookEvent $e Save hook event
   * @return void
   */
  public function pushSitemapUnlessUnpublished(HookEvent $e): void {
    $page = $e->arguments('page');

    if (!$page->hasStatus('unpublished') && $this->sitemap_url_exists) {
      $this->pushSitemapXml($page);
    }

    if (!$this->sitemap_url_exists) {
      $this->noSitemapError();
    }
  }

  /**
   * Executes a sitemap push.
   * Handles cache clearing if supposed to
   * Warns user on error if supposed to
   * Logs result
   * @return void
   */
  public function pushSitemapXml(Page $page): void {
    $this->clearSitemapCacheIfConfigured();

    $logMsg = [
      "ID: {$page->id}",
      "URL: {$page->url}"
    ];

    foreach ($this->searchEngines as $name => $url) {
      $url = str_replace('%{SITEMAP_URL}', $this->sitemap_location_url, $url);

      $result = $this->pingUrl($url);

      $logMsg[] = $result->success ? "{$name} - Success" : "{$name} - ERROR: {$result->message}";
    }

    $this->logMsg(implode(', ', $logMsg));
  }

  /**
   * Pings a URL, checks for 200 status, or optional specified status
   * @param  string $url            URL to ping
   * @param  int    $expectedStatus HTTP status to expect
   * @return bool
   */
  public function pingUrl(string $url, int $expectedStatus = 200): object {
    $http = new WireHttp;
    $request = $http->get($url);
    $requestStatus = $http->getHttpCode();
    $success = $requestStatus === $expectedStatus;

    // Translate in case needed
    $errorMsg = sprintf(__("Expected HTTP %1$s but received %2$s"), $expectedStatus, $requestStatus);

    return (object) [
      'success' => $success,
      'httpStatus' => $requestStatus,
      'content' => $request,
      'message' => !$success ? $errorMsg : null
    ];
  }

  /**
   * Triggers a message or error
   * @param  Page $page Page error occurred on
   * @return void
   */
  private function noSitemapError(Page $page): void {
    if ($this->sitemap_show_warning_if_not_submitted) {
      $msg = sprintf(__("%s does not exist. Sitemap was not submitted to search engines."), $this->sitemap_location_url);

      $this->warning($msg);
    }

    $this->logMsg("ID: {$page->id}, URL: {$page->url}, ERROR: Sitemap not submitted, no valid sitemap URL configured");
  }

  /**
   * Logs module activity
   * @param  string $message Message to be logged
   * @return void
   */
  private function logMsg(string $message): void {
    $this->log->save(self::LOG_FILENAME, $message);
  }

  /**
   * Clears sitemap cache if configured
   * @return void
   */
  private function clearSitemapCacheIfConfigured(): void {
    $markupSitemapTools = new MarkupSitemapTools;

    if ($markupSitemapTools->moduleInstalled() && $this->sitemap_module_clear_cache_on_save) {
      $result = $markupSitemapTools->clearSitemapCache();

      if ($result) {
        $this->logMsg("MarkupSitemap cache cleared");
      }
    }
  }
}
