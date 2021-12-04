<?php namespace ProcessWire;

/**
 * Module configuration for the Sitemap Submit module
 */

require_once __DIR__ . '/classes/MarkupSitemapTools.php';

class ProcessSitemapSubmitConfig extends ModuleConfig {

  /**
   * Returns the default sitemap.xml URL based on the website URL and common filename
   * @return string
   */
  private function getDefaultSitemapUrl(): string {
    return "{$this->pages->get('/')->httpUrl}sitemap.xml";
  }

  /**
   * Get default configuration
   * @return array
   */
  public function getDefaults(): array {
    $defaultUrl = $this->getDefaultSitemapUrl();

    return [
      'sitemap_submit_excluded_templates' => [],
      'sitemap_location_url' => $defaultUrl,
      'sitemap_submit_when_debug_active' => false,
      'sitemap_url_exists' => true,
      'sitemap_show_warning_if_not_submitted' => true,
      'sitemap_last_url' => $defaultUrl,
      'sitemap_default_url' => $defaultUrl,
      'sitemap_module_clear_cache_on_save' => true
    ];
  }

  /**
   * Creates configuration inputs
   * @return InputfieldWrapper
   */
  public function getInputFields(): InputfieldWrapper {
    $inputFields = parent::getInputFields();
    $allTemplates = $this->templates;
    $thisModule = $this->modules->get('ProcessSitemapSubmit');
    $moduleConfig = $this->modules->getModuleConfigData($thisModule);

    /////////////////
    // Sitemap URL //
    /////////////////

    $url = $this->modules->get('InputfieldUrl');
    $url->name = 'sitemap_location_url';
    $url->label = __('Sitemap URL');
    $url->description = __('Sitemap URL is validated if changed when module configuration is saved.');
    $url->notes = __('Default: ') . $this->getDefaultSitemapUrl();
    $url->placeholder = $this->getDefaultSitemapUrl();
    $url->icon = 'link';
    $url->columnWidth = 50;

    // Field added to fieldset below validation/verification

    /////////////////////////////////////////////
    // Sitemap URL Validation and Verification //
    /////////////////////////////////////////////

    // Set URL to default if none provided
    // Validate the format of the configured URL
    // Ping URL to check that it exists
    // Show errors if validation or ping fails

    // Get currently configured sitemap URL
    $configuredUrl = $moduleConfig['sitemap_location_url'];

    // Pass config data to JS
    $this->config->js('sitemapSubmit', [
      'moduleConfig' => ['current' => $moduleConfig, 'defaults' => $this->getDefaults()]
    ]);

    // Add module config JS to page
    $this->config->scripts->add("{$this->urls->$thisModule}src/scripts/sitemap_submit_module_config.js");

    // Set default if the URL field is empty
    if (!$configuredUrl) {
      $moduleConfig['sitemap_location_url'] = $defaultSitemapUrl;
    }

    // Will be set if the URL is checked and there are errors
    $urlError = false;

    // If a new URL is set and it's different than the last URL, then check it.
    // Prevents the module from checking the URL if no changes have been made
    if ($moduleConfig['sitemap_location_url'] !== $moduleConfig['sitemap_last_url']) {
      // Validate URL string
      $urlFormatValid = $configuredUrl ? filter_var($configuredUrl, FILTER_VALIDATE_URL) : false;


      // Show error if URL is configured but not valid
      if ($configuredUrl && !$urlFormatValid) {
        $urlError = true;
        $url->error(__("The provided sitemap.xml location URL format is invalid"));
      }

      // Check that sitemap.xml page exists at the specified URL, set a config variable for the
      // module to use.
      if ($configuredUrl && $urlFormatValid) {
        $pingResult = $thisModule->pingUrl($configuredUrl);

        // If error, add to field
        if (!$pingResult->success) {
          $urlError = true;
          $url->error(__("URL check for {$configuredUrl} failed. HTTP Status: {$pingResult->httpStatus}"));
        }
      }

      // If there is no URL error, then update the last URL to the current validated URL
      // Prevents clearing errors when saving problematic URL twice
      if (!$urlError) {
        $moduleConfig['sitemap_last_url'] = $moduleConfig['sitemap_location_url'];
      }
    }

    $moduleConfig['sitemap_url_exists'] = !$urlError;

    // Manual module configuration changes saved at end of this method

    $inputFields->add($url);


    //////////////////////////////////
    // Enable When Site Is In Debug //
    //////////////////////////////////

    $checkbox = $this->modules->get('InputfieldCheckbox');
    $checkbox->name = 'sitemap_submit_when_debug_active';
    $checkbox->label = __('Enable In ProcessWire Debug Mode');
    $checkbox->description = __('Enable this while $config->debug=true');
    $checkbox->icon = 'wrench';
    $checkbox->columnWidth = 50;

    $inputFields->add($checkbox);

    ////////////////////////
    // Excluded Templates //
    ////////////////////////

    $asmSelect = $this->modules->get('InputfieldAsmSelect');
    $asmSelect->name = 'sitemap_submit_excluded_templates';
    $asmSelect->label = __('Excluded Templates');
    $asmSelect->description = __('Pages with these templates will not trigger a sitemap submission.');

    foreach ($allTemplates as $template) {
      if ($template->flags & Template::flagSystem) {
        continue;
      }

      $asmSelect->addOption($template->name, $template->get('label|name'));
    }

    $inputFields->add($asmSelect);

    ///////////////////////
    // Show User Warning //
    ///////////////////////

    $checkbox = $this->modules->get('InputfieldCheckbox');
    $checkbox->name = 'sitemap_show_warning_if_not_submitted';
    $checkbox->label = __('Show Warning On Sitemap Submission Fail');
    $checkbox->description = __('Show a warning to the user if the sitemap was not pushed to search engines');
    $checkbox->icon = 'exclamation-triangle';
    $checkbox->columnWidth = 50;

    $inputFields->add($checkbox);

    /////////////////////////////////////
    // Companion Module Cache Clearing //
    /////////////////////////////////////

    // This module is designed to be aware of and work with the MarkupSitemap module. MarkupSitemap
    // generates, caches, and serves a sitemap.xml file. Becuase it can be cached, the sitemap may
    // not be up to date when the search engine parses it. If MarkupSitemap is installed this
    // provides an option to clear the cache when this module is triggered.

    $markupSitemapModuleInstalled = $this->modules->isInstalled('MarkupSitemap');

    /**
     * Set configuration for companion module if present
     */
    if ($markupSitemapModuleInstalled) {
      $checkbox = $this->modules->get('InputfieldCheckbox');
      $checkbox->name = 'sitemap_module_clear_cache_on_save';
      $checkbox->label = __('Clear Sitemap Cache');
      $checkbox->description = __('MarkupSitemap generates sitemaps and caches the document. To ensure that the changes to pages are seen by search engines, leave this option checked so that the sitemap is regenerated when changes in ProcessWire occur.');
      $checkbox->icon = 'bomb';
      $checkbox->columnWidth = 50;

      $inputFields->add($checkbox);
    }

    /**
     * Show message for companion module if not present
     */
    if (!$markupSitemapModuleInstalled) {
      $content = '<p>' . __("This module is works with the MarkupSitemap module which automatically generates the sitemap.xml file for a ProcessWire website. When MarkupSitemap is installed, ProcessSitemapSubmit will has more options to manage sitemaps.") . '</p>';

      $content .= '<p><a href="https://processwire.com/modules/markup-sitemap/" target="_blank" rel="noopener">' . __('Click here for more information about MarkupSitemap') . '</a></p>';

      $field = $this->modules->get('InputfieldMarkup');
      $field->label = __('Companion Module');
      $field->value = $content;
      $field->columnWidth = 50;

      $inputFields->add($field);
    }


    // Save any updates to the module configuration made above
    $this->modules->saveModuleConfigData($thisModule, $moduleConfig);

    return $inputFields;
  }
}