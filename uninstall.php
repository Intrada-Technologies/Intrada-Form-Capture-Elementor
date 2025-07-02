<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit();
}

// Remove plugin options from the database
delete_option('intrada_webhook_url');
delete_option('intrada_site_id');
delete_option('intrada_site_secret');
delete_option('intrada_api_key');


// Remove options from multisite installations
if (is_multisite()) {
  global $wpdb;
  $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
  foreach ($blog_ids as $blog_id) {
    switch_to_blog($blog_id);
    delete_option('intrada_webhook_url');
    delete_option('intrada_site_id');
    delete_option('intrada_site_secret');
    delete_option('intrada_api_key');
    restore_current_blog();
  }
}
