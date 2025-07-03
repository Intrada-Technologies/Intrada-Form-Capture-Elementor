<?php

class intrada_form_capture_plugin_update
{
  public function __construct()
  {
    add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
    add_filter('site_transient_update_plugins', array($this, 'push_update'));
  }

  public function push_update($transient)
  {
    if (empty($transient->checked)) {
      return $transient;
    }

    $remote = wp_remote_get('https://raw.githubusercontent.com/Intrada-Technologies/Intrada-Form-Capture-Elementor/main/info.json', array(
      'timeout' => 10,
      'headers' => array('Accept' => 'application/json')
    ));

    if (
      is_wp_error($remote)
      || 200 !== wp_remote_retrieve_response_code($remote)
      || empty(wp_remote_retrieve_body($remote))
    ) {
      return $transient;
    }

    $remote = json_decode(wp_remote_retrieve_body($remote));

    // Path to your main plugin file
    $plugin = 'intrada-elementor-form-capture/intrada-elementor-form-capture.php';

    if (
      version_compare($remote->version, $transient->checked[$plugin], '>')
    ) {
      $obj = new stdClass();
      $obj->slug = $remote->slug;
      $obj->plugin = $plugin;
      $obj->new_version = $remote->version;
      $obj->url = $remote->homepage ?? '';
      $obj->package = $remote->download_url;

      $transient->response[$plugin] = $obj;
    }

    return $transient;
  }

  public function plugin_info($res, $action, $args)
  {
    if ($action !== 'plugin_information') {
      return $res;
    }
    if ($args->slug !== 'intrada-elementor-form-capture') {
      return $res;
    }


    $remote = wp_remote_get('https://raw.githubusercontent.com/Intrada-Technologies/Intrada-Form-Capture-Elementor/main/info.json', array(
      'timeout' => 10,
      'headers' => array(
        'Accept' => 'application/json'
      )
    ));

    if (
      is_wp_error($remote)
      || 200 !== wp_remote_retrieve_response_code($remote)
      || empty(wp_remote_retrieve_body($remote))
    ) {
      return $res;
    }

    $remote = json_decode(wp_remote_retrieve_body($remote));

    $res = new stdClass();
    $res->name = $remote->name;
    $res->slug = $remote->slug;
    $res->author = $remote->author;
    $res->author_profile = $remote->author_profile;
    $res->version = $remote->version;
    $res->tested = $remote->tested;
    $res->requires = $remote->requires;
    $res->requires_php = $remote->requires_php;
    $res->download_link = $remote->download_url;
    $res->trunk = $remote->download_url;
    $res->last_updated = $remote->last_updated;
    $res->sections = array(
      'description' => $remote->sections->description,
      'installation' => $remote->sections->installation,
      'changelog' => $remote->sections->changelog
    );


    if (! empty($remote->sections->screenshots)) {
      $res->sections['screenshots'] = $remote->sections->screenshots;
    }

    $res->banners = array(
      'low' => $remote->banners->low,
      'high' => $remote->banners->high
    );

    return $res;
  }
}
