<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

/**
 * Class Elementor_Settings_Integration
 *
 * Handles the creation of a custom settings page under the Elementor menu
 * for managing API credentials and other integration settings.
 */
class Elementor_Settings_Integration
{
  /**
   * The unique slug for the settings page.
   * @var string
   */
  private $menu_slug = 'intrada-form-capture-settings';

  /**
   * The option group name for registering settings.
   * @var string
   */
  private $option_group = 'intrada-form-capture-settings-group';

  /**
   * Register the necessary WordPress hooks.
   */
  public function register()
  {
    // Hook to add the menu item to the admin dashboard.
    // We use a high priority number (lower priority) to ensure this runs after Elementor has registered its main menu.
    add_action('admin_menu', [$this, 'add_settings_page'], 99);

    // Hook to register settings, sections, and fields.
    add_action('admin_init', [$this, 'register_and_add_settings_fields']);
  }

  /**
   * Add the submenu page under the main Elementor menu.
   */
  public function add_settings_page()
  {
    add_submenu_page(
      'elementor', // Parent slug (main Elementor menu)
      __('Form Capture Settings', 'intrada-elementor-form-capture'), // Page title
      __('Form Capture Settings', 'intrada-elementor-form-capture'), // Menu title
      'manage_options', // Capability required to see this option
      $this->menu_slug, // The unique menu slug
      [$this, 'render_settings_page_wrapper'], // Callback function to render the page content
      3 // Position in the menu
    );
  }

  /**
   * Register the settings, add the section, and create the fields.
   * This function is hooked into 'admin_init'.
   */
  public function register_and_add_settings_fields()
  {
    // Register the setting group. This allows WordPress to handle saving.
    register_setting($this->option_group, 'intrada_webhook_url', ['sanitize_callback' => 'esc_url_raw']);
    register_setting($this->option_group, 'intrada_site_id', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting($this->option_group, 'intrada_site_secret', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting($this->option_group, 'intrada_api_key', ['sanitize_callback' => 'sanitize_text_field']);

    // Add a settings section to group related fields together.
    add_settings_section(
      'intrada_general_settings_section', // Section ID
      __('API Credentials', 'intrada-elementor-form-capture'), // Section title
      null, // Optional callback to render a description for the section
      $this->menu_slug // The page slug where this section will be displayed
    );

    // Add each settings field and associate it with the section.
    add_settings_field(
      'intrada_webhook_url', // Field ID
      __('Webhook URL', 'intrada-elementor-form-capture'), // Field title
      [$this, 'render_text_input_field'], // Callback to render the input
      $this->menu_slug, // Page slug
      'intrada_general_settings_section', // Section ID
      ['id' => 'intrada_webhook_url', 'type' => 'url'] // Arguments for the callback
    );

    add_settings_field(
      'intrada_site_id',
      __('Site ID', 'intrada-elementor-form-capture'),
      [$this, 'render_text_input_field'],
      $this->menu_slug,
      'intrada_general_settings_section',
      ['id' => 'intrada_site_id']
    );

    add_settings_field(
      'intrada_site_secret',
      __('Site Secret', 'intrada-elementor-form-capture'),
      [$this, 'render_text_input_field'],
      $this->menu_slug,
      'intrada_general_settings_section',
      ['id' => 'intrada_site_secret', 'type' => 'password']
    );

    add_settings_field(
      'intrada_api_key',
      __('API Key', 'intrada-elementor-form-capture'),
      [$this, 'render_text_input_field'],
      $this->menu_slug,
      'intrada_general_settings_section',
      ['id' => 'intrada_api_key', 'type' => 'password']
    );
  }

  /**
   * Render the main wrapper for the settings page.
   * This includes the form tag and WordPress helper functions.
   */
  public function render_settings_page_wrapper()
  {
    // Check if the user has the required capability.
    if (!current_user_can('manage_options')) {
      return;
    }
?>
    <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
      <form action="options.php" method="post">
        <?php
        // Output security fields for the registered setting group (nonce, etc.).
        settings_fields($this->option_group);

        // Output the settings sections and their fields.
        do_settings_sections($this->menu_slug);

        // Output the submit button.
        submit_button(__('Save Settings', 'intrada-elementor-form-capture'));
        ?>
      </form>
    </div>
  <?php
  }

  /**
   * Generic callback to render a text or password input field.
   * The specific field to render is passed via the $args array.
   *
   * @param array $args Arguments passed from add_settings_field().
   */
  public function render_text_input_field($args)
  {
    $field_id = $args['id'];
    $option_value = get_option($field_id);
    $field_type = isset($args['type']) ? $args['type'] : 'text';
  ?>
    <input
      type="<?php echo esc_attr($field_type); ?>"
      name="<?php echo esc_attr($field_id); ?>"
      value="<?php echo esc_attr($option_value); ?>"
      class="regular-text">
<?php
  }
}
