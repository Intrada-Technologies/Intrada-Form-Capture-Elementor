<?php
/**
 * A class to handle checking for plugin updates from a private or public GitHub repository.
 *
 * @version 1.0.0
 * @author Pippin Williamson (modified for this use case)
 * @link https://github.com/pippinsplugins/GitHub-Plugin-Updater
 */

if ( ! class_exists( 'Intrada_Plugin_Updater' ) ) {

    class Intrada_Plugin_Updater {

        /**
         * The plugin file path.
         * @var string
         */
        private $file;

        /**
         * The plugin data.
         * @var array
         */
        private $plugin_data;

        /**
         * The plugin's basename.
         * @var string
         */
        private $basename;

        /**
         * The GitHub repository username.
         * @var string
         */
        private $username;

        /**
         * The GitHub repository name.
         * @var string
         */
        private $repository;

        /**
         * The response from the GitHub API.
         * @var object
         */
        private $github_api_response;

        /**
         * Intrada_Plugin_Updater constructor.
         *
         * @param string $file       The full path to the main plugin file.
         * @param string $username   The GitHub username for the repository.
         * @param string $repository The GitHub repository name.
         */
        public function __construct( $file, $username, $repository ) {
            $this->file       = $file;
            $this->username   = $username;
            $this->repository = $repository;

            // Get plugin data. We need this to compare versions.
            $this->plugin_data = get_plugin_data( $this->file );
            $this->basename    = plugin_basename( $this->file );

            // Hook into the update check process.
            $this->initialize();
        }

        /**
         * Initialize the hooks.
         */
        public function initialize() {
            add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'modify_transient' ], 10, 1 );
            add_filter( 'plugins_api', [ $this, 'plugin_popup' ], 10, 3 );
            add_filter( 'upgrader_post_install', [ $this, 'after_install' ], 10, 3 );
        }

        /**
         * Check for updates and modify the transient.
         *
         * @param object $transient The WordPress update transient.
         * @return object The modified transient.
         */
        public function modify_transient( $transient ) {
            if ( ! is_object( $transient ) || ! isset( $transient->response ) ) {
                return $transient;
            }

            // Get the latest release from GitHub.
            $latest_release = $this->get_repository_info();

            // If there's an error, bail.
            if ( ! $latest_release ) {
                return $transient;
            }

            // Compare the GitHub version with the current plugin version.
            if ( version_compare( $latest_release->tag_name, $this->plugin_data['Version'], '>' ) ) {
                $package_url = $latest_release->zipball_url;

                $obj              = new stdClass();
                $obj->slug        = $this->basename;
                $obj->new_version = $latest_release->tag_name;
                $obj->url         = $this->plugin_data['PluginURI'];
                $obj->package     = $package_url;
                $obj->icons       = [
                    'default' => 'https://raw.githubusercontent.com/' . $this->username . '/' . $this->repository . '/main/assets/icon-128x128.png',
                ];
                $obj->banners     = [
                    'low'  => 'https://raw.githubusercontent.com/' . $this->username . '/' . $this->repository . '/main/assets/banner-772x250.png',
                    'high' => 'https://raw.githubusercontent.com/' . $this->username . '/' . $this->repository . '/main/assets/banner-1544x500.png',
                ];

                $transient->response[ $this->basename ] = $obj;
            }

            return $transient;
        }

        /**
         * Handle the plugin details popup.
         *
         * @param bool|object|array $result The result object.
         * @param string            $action The type of information being requested.
         * @param object            $args   Plugin arguments.
         * @return bool|object The modified result object.
         */
        public function plugin_popup( $result, $action, $args ) {
            if ( 'plugin_information' !== $action || ! isset( $args->slug ) || $args->slug !== $this->basename ) {
                return $result;
            }

            // Get the latest release from GitHub.
            $latest_release = $this->get_repository_info();

            // If there's an error, bail.
            if ( ! $latest_release ) {
                return $result;
            }

            $obj                = new stdClass();
            $obj->name          = $this->plugin_data['Name'];
            $obj->slug          = $this->basename;
            $obj->version       = $latest_release->tag_name;
            $obj->author        = $this->plugin_data['AuthorName'];
            $obj->author_profile = $this->plugin_data['AuthorURI'];
            $obj->last_updated  = $latest_release->published_at;
            $obj->homepage      = $this->plugin_data['PluginURI'];
            $obj->short_description = $this->plugin_data['Description'];
            $obj->sections      = [
                'description' => $this->plugin_data['Description'],
                'changelog'   => $latest_release->body,
            ];
            $obj->download_link = $latest_release->zipball_url;

            return $obj;
        }

        /**
         * Get the latest release information from GitHub.
         *
         * @return object|bool The latest release object or false on error.
         */
        private function get_repository_info() {
            if ( null !== $this->github_api_response ) {
                return $this->github_api_response;
            }

            $url = "https://api.github.com/repos/{$this->username}/{$this->repository}/releases/latest";

            $response = wp_remote_get( $url );

            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $this->github_api_response = false;
                return false;
            }

            $this->github_api_response = json_decode( wp_remote_retrieve_body( $response ) );

            return $this->github_api_response;
        }
        
        /**
         * Perform actions after the plugin is updated.
         *
         * @param object $upgrader_object
         * @param array  $options
         * @return void
         */
        public function after_install( $upgrader_object, $options ) {
            if ( $options['action'] == 'update' && $options['type'] === 'plugin' ) {
                // Clear the transient so it forces a re-check.
                delete_site_transient( 'update_plugins' );
            }
        }
    }
}
