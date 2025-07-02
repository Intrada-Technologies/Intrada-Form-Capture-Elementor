<?php

/**
 * Plugin Name:       Intrada Elementor Form Capture
 * Plugin URI:        --
 * Description:       Captures Elementor form submissions and sends them to a custom endpoint.
 * Version:           1.0.2
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




function intrada_form_capture_init()
{
  $settings_file = plugin_dir_path(__FILE__) . 'includes/class-elementor-settings-integration.php';
  $capture_file  = plugin_dir_path(__FILE__) . 'includes/class-elementor-form-capture.php';
  $updater_file  = plugin_dir_path(__FILE__) . 'includes/updater.php';

  if (is_admin()) {
    // Replace with your GitHub username and repository name
    $github_username = 'Intrada-Technologies';
    $github_repository = 'Intrada-Form-Capture-Elementor';

    // Include the updater class
    if (file_exists($updater_file)) {
      require_once $updater_file;
      $updater = new Intrada_Plugin_Updater(__FILE__, $github_username, $github_repository);
    } else {
      error_log('Intrada Form Capture: Missing updater file.');
    }
    
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
add_action('plugins_loaded', 'intrada_form_capture_init');


function add_settings_link($links)
{
  // https://dazzling-dhawan.192-64-126-71.plesk.page/wp-admin/admin.php?page=intrada-form-capture-settings
  $settings_link = '<a href="admin.php?page=intrada-form-capture-settings">' . __('Settings', 'intrada-form-capture') . '</a>';
  array_unshift($links, $settings_link); // Add the link to the beginning of the array
  return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_settings_link');
