<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

// using this hook: elementor_pro/forms/new_record we need to post to the endpoint saved in the settings
/**
 * Class Elementor_Form_Capture
 *
 * Handles the capture of Elementor form submissions and sends them to a custom endpoint.
 */
class Elementor_Form_Capture
{
  // get the settings from the database
  private $settings;
  public function __construct()
  {
    // Load settings from the database
    $this->settings = get_option('intrada_form_capture_settings', []);

    // Hook into Elementor's form submission action
    add_action('elementor_pro/forms/new_record', [$this, 'capture_form_submission'], 10, 2);
  }

  /**
   * Capture the form submission and send it to the custom endpoint.
   *
   * @param \ElementorPro\Modules\Forms\Classes\Record $record The form record.
   * @param \ElementorPro\Modules\Forms\Classes\Form $form The form instance.
   */
  public function capture_form_submission($record, $form)
  {

    $form_name = $record->get_form_settings('form_name');
    $data = $record->get('fields');
    // Reload settings from the database to ensure the latest values
    $settings = get_option('intrada_form_capture_settings', []);

    $webhook_url = get_option('intrada_webhook_url', '');
    $site_id = get_option('intrada_site_id', '');
    $site_secret = get_option('intrada_site_secret', '');
    $api_key = get_option('intrada_api_key', '');

    $data['form_name'] = $form_name;


    // Check if the webhook URL is set
    if (empty($webhook_url)) {
      error_log('Elementor Form Capture: Webhook URL is not set.');
      return;
    }


    // Send the data to the endpoint
    wp_remote_post($webhook_url, [
      'method' => 'POST',
      'body' => json_encode($data),
      'headers' => [
        'Content-Type' => 'application/json',
        'X-Site-ID' => $site_id,
        'X-Site-Secret' => $site_secret,
        'X-API-Key' => $api_key,
      ],
    ]);
  }
}
