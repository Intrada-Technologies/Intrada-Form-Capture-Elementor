<?php

class intrada_form_capture_plugin_update
{
  public function __construct()
  {
    add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
  }

  public function plugin_info($res, $action, $args)
  {
    if ($action !== 'plugin_information' || $args->slug !== 'intrada-elementor-form-capture') {
      return $res;
    }
    if (plugin_basename(__DIR__) !== $args->slug) {
      return $res;
    }


    $remote = wp_remote_get('https://raw.githubusercontent.com/Intrada-Technologies/Intrada-Form-Capture-Elementor/refs/heads/main/info.json', array(
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
