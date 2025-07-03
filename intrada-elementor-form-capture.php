<?php

/**
 * Plugin Name:       Intrada Elementor Form Capture
 * Plugin URI:        --
 * Description:       Captures Elementor form submissions and sends them to a custom endpoint.
 * Version:           1.0.4
 * Author:            Intrada Technologies
 * Author URI:        https://intradatech.com/
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       intrada-form-capture
 * Requires Plugins:  elementor-pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Load plugin text domain for translations
 */
function intrada_form_capture_load_textdomain()
{
  load_plugin_textdomain('intrada-form-capture', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'intrada_form_capture_load_textdomain');


function intrada_form_capture_init()
{
  $settings_file = plugin_dir_path(__FILE__) . 'includes/class-elementor-settings-integration.php';
  $capture_file  = plugin_dir_path(__FILE__) . 'includes/class-elementor-form-capture.php';
  $update_file   = plugin_dir_path(__FILE__) . 'includes/update.php';

  if (file_exists($update_file)) {
    require_once $update_file;
    new intrada_form_capture_plugin_update();
  } else {
    error_log('Intrada Form Capture: Missing update file.');
  }


  // Check if required files exist
  if (!file_exists($settings_file)) {
    error_log('Intrada Form Capture: Missing settings integration file.');
    return;
  }
  if (!file_exists($capture_file)) {
    error_log('Intrada Form Capture: Missing form capture file.');
    return;
  }

  require_once $settings_file;
  require_once $capture_file;

  // Check if classes exist before instantiating
  if (!class_exists('Elementor_Settings_Integration')) {
    error_log('Intrada Form Capture: Elementor_Settings_Integration class not found.');
    return;
  }
  if (!class_exists('Elementor_Form_Capture')) {
    error_log('Intrada Form Capture: Elementor_Form_Capture class not found.');
    return;
  }

  try {
    $elementor_settings_integration = new Elementor_Settings_Integration();
    $elementor_settings_integration->register();

    $elementor_form_capture = new Elementor_Form_Capture();
  } catch (Exception $e) {
    error_log('Intrada Form Capture: Exception - ' . $e->getMessage());
  }
}
add_action('init', 'intrada_form_capture_init');


/**
 * Add settings link to plugin action links
 */
function intrada_form_capture_add_settings_link($links)
{
  $settings_link = '<a href="admin.php?page=intrada-form-capture-settings">' . __('Settings', 'intrada-form-capture') . '</a>';
  array_unshift($links, $settings_link);
  return $links;
}

/**
 * Initialize plugin action links after text domain is loaded
 */
function intrada_form_capture_init_admin()
{
  add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'intrada_form_capture_add_settings_link');
}
add_action('init', 'intrada_form_capture_init_admin');
