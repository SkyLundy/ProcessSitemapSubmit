var AutoSitemapSubmitModule = AutoSitemapSubmitModule || {}

/**
 * This handles behaviors for the Auto Sitemap Submit module config page.
 * This script is inserted into the module config page only
 * @return {object} Object with public methods
 */
AutoSitemapSubmitModule.Config = (function() {

  /**
   * Holds object containing module config data passed from ProcessWire
   * @type {object}
   */
  const pwModuleConfig = ProcessWire.config.autoSitemapSubmit.moduleConfig

  /**
   * Initialize the module
   * @return {void} 
   */
  const init = () => {
    _bindFillSitemapUrlIfEmpty()
  }

  /**
   * Fills the sitemap URL if empty, ensures that it always has a value.
   * Otherwise field can appear empty but have a value assigned.
   * @return {void}
   */
  const _bindFillSitemapUrlIfEmpty = () => {
    const urlInput = document.getElementById('Inputfield_sitemap_location_url')

    if (!urlInput.value) {
      urlInput.value = pwModuleConfig.defaults.sitemap_location_url
    }
  }

  return {
    init: init
  }
}());



// Init module on page load
window.addEventListener('load', AutoSitemapSubmitModule.Config.init)