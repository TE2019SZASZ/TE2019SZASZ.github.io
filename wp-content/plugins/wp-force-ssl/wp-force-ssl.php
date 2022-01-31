<?php
/*
  Plugin Name: WP Force SSL
  Plugin URI: https://wpforcessl.com/
  Description: Redirect all traffic from HTTP to HTTPS and fix other SSL issues.
  Author: WebFactory Ltd
  Author URI: https://www.webfactoryltd.com/
  Version: 1.65
  Text Domain: wp-force-ssl
  Requires at least: 4.6
  Requires PHP: 5.2
  Tested up to: 5.8

  Copyright 2019 - 2022  WebFactory Ltd  (email: support@webfactoryltd.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// if the file is called directly
if (!defined('ABSPATH')) {
  exit('You are not allowed to access this file directly.');
}

require_once dirname(__FILE__) . '/inc/wp-force-ssl-status-tests.php';
require_once dirname(__FILE__) . '/inc/wp-force-ssl-utility.php';
require_once dirname(__FILE__) . '/wf-flyout/wf-flyout.php';
new wf_flyout(__FILE__);

define('WPFSSL_OPTIONS_KEY', 'wpfssl_options');
define('WPFSSL_META_KEY', 'wpfssl_meta');


// start up the engine
class wpForceSSL
{
  protected static $instance = false;
  public $options = array();
  public $version = 0;
  public $plugin_url = '';
  public $plugin_dir = '';
  public $meta = array();


  /**
   * Check if minimum WP and PHP versions are available
   * Register all hooks for the plugin
   *
   * @since 1.5
   *
   * @return null
   *
   */
  private function __construct()
  {
    $this->get_plugin_version();

    if (false === $this->check_wp_version(4.6)) {
      return false;
    }

    $this->options = get_option(WPFSSL_OPTIONS_KEY, array());
    $default = array(
      'wpfs_ssl' => 'yes',
      'wpfs_hsts' => 'no',
      'wpfs_expect_ct' => 'no',
      'wpfs_adminbar_menu' => 'yes',
      'wpfs_dashboard_widget' => 'yes'
    );
    $this->options = array_merge($default, $this->options);

    $this->plugin_url = plugin_dir_url(__FILE__);
    $this->plugin_dir = plugin_dir_path(__FILE__);
    $this->meta = $this->get_meta();

    if ($this->options['wpfs_ssl'] == 'yes') {
      add_action('template_redirect', array($this, 'wpfs_core'));
    }
    if ($this->options['wpfs_hsts'] == 'yes') {
      add_action('send_headers', array($this, 'to_strict_transport_security'));
    }
    if ($this->options['wpfs_expect_ct'] == 'yes') {
      add_action('send_headers', array($this, 'enable_expect_ct'));
    }

    add_action('admin_menu', array($this, 'add_settings_page'));
    add_action('admin_head',  array($this, 'cleanup_enqueues'), 99999);
    add_action('wp_before_admin_bar_render', array($this, 'admin_bar'));
    add_filter('admin_footer_text', array($this, 'admin_footer_text'));
    add_action('admin_print_scripts', array($this, 'remove_admin_notices'));
    add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
    add_action('wp_dashboard_setup', array($this, 'add_widget'));

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
    add_filter('plugin_row_meta', array($this, 'plugin_meta_links'), 10, 2);

    // ajax hooks for the settings, and SSL certificate test
    add_action('wp_ajax_wpfs_save_settting', array($this, 'ajax_save_setting'));
    add_action('wp_ajax_wpfs_test_ssl', array($this, 'ajax_check_ssl'));
    add_action('wp_ajax_wpfs_run_tests', array($this, 'ajax_run_tests'));
    add_action('wp_ajax_wpfs_dismiss_notice', array($this, 'ajax_dismiss_notice'));
  } // __construct


  /**
   * Get plugin meta data, create if not existent
   *
   * @since 1.5
   *
   * @return array plugin meta
   *
   */
  public function get_meta()
  {
    $meta = get_option(WPFSSL_META_KEY, array());
    $default = array(
      'first_version' => $this->get_plugin_version(),
      'first_install' => time(),
      'hide_welcome_pointer' => false
    );

    $meta = array_merge($default, $meta);
    if (sizeof($default) != sizeof($meta)) {
      update_option(WPFSSL_META_KEY, $meta);
    }

    return $meta;
  } // get_meta


  /**
   * Dismiss notice via AJAX call
   *
   * @return null
   */
  function ajax_dismiss_notice()
  {
    check_ajax_referer('wpfs_dismiss_notice');

    if (!current_user_can('administrator')) {
      wp_send_json_error('You are not allowed to run this action.');
    }

    $notice_name = trim(sanitize_text_field(@$_GET['notice_name']));

    if ($notice_name != 'welcome') {
      wp_send_json_error('Unknown notice');
    } else {
      $this->meta['hide_welcome_pointer'] = true;
      update_option(WPFSSL_META_KEY, $this->meta);
      wp_send_json_success();
    }
  } // ajax_dismiss_notice


  /**
   * Get plugin version
   *
   * @since 1.5
   *
   * @return string plugin version
   *
   */
  public function get_plugin_version()
  {
    $plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');
    $this->version = $plugin_data['version'];

    return $plugin_data['version'];
  } // get_plugin_version


  /**
   * Return instance, create if not already existing
   *
   * @since 1.5
   *
   * @return object wpForceSLL instance
   *
   */
  public static function get_instance()
  {
    if (false == is_a(self::$instance, 'wpForceSSL')) {
      self::$instance = new self;
    }

    return self::$instance;
  } // get_instance


  /**
   * Run websites tests
   *
   * @return null
   */
  function ajax_run_tests()
  {
    global $wp_force_ssl_tests;

    check_ajax_referer('run_tests_nonce_action');
    if (!current_user_can('administrator')) {
      wp_send_json_error('You are not allowed to run this action.');
    }

    if (isset($_REQUEST['force']) && (bool) $_REQUEST['force'] === true) {
      $nocache = true;
    } else {
      $nocache = false;
    }

    $res = $wp_force_ssl_tests->get_tests_results($nocache);
    if (is_wp_error($res)) {
      wp_send_json_error($res->get_error_message());
    } else {
      wp_send_json_success($res);
    }
  } // ajax_run_tool


  /**
   * Enqueue admin scripts
   *
   * @since 1.5
   *
   * @return null
   *
   */
  public function admin_scripts($hook)
  {
    $meta = $this->get_meta();
    $pointers = array();

    if (!$meta['hide_welcome_pointer'] && !$this->is_plugin_page() && current_user_can('administrator')) {
      $pointers['_nonce_dismiss_pointer'] = wp_create_nonce('wpfs_dismiss_notice');
      $pointers['welcome'] = array('target' => '#menu-settings', 'edge' => 'left', 'align' => 'right', 'content' => 'Thank you for installing the <b style="font-weight: 800;">WP Force SSL</b> plugin!<br>Open <a href="' . admin_url('options-general.php?page=wpfs-settings') . '">Settings - WP Force SSL</a> to access SSL settings.');

      wp_enqueue_style('wp-pointer');

      wp_enqueue_script('wp-force-ssl-pointers', $this->plugin_url . 'js/wpfs-pointers.js', array('jquery'), $this->version, true);
      wp_enqueue_script('wp-pointer');
      wp_localize_script('wp-pointer', 'wp_force_ssl_pointers', $pointers);
    }

    if (false == $this->is_plugin_page()) {
      return;
    }

    wp_enqueue_style('wpfs-style', $this->plugin_url . 'css/wpfs-style.css', null, $this->version);
    wp_enqueue_style('wpfs-sweetalert2-style', $this->plugin_url . 'css/sweetalert2.min.css', null, $this->version);
    wp_enqueue_style('wp-jquery-ui-dialog');

    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-position');
    wp_enqueue_script('jquery-ui-dialog');

    wp_enqueue_script('wpfs-sweetalert2', $this->plugin_url . 'js/sweetalert2.min.js', array('jquery'), $this->version, true);
    wp_enqueue_script('wpfs-script', $this->plugin_url . 'js/wpfs-script.js', array('jquery'), $this->version, true);

    wp_localize_script('wpfs-script', 'wpfs', array(
      'ajaxurl' => admin_url('admin-ajax.php'),
      'loading_icon_url' => plugins_url('img/wp-ssl.png', __FILE__),
      'testing' => __('Testing. Please wait ...', 'wp-force-ssl'),
      'saving' => __('Saving. Please wait ...', 'wp-force-ssl'),
      'test_success' => __('Test Completed Successfully', 'wp-force-ssl'),
      'test_failed' => __('Test Failed', 'wp-force-ssl'),
      'home_url' => get_home_url(),
      'save_success' => __('Settings saved', 'wp-force-ssl'),
      'undocumented_error' => __('An undocumented error has occurred. Please refresh the page and try again.', 'wp-force-ssl'),
      'documented_error' => __('An error has occurred.', 'wp-force-ssl'),
      'nonce_save_settings' => wp_create_nonce('save_settting_nonce_action'),
      'nonce_test_ssl' => wp_create_nonce('test_ssl_nonce_action'),
      'nonce_run_tests' => wp_create_nonce('run_tests_nonce_action')
    ));

    $this->cleanup_enqueues();
  } // admin_scripts


  /**
   * Load text domain
   *
   * @since 1.5
   *
   * @return null
   *
   */
  static function plugins_loaded()
  {
    load_plugin_textdomain('wp-force-ssl');
  } // plugins_loaded


  /**
   * Register menu page
   *
   * @since 1.5
   *
   * @return null
   *
   */
  public function add_settings_page()
  {
    global $wp_force_ssl_tests;
    $test_results = $wp_force_ssl_tests->count_statuses();

    add_options_page(
      __('WP Force SSL', 'wp-force-ssl'),
      __('WP Force SSL', 'wp-force-ssl') . ($test_results['fail'] ? sprintf(' <span class="wfssl-failed-tests awaiting-mod">%d</span>', $test_results['fail']) : ''),
      'administrator',
      'wpfs-settings',
      array($this, 'settings_page_content')
    );
  } // add_settings_page


  /**
   * Shows admin top menu entry
   *
   * @since 1.6
   *
   * @return null
   *
   */
  function admin_bar()
  {
    if (!is_admin()) {
      return;
    }

    global $wp_admin_bar, $wp_force_ssl_tests;

    $test_results = $wp_force_ssl_tests->count_statuses();
    $plugin_name = __('WP Force SSL', 'wp-force-ssl');
    $plugin_logo = esc_url($this->plugin_url) . 'img/wp-force-ssl-icon.png';

    if (
      'yes' != $this->options['wpfs_adminbar_menu'] ||
      false === current_user_can('administrator') ||
      false === apply_filters('wp_force_ssl_show_admin_bar', true)
    ) {
      return;
    }

    $title = '<div class="wfssl-adminbar-icon" style="display:inline-block;"><img style="height: 22px; padding: 4px; margin-bottom: -10px;  filter: invert(1) brightness(1.2) grayscale(1);" src="' . $plugin_logo . '" alt="' . $plugin_name . '" title="' . $plugin_name . '"></div> <span class="ab-label">' . $plugin_name . '</span>';
    if ($test_results['fail']) {
      $title .= sprintf(' <span class="wfssl-failed-tests awaiting-mod" style="display: inline-block;vertical-align: top;box-sizing: border-box;margin: 1px 0 -1px 2px; padding: 0 5px; min-width: 18px;height: 18px; border-radius: 9px;background-color: #d63638; color: #fff; font-size: 11px; line-height: 1.6; text-align: center; z-index: 26;vertical-align: text-bottom;">%d</span>', $test_results['fail']);
    }

    $wp_admin_bar->add_node(array(
      'id'    => 'wfssl-ab',
      'title' => $title,
      'href'  => admin_url('options-general.php?page=wpfs-settings'),
      'parent' => '',
    ));

    $wp_admin_bar->add_node(array(
      'id'    => 'wfssl-status',
      'title' => 'Status',
      'href'  => admin_url('options-general.php?page=wpfs-settings#tab_status'),
      'parent' => 'wfssl-ab'
    ));

    $wp_admin_bar->add_node(array(
      'id'    => 'wfssl-settings',
      'title' => 'Settings',
      'href'  => admin_url('options-general.php?page=wpfs-settings#tab_settings'),
      'parent' => 'wfssl-ab'
    ));

    $wp_admin_bar->add_node(array(
      'id'    => 'wfssl-scanner',
      'title' => 'Content Scanner',
      'href'  => admin_url('options-general.php?page=wpfs-settings#tab_scanner'),
      'parent' => 'wfssl-ab'
    ));

    $wp_admin_bar->add_node(array(
      'id'    => 'wfssl-snapshots',
      'title' => 'SSL Certificate',
      'href'  => admin_url('options-general.php?page=wpfs-settings#tab_ssl'),
      'parent' => 'wfssl-ab'
    ));
  } // admin_bar


  // add widget to dashboard
  function add_widget()
  {
    if (current_user_can('administrator') && $this->options['wpfs_dashboard_widget'] == 'yes') {
      add_meta_box('wpfssl_status', 'WP Force SSL Status', array($this, 'widget_content'), 'dashboard', 'side', 'high');
    }
  } // add_widget


  // render dashboard widget
  function widget_content()
  {
    global $wp_force_ssl_tests;
    $widget_html = '';

    $tests = $wp_force_ssl_tests->get_tests_results(false);
    if (is_wp_error($tests)) {
      wpForceSSL_Utility::wp_kses_wf('<p>Unable to get test results. Sorry :(<br>Open <a href="' . admin_url('options-general.php?page=wpfs-settings#tab_status') . '">WP Force SSL</a> plugin to contact support.</p>');
      return;
    }

    $widget_html .= '<style>';
    $widget_html .= '#wpfssl_status .inside { padding: 0; margin: 0; }';
    $widget_html .= '#wpfssl_status table td { max-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; border-top: 1px solid #f0f0f1; }';
    $widget_html .= '#wpfssl_status table { border: none; }';
    $widget_html .= '#wpfssl_status td .wpfssl-show-hover { color: #dcdcde; display: none; }';
    $widget_html .= '#wpfssl_status td:hover .wpfssl-show-hover { display: inline; }';
    $widget_html .= '#wpfssl_status p { padding: 0 12px 12px 12px; }';
    $widget_html .= '#wpfssl_status .wpfssl-status-fail { border-left: 5px solid #d63638; }';
    $widget_html .= '#wpfssl_status .wpfssl-status-warning { border-left: 5px solid #ffaa39; }';
    $widget_html .= '#wpfssl_status .wpfssl-status-pass { border-left: 5px solid #42972d; }';
    $widget_html .= '</style>';

    $widget_html .= '<table class="striped widefat">';
    for ($i = 0; $i < 7; $i++) {
      $widget_html .= '<tr>';
      $widget_html .= '<td class="wpfssl-status-' . $tests[$i]['status'] . '">' . $tests[$i]['title'] . '<span class="wpfssl-show-hover"> | <a href="' . admin_url('options-general.php?page=wpfs-settings#tab_status') . '">View Details</a></span></td>';
      $widget_html .= '</tr>';
    } // for
    $widget_html .= '</table>';

    $widget_html .= '<p>View all <b>' . sizeof($tests) . ' tests</b>, detailed test explanations, and how to fix problems in the <a href="' . admin_url('options-general.php?page=wpfs-settings#tab_status') . '">WP Force SSL</a> plugin settings.</p>';

    wpForceSSL_Utility::wp_kses_wf($widget_html);
  } // widget_content


  // fix for aggressive plugins that include their CSS on all pages
  function cleanup_enqueues()
  {
    if (!$this->is_plugin_page()) {
      return;
    }

    wp_dequeue_style('uiStyleSheet');
    wp_dequeue_style('wpcufpnAdmin');
    wp_dequeue_style('unifStyleSheet');
    wp_dequeue_style('wpcufpn_codemirror');
    wp_dequeue_style('wpcufpn_codemirrorTheme');
    wp_dequeue_style('collapse-admin-css');
    wp_dequeue_style('jquery-ui-css');
    wp_dequeue_style('tribe-common-admin');
    wp_dequeue_style('file-manager__jquery-ui-css');
    wp_dequeue_style('file-manager__jquery-ui-css-theme');
    wp_dequeue_style('wpmegmaps-jqueryui');
    wp_dequeue_style('wp-botwatch-css');
    wp_dequeue_style('njt-filebird-admin');
    wp_dequeue_style('ihc_jquery-ui.min.css');
    wp_dequeue_style('badgeos-juqery-autocomplete-css');
    wp_dequeue_style('mainwp');
    wp_dequeue_style('mainwp-responsive-layouts');
    wp_dequeue_style('jquery-ui-style');
    wp_dequeue_style('additional_style');
    wp_dequeue_style('wobd-jqueryui-style');
    wp_dequeue_style('wpdp-style3');
    wp_dequeue_style('jquery_smoothness_ui');
    wp_dequeue_style('uap_main_admin_style');
    wp_dequeue_style('uap_font_awesome');
    wp_dequeue_style('uap_jquery-ui.min.css');
  } // cleanup_enqueues


  /**
   * Add links to plugin's description in plugins table
   *
   * @param array  $links  Initial list of links.
   * @param string $file   Basename of current plugin.
   *
   * @return array
   */
  function plugin_meta_links($links, $file)
  {
    if ($file !== plugin_basename(__FILE__)) {
      return $links;
    }

    $support_link = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-force-ssl/" title="Get help">Support</a>';
    $home_link = '<a target="_blank" href="https://wpforcessl.com/?ref=wpfssl-free" title="Plugin Homepage">Plugin Homepage</a>';

    $links[] = $support_link;
    $links[] = $home_link;

    return $links;
  } // plugin_meta_links


  /**
   * Add action links to plugins table, left part
   *
   * @param array  $links  Initial list of links.
   *
   * @return array
   */
  function plugin_action_links($links)
  {
    $settings_link = '<a href="' . admin_url('options-general.php?page=wpfs-settings') . '" title="Configure SSL settings">Configure SSL</a>';
    $buy_link = '<a href="' . admin_url('options-general.php?page=wpfs-settings#open-pro-dialog') . '" title="Buy the PRO version"><b>Buy PRO</b></a>';

    array_unshift($links, $buy_link);
    array_unshift($links, $settings_link);

    return $links;
  } // plugin_action_links


  /**
   * Remove all WP notices on WPSSL page
   *
   * @return null
   */
  function remove_admin_notices()
  {
    if (!$this->is_plugin_page()) {
      return false;
    }

    global $wp_filter;
    unset($wp_filter['user_admin_notices'], $wp_filter['admin_notices']);
  } // remove_admin_notices


  /**
   * Main function for displaying plugin's admin page
   *
   * @return null
   */
  function settings_page_content()
  {
    // double check for admin privileges
    if (!current_user_can('administrator')) {
      wp_die('Sorry, you are not allowed to access this page.');
    }

    echo '<header>';
    echo '<img id="logo-icon" src="' . esc_url($this->plugin_url) . 'img/wp-force-ssl-logo.png" title="WP Force SSL" alt="WP Force SSL" />';
    echo '</header>';

    echo '<div id="wfssl-notifications">';
    echo '</div>';

    echo '<div class="wfssl-container-left">';
    echo '<div id="wfssl-tabs" class="ui-tabs" style="display: none;">';
    $tabs = array();

    $tabs[] = array('id' => 'tab_status', 'class' => 'wfssl-tab', 'label' => 'Status', 'callback' => 'tab_status');
    $tabs[] = array('id' => 'tab_settings', 'class' => 'wfssl-tab', 'label' => 'Settings', 'callback' => 'tab_settings');
    $tabs[] = array('id' => 'tab_scanner', 'class' => 'wfssl-tab', 'label' => 'Content Scanner', 'callback' => 'tab_scanner');
    $tabs[] = array('id' => 'tab_ssl', 'class' => 'wfssl-tab', 'label' => 'SSL Certificate', 'callback' => 'tab_ssl');
    $tabs[] = array('id' => 'tab_support', 'class' => 'wfssl-tab', 'label' => 'Support', 'callback' => 'tab_support');
    $tabs[] = array('id' => 'tab_pro', 'class' => 'wfssl-tab wfssl-tab-pro', 'label' => 'PRO', 'callback' => '');

    echo '<nav>';
    echo '<div class="wfssl-container">';
    echo '<ul class="wfssl-main-tab">';
    foreach ($tabs as $tab) {
      echo '<li id="button-' . esc_attr($tab['id']) . '" class="' . esc_attr($tab['class']) . '"><a href="#' . esc_attr($tab['id']) . '">' . esc_attr($tab['label']) . '</a></li>';
    }
    echo '</ul>';
    echo '</div>'; // container
    echo '</nav>';

    // tabs
    echo '<div class="wfssl-container">';

    foreach ($tabs as $tab) {
      if (is_callable(array($this, $tab['callback']))) {
        echo '<div id="' . esc_attr($tab['id']) . '" class="wfssl-tab-content">';
        call_user_func(array($this, $tab['callback']));
        echo '</div>';
      }
    }

    echo '</div>'; // wfssl-container
    echo '</div>';
    echo '</div>'; // wfssl-container-left

    echo '<div class="wfssl-container wfssl-container-right">';
    echo '<div class="sidebar-box pro-ad-box">
            <p class="text-center"><a href="https://wpforcessl.com/?ref=wpfssl-free-sidebar-box" target="_blank">
            <img src="' . esc_url($this->plugin_url) . 'img/wp-force-ssl-logo.png" alt="WP Force SSL PRO" title="WP Force SSL PRO"></a><br><b>PRO version</b> is here! Grab the launch discount - <b>all prices are LIFETIME!</b></p>
            <ul class="plain-list">
                <li>15+ Options to Fine-Tune Your SSL</li>
                <li>Mixed Content Scanner + Fixer</li>
                <li>Real-Time SSL &amp; Site monitoring (via our SaaS)</li>
                <li>Generate &amp; Install Free SSL Certificate</li>
                <li>Licenses &amp; Sites Manager (remote SaaS dashboard)</li>
                <li>White-label Mode + Complete Codeless Plugin Rebranding</li>
                <li>Email support from plugin developers</li>
            </ul>

            <p class="text-center"><a href="#" class="open-pro-dialog button button-buy" data-pro-feature="sidebar-box">Get PRO Now</a></p>
            </div>';
    echo '<div class="sidebar-box" style="margin-top: 35px;">
    <p>Please <a href="https://wordpress.org/support/plugin/wp-force-ssl/reviews/#new-post" target="_blank">rate the plugin ★★★★★</a> to <b>keep it up-to-date &amp; maintained</b>. It only takes a second to rate. Thank you! 👋</p>
    </div>';
    echo '</div>'; // wfssl-container-right

    wpForceSSL_Utility::wp_kses_wf($this->pro_dialog());
  } // settings_page_content


  function tab_support()
  {
    echo '<div style="overflow: auto;">
                    <div class="wfssl-box">
                        <h3>Support</h3>
                        <p>Support for the free version is available only via the <a href="https://wordpress.org/support/plugin/wp-force-ssl/" target="_blank">WP repo support forum</a>. If you need email support please <a href="#" class="open-pro-dialog" data-pro-feature="support">upgrade to PRO</a>.</p>';
    echo '</div>';
    echo '</div>';
    echo '<h3>FAQ</h3>
                    <div class="wfssl-accordion-wfssl-accordion-tabs">
                        <div class="wfssl-accordion-tab">
                            <input type="checkbox" id="faq1">
                            <label class="wfssl-accordion-tab-label wfssl-fs-18 wfssl-font" for="faq1">Will WP Force SSL slow down my site?</label>
                            <div class="wfssl-accordion-tab-content">
                            Absolutely not. Everything the plugin does happens in the admin. Nothing is loaded, added, or processed on the front-end so you can rest assured there is no impact on the performance of your site.
                            </div>
                        </div>

                        <div class="wfssl-accordion-tab">
                            <input type="checkbox" id="faq8">
                            <label class="wfssl-accordion-tab-label wfssl-fs-18 wfssl-font" for="faq8">I just moved my site to another address, will WP Force SSL help?</label>
                            <div class="wfssl-accordion-tab-content">
                            Definitely! Especially if you moved from HTTP to HTTPS. The plugin will make sure to properly redirect all your content, check your SSL certificate, and add other security features.
                            </div>
                        </div>

                        <div class="wfssl-accordion-tab d-none">
                            <input type="checkbox" id="faq2">
                            <label class="wfssl-accordion-tab-label wfssl-fs-18 wfssl-font" for="faq2">Can you install an SSL certificate for me?</label>
                            <div class="wfssl-accordion-tab-content">
                            Sorry, at the moment we can\'t. The automatic SSL certificate installation feature is on our to-do and will be available in one of the future versions.
                            </div>
                        </div>

                        <div class="wfssl-accordion-tab">
                            <input type="checkbox" id="faq11">
                            <label class="wfssl-accordion-tab-label wfssl-fs-18 wfssl-font" for="faq11">What are the requirements for running SSL on a site?</label>
                            <div class="wfssl-accordion-tab-content">There are many but a short answer is "A valid SSL certificate". Please head over to the <a href="#" data-tab="0" class="change-tab">Status</a> tab and see the results. The tests were made specifically to see if a site is ready for SSL.
                            </div>
                        </div>

                        <div class="wfssl-accordion-tab">
                            <input type="checkbox" id="faq4">
                            <label class="wfssl-accordion-tab-label wfssl-fs-18 wfssl-font" for="faq4">Is WP Force SSL dangerous for my site?</label>
                            <div class="wfssl-accordion-tab-content">
                            No, definitely not! The plugin does not make any permanent changes to your site so even if it comes to a worst-case scenario you can just disable the plugin and that will undo all changes.
                            </div>
                        </div>

                        <div class="wfssl-accordion-tab d-none">
                            <input type="checkbox" id="faq5">
                            <label class="wfssl-accordion-tab-label wfssl-fs-18 wfssl-font" for="faq5">Can you generate/get an SSL certificate for me?</label>
                            <div class="wfssl-accordion-tab-content">
                            At the moment no. Sorry. We are already working on a feature that will automatically get a certificate from Let\'s Encrypt and install it on your site but it\'s not ready yet.
                            </div>
                        </div>

                        <div class="wfssl-accordion-tab">
                            <input type="checkbox" id="faq7">
                            <label class="wfssl-accordion-tab-label wfssl-fs-18 wfssl-font" for="faq7">Will WP Force SSL modify my files, database or any content?</label>
                            <div class="wfssl-accordion-tab-content">
                            It will not automatically modify anything. If anything needs permanent changes you\'ll be prompted to double-confirm the change. However, on 90% of sites, all changes are done on the fly so they are not permanent. Disabling the plugin undoes all changes.
                            </div>
                        </div>

                        <div class="wfssl-accordion-tab">
                            <input type="checkbox" id="faq6">
                            <label class="wfssl-accordion-tab-label wfssl-fs-18 wfssl-font" for="faq6">When I buy PRO can I manage my licenses and change sites?</label>
                            <div class="wfssl-accordion-tab-content">
                            Certainly! Purchases, sites, licenses & SSL monitors are managed in the <a href="https://dashboard.wpforcessl.com/" target="_blank">WP Force SSL Dashboard</a>. It\'s a central place to manage all your sites. You can move the licenses (change sites/domains) as much as you need. There are no limits.
                            </div>
                        </div>
                    </div>';
  } // tab_support


  function tab_status()
  {
    echo '<div id="status_progress_wrapper" class="wfssl-progress" style="display:none;">
              <div id="status_progress" class="bar orange" style="width:0%">
                  <div id="status_progress_text" class="wfssl-progress-text"></div>
              </div>
            </div>

            <div id="status_tasks" class="wfssl-labels" style="display:none;">
              <div class="status-tasks status-tasks-selected">All tasks</div>
              <div class="status-tasks-remaining">Remaining tasks</div>
            </div>

            <div id="test-results-wrapper">
              <div class="loading-wrapper">
                  <img class="wfssl_flicker" src="' . esc_url($this->plugin_url) . 'img/wp-force-ssl-icon.png' . '" alt="Loading. Please wait." title="Loading. Please wait.">
                  <p>Loading. Please wait.</p>
              </div>
            </div>';
    echo '<div class="button button-primary run-tests" style="float: right; display:none;">Run Tests Again</div>';
  } // tab_status


  function tab_settings()
  {
    echo '<form id="wpfs_form"><table class="form-table" id="settings-table">';

    echo '<tr><td>
        <label for="wpfs_ssl">Redirect HTTP requests to HTTPS</label>
        <small>Visitors will be automatically redirected from HTTP to HTTPS for all pages, posts and other WP content. The 301 redirect status is used. Files that are outside of WP, or accessed directly will still be served via HTTP.</small>
    </td><td>';
    wpForceSSL_Utility::create_toogle_switch('wpfs_ssl', array('saved_value' => ($this->options['wpfs_ssl'] == 'yes' ? true : false)));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="fix_frontend_mixed_content_fixer">Fix mixed content on frontend<span class="pro-feature open-pro-dialog">PRO</span></label>
            <small>Fix mixed content on the frontend on the fly (files and/or database is not changed) by replacing <code>http://</code> with <code>https://</code> for linked resources.</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('fix_frontend_mixed_content_fixer', array('class' => 'open-pro-dialog', 'saved_value' => false));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="fix_backend_mixed_content_fixer">Fix mixed content on backend (in WP admin)<span class="pro-feature open-pro-dialog">PRO</span></label>
            <small>Fix mixed content in the backend (WP admin) on the fly (files and/or database is not changed) by replacing <code>http://</code> with <code>https://</code> for linked resources</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('fix_backend_mixed_content_fixer', array('class' => 'open-pro-dialog', 'saved_value' => false));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="wpfs_hsts">Enable HSTS</label>
            <small>HSTS (HTTP Strict Transport Security) is a header sent by your website to your visitors\' browser telling it to only use HTTPS to connect to the website. If someone tried to perform a man-in-the-middle attack and redirect the visitor to their own malicious version of the domain the browser will refuse to load the website via HTTP and force them to show a valid SSL certificate for the domain, which they will not have and thus the attack will fail.</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('wpfs_hsts', array('saved_value' => ($this->options['wpfs_hsts'] == 'yes' ? true : false)));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="force_secure_cookies">Force Secure Cookies<span class="pro-feature open-pro-dialog">PRO</span></label>
            <small>Cookies are small packets of data stored on your computer by the websites you visit so it remembers information like your logged in state. Most times this information is sensitive, so you should enable this option to harden the way cookies are exchanged with by your browser and to prevent anyone else from reading them.</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('force_secure_cookies', array('class' => 'open-pro-dialog', 'saved_value' => false));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="htaccess_301_redirect">Redirect HTTP to HTTPS requests via .htaccess<span class="pro-feature open-pro-dialog">PRO</span></label>
            <small>Redirect all <code>http://</code> requests to <code>https://</code> via .htaccess as soon as the request is received. This is slighly faster than PHP redirect but if your server does not use .htaccess you can use the PHP redirect option too.</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('htaccess_301_redirect', array('class' => 'open-pro-dialog', 'saved_value' => false));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="xss_protection">Cross-site scripting (X-XSS) protection<span class="pro-feature open-pro-dialog">PRO</span></label>
            <small>Protects your site from cross-site scripting attacks. If a cross-site scripting attack is detected, the browser will automatically block those requests.</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('xss_protection', array('class' => 'open-pro-dialog', 'saved_value' => false));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="x_content_options">X-Content-Type Options<span class="pro-feature open-pro-dialog">PRO</span></label>
            <small>This header prevents MIME-sniffing, which is used to disguise the content type of malicious files being uploaded to the website.</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('x_content_options', array('class' => 'open-pro-dialog', 'saved_value' => false));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="referrer_policy">Referrer Policy<span class="pro-feature open-pro-dialog">PRO</span></label>
            <small>To prevent data leakage, only send referrer information when navigating to the same protocol (HTTPS -&gt; HTTPS) and not when downgrading (HTTPS -&gt; HTTP).</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('referrer_policy', array('class' => 'open-pro-dialog', 'saved_value' => false));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="wpfs_expect_ct">Expect CT</label>
            <small>Enables the Expect-CT header, requesting that the browser check that the certificate for that site appears in public CT logs.</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('wpfs_expect_ct', array('saved_value' => ($this->options['wpfs_expect_ct'] == 'yes' ? true : false)));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="x_frame_options">X-Frame Options<span class="pro-feature open-pro-dialog">PRO</span></label>
            <small>This header prevents your site from being loaded in an iFrame on other domains. This is used to prevent clickjacking attacks. Be sure to enable this option!</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('x_frame_options', array('class' => 'open-pro-dialog', 'saved_value' => false));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="permissions_policy">Permissions Policy<span class="pro-feature open-pro-dialog">PRO</span></label>
            <small>The Permissions Policy allows you to specify which browser resources to allow on your site (i.e. microphone, webcam, etc.).</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('permissions_policy', array('class' => 'open-pro-dialog', 'saved_value' => false));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="wpfs_adminbar_menu">Show WP Force SSL menu to administrators in admin bar</label>
            <small>When enabled an extra menu will be shown to admins in the admin topbar. Don\'t forget to reload the page after saving to see the change.</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('wpfs_adminbar_menu', array('saved_value' => ($this->options['wpfs_adminbar_menu'] == 'yes' ? true : false)));
    echo '</td></tr>';

    echo '<tr><td>
            <label for="wpfs_dashboard_widget">Show WP Force SSL widget to administrators in admin dashboard</label>
            <small>When enabled a widget with status details will be shown to admins in the admin dashboard.</small>
        </td><td>';
    wpForceSSL_Utility::create_toogle_switch('wpfs_dashboard_widget', array('saved_value' => ($this->options['wpfs_dashboard_widget'] == 'yes' ? true : false)));
    echo '</td></tr>';

    echo '</table></form>';

    echo '<p><a href="#" class="button button-primary save-ssl-options">' . __('Save Settings', 'wp-force-ssl') . '</a></p>';
  } //tab_settings


  function tab_scanner()
  {
    echo '<div id="scanner_progress_wrapper" class="wfssl-progress" style="display:none;">
                <div id="scanner_progress" class="bar">
                    <div id="scanner_progress_text" class="wfssl-progress-text"></div>
                </div>
              </div>';
    $posts = get_posts(array('post_type' => get_post_types(array('public' => true)), 'numberposts' => 1000));
    echo '<div class="scanner-stats">Total pages to scan: ' . esc_html(number_format(count($posts))) . '</div>';
    echo '<a href="#" class="open-pro-dialog" data-pro-feature="content-scanner"><img class="scanner-preview" src="' . esc_url($this->plugin_url . 'img/content-scanner.jpg') . '" /></a>';

    echo '<div id="start-scanner" data-pro-feature="start-scanning" class="button button-primary open-pro-dialog">Start Scanning</div>';
  } //tab_scanner


  function tab_ssl()
  {
    echo '<h2>SSL Certificate Information</h2>';
    echo '<div id="ssl_cert_details" class="wfssl-box">';
    echo 'Loading certificate information ...<span class="wfssl-green wfssl_rotating dashicons dashicons-update"></span>';
    echo '</div>';
    echo '<div class="clear"></div>';

    echo '<h2>Real-Time SSL &amp; Site Monitoring<span class="pro-feature">PRO</span></h2>';
    echo '<div id="wfssl_cert_email" class="wfssl-box">';
    echo '<span class="wfssl-red dashicons dashicons-dismiss"></span>';
    echo '<p>Real-time site &amp; SSL certificate monitoring is a <a href="#" data-pro-feature="ssl-monitor-text" class="open-pro-dialog">PRO</a> feature powered by our SaaS that will help you and your clients sleep better. It monitors over 20 common errors related to SSL that can happen at any time. Forgot to renew your certificate? No problem, you\'ll get notified on time. Forgot to change certificate after switching the domain or server? Again, not a problem. You\'ll get notified.</p>';
    echo '<label for="ssl-monitor-toggle">Enable Monitoring</label> &nbsp; ';
    wpForceSSL_Utility::create_toogle_switch('ssl-monitor-toggle', array('class' => 'open-pro-dialog', 'saved_value' => false));
    echo '<div id="wfssl-cert-expiration-email-box">';
    echo '<input id="cert_expiration_email" name="cert_expiration_email" class="wfssl-cert-expiration-email-input open-pro-dialog" data-pro-feature="ssl-monitor-email" type="text" placeholder="Email for notifications" value="" style="height:32px; width:353px; background-color: #fff; margin-bottom: 20px;"><br>';
    echo '<div class="button button-primary open-pro-dialog" data-pro-feature="ssl-monitor-save">Save &amp; Enable Monitoring</div>';
    echo '</div>';
    echo '</div>';

    echo '<div class="clear"></div>';
    echo '<h2>Generate Free SSL Certificate<span class="pro-feature">PRO</span></h2>';
    echo '<div id="wfssl_cert_generate" class="wfssl-box">';
    echo '<p>Use this <a href="#" data-pro-feature="generate-ssl-cert-text" class="open-pro-dialog">PRO</a> tool to generate a free Let\'s Encrypt certificate. The certificate will be renewed automatically as long as WP Force SSL is active on the website. All you need is an email address, no other personal information or payment is necessary.</p>';
    echo '<div class="button button-primary open-pro-dialog" data-pro-feature="generate-ssl-cert">Generate Free SSL Certificate</div>';
    echo '</div>';
  } // tab_ssl


  /**
   * Perform the redirect to HTTPS if loaded over HTTP
   *
   * @since 1.5
   *
   * @return null
   *
   */
  public function wpfs_core()
  {
    if (!is_ssl()) {
      wp_safe_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
      exit();
    }
  } // wpfs_core


  /**
   * Check if minimal WP version required by WP Force SSL is used
   *
   * @since 1.5
   *
   * @return bool
   *
   */
  public function check_wp_version($min_version)
  {
    if (!version_compare(get_bloginfo('version'), $min_version,  '>=')) {
      add_action('admin_notices', array($this, 'notice_min_wp_version'));
      return false;
    } else {
      return true;
    }
  } // check_wp_version


  /**
   * Display error message if WP version is too low
   *
   * @since 1.5
   *
   * @return null
   *
   */
  public function notice_min_wp_version()
  {
    echo '<div class="error"><p>' . sprintf(__('WP Force SSL plugin <b>requires WordPress version 4.6</b> or higher to function properly. You are using WordPress version %s. Please <a href="%s">update it</a>.', 'wp-force-ssl'), get_bloginfo('version'), admin_url('update-core.php')) . '</p></div>';
  } // notice_min_wp_version_error


  /**
   * Send the HTTP Strict Transport Security (HSTS) header.
   *
   * @since 1.5
   *
   * @return null
   */
  public function to_strict_transport_security()
  {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
  } // to_strict_transport_security


  /**
   * Send the Expect CT header.
   *
   * @since 1.5
   *
   * @return null
   */
  public function enable_expect_ct()
  {
    header('Expect-CT: max-age=5184000, enforce');
  } // enable_expect_ct


  /**
   * Save plugin settings received via AJAX
   *
   * @since 1.5
   *
   * @return null
   */
  public function ajax_save_setting()
  {
    check_ajax_referer('save_settting_nonce_action');

    $wpfs_ssl = isset($_POST['wpfs_ssl']) ? 'yes' : 'no';
    $wpfs_hsts = isset($_POST['wpfs_hsts']) ? 'yes' : 'no';
    $wpfs_expect_ct = isset($_POST['wpfs_expect_ct']) ? 'yes' : 'no';
    $wpfs_adminbar_menu = isset($_POST['wpfs_adminbar_menu']) ? 'yes' : 'no';
    $wpfs_dashboard_widget = isset($_POST['wpfs_dashboard_widget']) ? 'yes' : 'no';

    $wpfs_settings = array(
      'wpfs_ssl' => $wpfs_ssl,
      'wpfs_hsts' => $wpfs_hsts,
      'wpfs_expect_ct' => $wpfs_expect_ct,
      'wpfs_adminbar_menu' => $wpfs_adminbar_menu,
      'wpfs_dashboard_widget' => $wpfs_dashboard_widget
    );
    update_option(WPFSSL_OPTIONS_KEY, $wpfs_settings);

    wp_send_json_success();
  } // ajax_save_setting


  /**
   * Check SSL Certificate by performing a request to home_url over https and send back a json response
   *
   * @since 1.5
   *
   * @return null
   */
  public function ajax_check_ssl()
  {
    global $wp_force_ssl_tests;

    check_ajax_referer('test_ssl_nonce_action');

    if (isset($_REQUEST['force']) && (bool)$_REQUEST['force'] === true) {
      $nocache = true;
    } else {
      $nocache = false;
    }
    $status = $wp_force_ssl_tests->get_ssl_status($nocache);

    wp_send_json_success($status);
  } // check_ssl

  /**
   * Check if currently on WP Force SSL settings page
   *
   * @since 1.5
   *
   * @return bool is on WP Force SSL settings page
   */
  public function is_plugin_page()
  {
    $current_screen = get_current_screen();
    if ($current_screen->id === 'settings_page_wpfs-settings') {
      return true;
    } else {
      return false;
    }
  } // is_plugin_page


  /**
   * Change admin footer text to show plugin information
   *
   * @since 1.5
   *
   * @param string $text_org original footer text
   *
   * @return string footer text html
   */
  public function admin_footer_text($text_org)
  {
    if (false === $this->is_plugin_page()) {
      return $text_org;
    }

    $text = '<i><a target="_blank" href="https://wpforcessl.com/?ref=wpfssl-free">WP Force SSL</a> v' . $this->version . ' by <a href="https://www.webfactoryltd.com/" title="' . __('Visit our site to get more great plugins', 'wp-force-ssl') . '" target="_blank">WebFactory Ltd</a>.';
    $text .= ' Please <a target="_blank" href="https://wordpress.org/support/plugin/wp-force-ssl/reviews/#new-post" title="' . __('Rate the plugin', 'wp-force-ssl') . '">' . __('Rate the plugin ★★★★★', 'wp-force-ssl') . '</a>.</i> ';
    return $text;
  } // admin_footer_text


  function pro_dialog()
  {
    $out = '';

    $out .= '<div id="wpfssl-pro-dialog" style="display: none;" title="WP Force SSL PRO is here!"><span class="ui-helper-hidden-accessible"><input type="text"/></span>';

    $out .= '<div class="center logo"><a href="https://wpforcessl.com/?ref=wpfssl-free-pricing-table" target="_blank"><img src="' . $this->plugin_url . 'img/wp-force-ssl-logo.png' . '" alt="WP Force SSL PRO" title="WP Force SSL PRO"></a><br>';

    $out .= '<span>Limited PRO Launch Discount - <b>all prices are LIFETIME</b>! Pay once &amp; use forever!</span>';
    $out .= '</div>';

    $out .= '<table id="wpfssl-pro-table">';
    $out .= '<tr>';
    $out .= '<td class="center">Lifetime Personal License</td>';
    $out .= '<td class="center">Lifetime Team License</td>';
    $out .= '<td class="center">Lifetime Agency License</td>';
    $out .= '</tr>';

    $out .= '<tr class="prices">';
    $out .= '<td class="center"><del>$49 /year</del><br><span>$59</span> <b>/lifetime</b></td>';
    $out .= '<td class="center"><del>$89 /year</del><br><span>$89</span> <b>/lifetime</b></td>';
    $out .= '<td class="center"><del>$199 /year</del><br><span>$119</span> <b>/lifetime</b></td>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td><span class="dashicons dashicons-yes"></span><b>1 Site License</b></td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span><b>5 Sites License</b></td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span><b>100 Sites License</b></td>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>All Plugin Features</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>All Plugin Features</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>All Plugin Features</td>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Lifetime Updates &amp; Support</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Lifetime Updates &amp; Support</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Lifetime Updates &amp; Support</td>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Content Scanner</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Content Scanner</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Content Scanner</td>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Real-Time SSL &amp; Site Monitoring</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Real-Time SSL &amp; Site Monitoring</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Real-Time SSL &amp; Site Monitoring</td>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Free SSL Certificate Generating</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Free SSL Certificate Generating</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Free SSL Certificate Generating</td>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td><span class="dashicons dashicons-no"></span>Licenses &amp; Sites Manager</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Licenses &amp; Sites Manager</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Licenses &amp; Sites Manager</td>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td><span class="dashicons dashicons-no"></span>White-label Mode</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>White-label Mode</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>White-label Mode</td>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td><span class="dashicons dashicons-no"></span>Full Plugin Rebranding</td>';
    $out .= '<td><span class="dashicons dashicons-no"></span>Full Plugin Rebranding</td>';
    $out .= '<td><span class="dashicons dashicons-yes"></span>Full Plugin Rebranding</td>';
    $out .= '</tr>';

    $out .= '<tr>';
    $out .= '<td><a class="button button-buy" data-href-org="https://wpforcessl.com/buy/?product=personal-launch&ref=pricing-table" href="https://wpforcessl.com/buy/?product=personal-launch&ref=pricing-table" target="_blank">Lifetime License<br>$59 -&gt; BUY NOW</a>
    <br>or <a class="button-buy" data-href-org="https://wpforcessl.com/buy/?product=personal-monthly&ref=pricing-table" href="https://wpforcessl.com/buy/?product=personal-monthly&ref=pricing-table" target="_blank">only $6.99 <small>/month</small></a></td>';
    $out .= '<td><a class="button button-buy" data-href-org="https://wpforcessl.com/buy/?product=team-launch&ref=pricing-table" href="https://wpforcessl.com/buy/?product=team-launch&ref=pricing-table" target="_blank">Lifetime License<br>$69 -&gt; BUY NOW</a></td>';
    $out .= '<td><a class="button button-buy" data-href-org="https://wpforcessl.com/buy/?product=agency-launch&ref=pricing-table" href="https://wpforcessl.com/buy/?product=agency-launch&ref=pricing-table" target="_blank">Lifetime License<br>$119 -&gt; BUY NOW</a></td>';
    $out .= '</tr>';

    $out .= '</table>';

    $out .= '<div class="center footer"><b>100% No-Risk Money Back Guarantee!</b> If you don\'t like the plugin over the next 7 days, we will happily refund 100% of your money. No questions asked! Payments are processed by our merchant of records - <a href="https://paddle.com/" target="_blank">Paddle</a>.</div></div>';

    return $out;
  } // pro_dialog


  /**
   * Clean-up on delete
   *
   * @since 1.5
   *
   * @return null
   */
  public static function uninstall()
  {
    delete_option(WPFSSL_OPTIONS_KEY);
    delete_option(WPFSSL_META_KEY);
  } // uninstall


  /**
   * Reset on deactivate
   *
   * @return null
   */
  public static function deactivate()
  {
    $meta = get_option(WPFSSL_META_KEY, array());
    $meta['hide_welcome_pointer'] = false;
    update_option(WPFSSL_META_KEY, $meta);
  } // deactivate


  /**
   * Disabled; we use singleton pattern so magic functions need to be disabled.
   *
   * @since 1.5
   *
   * @return null
   */
  private function __clone()
  {
  }


  /**
   * Disabled; we use singleton pattern so magic functions need to be disabled.
   *
   * @since 1.5
   *
   * @return null
   */
  public function __sleep()
  {
  }


  /**
   * Disabled; we use singleton pattern so magic functions need to be disabled.
   *
   * @since 1.5
   *
   * @return null
   */
  public function __wakeup()
  {
  }
  // end class
}


$wpfs = wpForceSSL::get_instance();
add_action('plugins_loaded', array($wpfs, 'plugins_loaded'));
register_deactivation_hook(__FILE__, array('wpForceSSL', 'deactivate'));
register_uninstall_hook(__FILE__, array('wpForceSSL', 'uninstall'));
