<?php
/**
 * WP Force SSL
 * https://wpforcessl.com/
 * (c) WebFactory Ltd, 2019 - 2022
 */

// include only file
if (!defined('ABSPATH')) {
  die('Do not open this file directly.');
}

class wpForceSSL_status_tests
{
  var $tests_cache_hours = 12;
  var $ssl_status_cache_hours = 24;
  var $ssl_expiry_days_limit = 26;

  function get_tests()
  {
    $tests = array();

    $tests['localhost'] = array(
      'callback' => 'localhost',
      'description' => '',
      'output' => array(
        'pass' => array(
          'title' => 'The site is publicly available (not on a localhost)',
          'description' => 'In order to issue a properly signed SSL certificate the site needs to be publicly available.',
        ),
        'warning' => array(
          'title' => 'The site is NOT publicly available. It\'s on a localhost.',
          'description' => 'There is nothing wrong with running a site on localhost. However, some WP Force SSL functions are not available for localhost. It\'s also not possible to issue SSL certificates for localhosts except self-signed ones.'
        ),
      )
    );

    $tests['checkssl'] = array(
      'callback' => 'checkssl',
      'description' => '',
      'output' => array(
        'pass' => array(
          'title' => 'Your SSL certificate is valid',
          'description' => 'Having a valid certificate is the first and most important step to having a secure site.',
        ),
        'fail' => array(
          'title' => 'Your SSL certificate is NOT valid',
          'description' => 'While testing your SSL certificate the following error occurred: %1$s<br>Upgrade to <a href="#" data-pro-feature="checkssl-test-gen-cert" class="open-pro-dialog">PRO</a> to generate a free SSL certificate that automatically renews.'
        ),
      )
    );

    $tests['pluginver'] = array(
      'callback' => 'pluginver',
      'description' => '',
      'output' => array(
        'pass' => array(
          'title' => 'You\'re using the latest version of WP Force SSL',
          'description' => 'Using the latest version of any plugin ensures you have the newest features and bug fixes.'
        ),
        'fail' => array(
          'title' => 'You\'re NOT using the latest version of WP Force SSL',
          'description' => 'Please <a href="' . admin_url('update-core.php') . '">update</a> to the latest version to enjoy the benefits of new features and bug fixes.'
        ),
      )
    );

    $tests['conflictingplugins'] = array(
      'callback' => 'conflictingplugins',
      'description' => '',
      'output' => array(
        'pass' => array(
          'title' => 'You\'re not using any plugins that conflict with WP Force SSL',
          'description' => 'It\'s always a good idea to use only one plugin to solve a problem. Not multiple ones that will cause conflicts.'
        ),
        'fail' => array(
          'title' => 'You\'re using %1$s plugin(s) that conflict with WP Force SSL',
          'description' => 'Please <a href="' . admin_url('plugins.php?plugin_status=active') . '">disable</a> the following plugin(s) to ensure there are no conflicts: %2$s.'
        ),
      )
    );

    $tests['wpurl'] = array(
      'callback' => 'wpurl',
      'description' => '',
      'output' => array(
        'pass' => array(
          'title' => 'WordPress address URL is properly configured for HTTPS',
          'description' => 'Prefix for the WordPress address URL should be <i>https://</i>'
        ),
        'fail' => array(
          'title' => 'WordPress address URL is NOT properly configured',
          'description' => 'WordPress address URL is configured with HTTP instead of HTTPS. Please change the URL in <a href="' . admin_url('options-general.php') . '">Settings - General</a>.'
        ),
      )
    );

    $tests['url'] = array(
      'callback' => 'url',
      'description' => '',
      'output' => array(
        'pass' => array(
          'title' => 'Site address URL is properly configured for HTTPS',
          'description' => 'Prefix for the site address URL should be <i>https://</i>'
        ),
        'fail' => array(
          'title' => 'Site address URL is NOT properly configured',
          'description' => 'Site address URL is configured with HTTP instead of HTTPS. Please change the URL in <a href="' . admin_url('options-general.php') . '">Settings - General</a>.'
        ),
      )
    );

    $tests['sslexpiry'] = array(
      'callback' => 'sslexpiry',
      'description' => '',
      'output' => array(
        'pass' => array(
          'title' => 'Your SSL certificate will expire in %1$s days. No need to renew it yet.',
          'description' => 'Having a valid certificate is the first and most important step to having a secure site.',
        ),
        'warning' => array(
          'title' => 'Your SSL certificate will expire in %1$s days. Renew it as soon as possible.',
          'description' => 'It\'s not smart to renew the certificate at the last minute. We recommend renewing it at least 15 days before it expires.',
        ),
        'fail' => array(
          'title' => '%1$s',
          'description' => 'Check your certificate manually immediately.',
        ),
      )
    );

    $tests['sslmonitoring'] = array(
      'callback' => 'sslmonitoring',
      'description' => '',
      'output' => array(
        'warning' => array(
          'title' => 'Realtime SSL monitoring is disabled',
          'description' => 'Real-time site and SSL certificate monitoring is a <a href="#" data-pro-feature="status-sslmonitoring-1" class="open-pro-dialog">PRO</a> feature that will help you and your clients sleep better. It monitors over 20 common errors related to SSL that can happen at any time. Forgot to renew your certificate? No problem, you\'ll get notified on time.<br><a href="#" data-pro-feature="status-sslmonitoring-2" class="open-pro-dialog">Enable real-time SSL monitoring</a>'
        ),
      )
    );

    $tests['httpsredirectwp'] = array(
      'callback' => 'httpsredirectwp',
      'description' => '',
      'output' => array(
        'pass' => array(
          'title' => 'WordPress URLs\' are properly redirected from HTTP to HTTPS',
          'description' => 'URLs like <code>http://site.com/page/</code> are automatically redirected to <code>https://site.com/test/</code>.',
        ),
        'fail' => array(
          'title' => 'WordPress URLs\' are NOT properly redirected from HTTP to HTTPS',
          'description' => 'While testing the redirect the following error occurred: %1$s'
        ),
      )
    );

    $tests['httpsredirectfile'] = array(
      'callback' => 'httpsredirectfile',
      'description' => '',
      'output' => array(
        'warning' => array(
          'title' => 'Non-WordPress URLs\' may not be properly redirected from HTTP to HTTPS',
          'description' => 'Redirecting non-WP URLs requires a special <code>.htaccess</code> rule to be in place and is a <a href="#" data-pro-feature="status-httpsredirectfile-1" class="open-pro-dialog">PRO</a> feature. If you have files outside WP such as PDFs or images, and you can\'t modify links to them to be HTTPS, then you need this feature.<br><a href="#" data-pro-feature="status-httpsredirectfile-2" class="open-pro-dialog">Enable redirecting for non-WP URLs</a>'
        ),
      )
    );

    $tests['hsts'] = array(
      'callback' => 'hsts',
      'description' => '',
      'output' => array(
        'pass' => array(
          'title' => 'HTTP Strict Transport Security (HSTS) is enabled',
          'description' => 'HSTS is a policy mechanism that helps protect websites against man-in-the-middle attacks such as protocol downgrade attacks and cookie hijacking. It allows web servers to declare that web browsers should automatically interact with it using only HTTPS connections.',
        ),
        'fail' => array(
          'title' => 'HTTP Strict Transport Security (HSTS) is NOT enabled',
          'description' => 'HSTS is a policy mechanism that helps protect websites against man-in-the-middle attacks such as protocol downgrade attacks and cookie hijacking. It allows web servers to declare that web browsers should automatically interact with it using only HTTPS connections. <a href="#" class="change-tab" data-tab="1">Enable HSTS</a> in Settings.',
        ),
      )
    );

    $tests['contentscanner'] = array(
      'callback' => 'contentscanner',
      'description' => '',
      'output' => array(
        'warning' => array(
          'title' => 'Content scanner was never run, mixed-content issues might be present',
          'description' => 'Content scanner is a <a href="#" data-pro-feature="status-contentscanner-1" class="open-pro-dialog">PRO</a> feature powered by our SaaS that ensures your site doesn\'t have any mixed content issues. It scans every page, post and custom post type and checks every link, image, CSS and JS file to verify everything is loaded via HTTPS. What it does in minutes is impossible to do manually.<br><a href="#" data-pro-feature="status-contentscanner-2" class="open-pro-dialog">Scan your content for mixed-content issues</a>'
        ),
      )
    );

    $tests['htaccess'] = array(
      'callback' => 'htaccess',
      'description' => '',
      'output' => array(
        'pass' => array(
          'title' => 'Your server uses .htaccess and it\'s writable',
          'description' => 'If needed the plugin will add redirect rules to the <code>.htaccess</code> file.',
        ),
        'warning' => array(
          'title' => 'Your server uses .htaccess but it\'s NOT writable',
          'description' => 'This is not a problem and it\'s a good idea to have <code>.htaccess</code> write-protected. However, the plugin won\'t be able to automatically add redirect rules to it.',
        ),
        'fail' => array(
          'title' => 'Your server doesn\'t use htaccess',
          'description' => 'There\'s nothing wrong with running a server setup that does\'t use <code>.htaccess</code>. However, the plugin won\'t be able to add redirect rules which might be required.',
        ),
      )
    );

    $tests['404home'] = array(
      'callback' => '404home',
      'description' => '',
      'output' => array(
        'warning' => array(
          'title' => 'Could not test if your 404 errors are redirected to the home page',
          'description' => 'This is a <a href="#" data-pro-feature="status-404home-1" class="open-pro-dialog">PRO</a> feature that checks if your 404 errors (pages) are redirected to the home page. It is not recommended by search engines to redirect all 404 pages to a single page. Instead, a 404 response code should be returned.<br><a href="#" data-pro-feature="status-404home-2" class="open-pro-dialog">Check 404 errors redirect</a>'
        ),
      )
    );

    return $tests;
  } // get_tests


  private function process_tests()
  {
    $results = array();

    wpForceSSL_Utility::clear_3rd_party_cache();

    $tests = $this->get_tests();

    foreach ($tests as $test_name => $test_details) {
      if ($test_name[0] == '_') {
        continue;
      }

      $result = call_user_func(array($this, 'test_' . $test_details['callback']));
      if (is_bool($result)) {
        if ($result === true) {
          $result = array('status' => 'pass', 'data' => array());
        } else {
          $result = array('status' => 'fail', 'data' => array());
        }
      }
      if (!isset($result['data'])) {
        $result['data'] = array();
      }
      if (isset($result['data']) && !is_array($result['data'])) {
        $result['data'] = array($result['data']);
      }

      $result['status'] = strtolower($result['status']);
      if ($result['status'] != 'pass' && $result['status'] != 'warning' && $result['status'] != 'fail') {
        user_error('Unknown test status result (' . esc_attr($result['status']) . ') for ' . esc_attr($test_name), E_USER_ERROR);
        die();
      }

      $tmp = $test_details['output'][$result['status']];
      $tmp = array_merge(array('title' => '', 'description' => ''), $tmp);

      $results[] = array(
        'test' => $test_name,
        'status' => $result['status'],
        'title' => $this->sprintfn($tmp['title'], $result['data']),
        'description' => $this->sprintfn($tmp['description'], $result['data']),
      );
    } // foreach $tests

    usort($results, function ($a, $b) {
      $values = array('pass' => 1, 'warning' => 2, 'fail' => 3);

      if ($values[$a['status']] == $values[$b['status']]) {
        return 0;
      }
      return ($values[$a['status']] < $values[$b['status']]) ? 1 : -1;
    });

    set_transient('wpssl_tests_results', $results, $this->tests_cache_hours * HOUR_IN_SECONDS);
    return $results;
  } // process_tests


  function count_statuses()
  {
    $out = array('pass' => 0, 'warning' => 0, 'fail' => 0);
    $results = $this->get_tests_results();

    foreach ($results as $result) {
      $out[$result['status']]++;
    } // foreach $results

    return $out;
  } // count_statuses


  /**
   * Version of sprintf with named arguments
   *
   * with sprintf: sprintf('second: %2$s ; first: %1$s', '1st', '2nd');
   *
   * with sprintfn: sprintfn('second: %second$s ; first: %first$s', array(
   *  'first' => '1st',
   *  'second'=> '2nd'
   * ));
   *
   * @param string $format sprintf format string, with any number of named arguments
   * @param array $args array of [ 'arg_name' => 'arg value', ... ] replacements to be made
   * @return string|false result of sprintf call, or bool false on error
   */
  function sprintfn($format, array $args = array())
  {
    // map of argument names to their corresponding sprintf numeric argument value
    $arg_nums = array_slice(array_flip(array_keys(array(0 => 0) + $args)), 1);

    // find the next named argument. each search starts at the end of the previous replacement.
    for ($pos = 0; preg_match('/(?<=%)([a-zA-Z_]\w*)(?=\$)/', $format, $match, PREG_OFFSET_CAPTURE, $pos);) {
      $arg_pos = $match[0][1];
      $arg_len = strlen($match[0][0]);
      $arg_key = $match[1][0];

      // no value named argument
      if (!array_key_exists($arg_key, $arg_nums)) {
        user_error("sprintfn(): Missing argument '" . esc_attr($arg_key) . "'", E_USER_WARNING);
        return false;
      }

      // replace the named argument with the corresponding numeric one
      $format = substr_replace($format, $replace = $arg_nums[$arg_key], $arg_pos, $arg_len);
      $pos = $arg_pos + strlen($replace); // skip to end of replacement for next iteration
    }

    return vsprintf($format, array_values($args));
  } // sprintfn


  function get_tests_results($skip_cache = false)
  {
    if ($skip_cache || !($results = get_transient('wpssl_tests_results'))) {
      $results = $this->process_tests();
    }

    return $results;
  } // get_tests_results

  function is_localhost()
  {
    if (
      substr($_SERVER['SERVER_ADDR'], 0, 6) == '127.0.'
      || substr($_SERVER['SERVER_ADDR'], 0, 8) == '192.168.'
      || @$_SERVER['HTTP_HOST'] == 'localhost'
      || $_SERVER['SERVER_ADDR'] == '::1'
    ) {
      return true;
    }

    $ssl_status = $this->get_ssl_status(false);
    if ($ssl_status['error']) {
      if (stripos($ssl_status['data'], 'unable to retrieve') !== false || stripos($ssl_status['data'], 'unable to resolve') !== false) {
        return true;
      }
    }

    return false;
  } // is_localhost

  function get_ssl_status($skip_cache = false)
  {
    if ($skip_cache || !($status = get_transient('wpfs_ssl_status'))) {
      $domain = get_bloginfo('url');

      $result = array('error' => true, 'data' => '', 'domain' => '');

      if (strpos($domain, ':') !== false) {
        $domain = parse_url($domain, PHP_URL_HOST);
      }

      if (empty($domain)) {
        $result = array('error' => true, 'domain' => $domain, 'data' => 'Invalid domain name.');
      }

      if (gethostbyname($domain) == $domain) {
        $result = array('error' => true, 'domain' => $domain, 'data' => 'Unable to resolve domain name.');
      }

      $response = $this->do_request('https://' . $domain);
      if (!$response || !is_array($response)) {
        $result = array('domain' => $domain, 'error' => true, 'data' => 'Unable to test SSL certificate. Assume it\'s NOT valid.');
      } elseif (!empty($response['error'])) {
        $err = $response['error'];
        $err = trim(substr($err, strpos($err, ':') + 1));
        $err = trim(str_replace(array('SSL:', 'SSL certificate problem:'), '', $err));

        $result = array('domain' => $domain, 'error' => true, 'data' => $err);
      } elseif ($response['code'] == 200) {
        $g = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);
        $r = stream_socket_client("ssl://{$domain}:443", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $g);
        if (!$r) {
          $result = array('domain' => $domain, 'error' => true, 'data' => 'Unknown error while fetching SSL certificate. Assuming it\'s NOT valid.');
        } else {
          $cont = stream_context_get_params($r);
          $tmp = openssl_x509_parse($cont['options']['ssl']['peer_certificate']);
          $data = array();

          $data['valid_from'] = date('Y-m-d', (int) $tmp['validFrom_time_t']);
          $data['valid_to'] = date('Y-m-d', (int) $tmp['validTo_time_t']);
          $data['issuer'] = implode(', ', array_reverse($tmp['issuer']));
          $data['issued_to'] = implode(', ', array_reverse($tmp['subject']));

          $d = str_ireplace('dns:', '', $tmp['extensions']['subjectAltName']);
          $d = explode(',', $d);
          $d = array_map('trim', $d);

          $data['issued_to_hosts'] = $d;

          $result = array('domain' => $domain, 'error' => false, 'data' => $data);
        }
      } else {
        $result = array('domain' => $domain, 'error' => true, 'data' => 'A valid SSL certificate was NOT found.');
      }

      $status = array('error' => $result['error'], 'data' => $result['data']);
      set_transient('wpfs_ssl_status', $status, $this->ssl_status_cache_hours * HOUR_IN_SECONDS);
    }

    return $status;
  } // get_ssl_status

  function do_request($url)
  {
    $args = array(
      'timeout' => 10,
      'httpversion' => '1.1',
      'sslverify' => true
    );

    $response = wp_remote_get($url, $args);

    $error = '';
    if (is_wp_error($response)) {
      $error = $response->get_error_message();
    }

    $out  = array(
      'code' => wp_remote_retrieve_response_code($response),
      'body' => wp_remote_retrieve_body($response),
      'error' => $error
    );

    return $out;
  } // make_request

  function test_rand()
  {
    $rand = rand(0, 100);

    if ($rand > 66) {
      return array('status' => 'pass', 'data' => $rand);
    } elseif ($rand > 33) {
      return array('status' => 'warning', 'data' => $rand);
    } else {
      return array('status' => 'fail', 'data' => $rand);
    }
  } // test_rand


  function test_wpurl()
  {
    $tmp = get_bloginfo('wpurl');
    if (stripos($tmp, 'https://') === 0) {
      return true;
    } else {
      return false;
    }
  } // test_wpurl


  function test_url()
  {
    $tmp = get_bloginfo('url');
    if (stripos($tmp, 'https://') === 0) {
      return true;
    } else {
      return false;
    }
  } // test_url


  function test_checkssl()
  {
    $ssl_status = $this->get_ssl_status();
    if ($ssl_status['error']) {
      return array('status' => 'fail', 'data' => $ssl_status['data']);
    } else {
      return true;
    }
  } // test_checkssl


  function test_localhost()
  {
    if ($this->is_localhost()) {
      return array('status' => 'warning', 'data' => '');
    } else {
      return true;
    }
  } // test_localhost


  function test_pluginver()
  {
    global $wpfs;
    $updates = get_site_transient('update_plugins');

    if (isset($updates->response['wp-force-ssl/wp-force-ssl.php']) && version_compare($wpfs->version, $updates->response['wp-force-ssl/wp-force-ssl.php']->new_version, '<')) {
      return false;
    } else {
      return true;
    }
  } // test_pluginver

  function test_conflictingplugins()
  {
    $plugins = array();

    if (defined('WPLE_BASE')) {
      $plugins[] = 'WP Encryption';
    }
    if (defined('WPSSL_VER')) {
      $plugins[] = 'WP Free SSL';
    }
    if (defined('SSL_ZEN_PLUGIN_VERSION')) {
      $plugins[] = 'SSL Zen';
    }
    if (defined('WPSSL_VER')) {
      $plugins[] = 'WP Free SSL';
    }
    if (defined('SSLFIX_PLUGIN_VERSION')) {
      $plugins[] = 'SSL Insecure Content Fixer';
    }
    if (class_exists('OCSSL', false)) {
      $plugins[] = 'One Click SSL';
    }
    if (class_exists('JSM_Force_SSL', false)) {
      $plugins[] = 'JSM\'s Force HTTP to HTTPS (SSL)';
    }
    if (function_exists('httpsrdrctn_plugin_init')) {
      $plugins[] = 'Easy HTTPS (SSL) Redirection';
    }
    if (defined('WPSSL_VER')) {
      $plugins[] = 'WP Free SSL';
    }
    if (class_exists('REALLY_SIMPLE_SSL')) {
      $plugins[] = 'Really Simple SSL';
    }
    if (defined('ESSL_REQUIRED_PHP_VERSION')) {
      $plugins[] = 'EasySSL';
    }
    if (class_exists('ICWP_Cloudflare_Flexible_SSL')) {
      $plugins[] = 'Flexible SSL for CloudFlare';
    }

    if ($plugins) {
      return array('status' => 'fail', 'data' => array(sizeof($plugins), implode(', ', $plugins)));
    } else {
      return true;
    }
  } // test_conflictingplugins


  function test_sslexpiry()
  {
    $ssl_status = $this->get_ssl_status();

    if ($ssl_status['error']) {
      return array('status' => 'fail', 'data' => 'Unable to test your SSL certificate\'s expiry date');
    } else {
      $days_valid = round((strtotime($ssl_status['data']['valid_to']) - time()) / DAY_IN_SECONDS);
      if ($days_valid <= 1) {
        return array('status' => 'fail', 'data' => 'Your SSL certificate has expired! Please renew it immediately.');
      } elseif ($days_valid <= $this->ssl_expiry_days_limit) {
        return array('status' => 'warning', 'data' => $days_valid);
      } else {
        return array('status' => 'pass', 'data' => $days_valid);
      }
    }
  } // test_sslexpiry


  function test_sslmonitoring()
  {
    return array('status' => 'warning', 'data' => '');
  } // test_sslmonitoring


  function test_httpsredirectwp()
  {
    $query = new WP_Query(array('orderby' => 'rand', 'post_status' => 'publish', 'posts_per_page' => '1'));
    if (!$query->posts) {
      $query = new WP_Query(array('orderby' => 'rand', 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => '1'));
    }
    $query->the_post();
    $url = get_the_permalink();
    wp_reset_postdata();

    if (!$url) {
      $url = get_bloginfo('url');
    }
    $url = str_replace('https://', 'http://', $url);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_exec($ch);

    if (curl_errno($ch)) {
      $error_msg = curl_error($ch);
      return array('status' => 'fail', 'data' => $error_msg);
    }
    $target = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    if (substr($target, 0, 8) == 'https://') {
      return true;
    } else {
      return array('status' => 'fail', 'data' => 'URL was not redirected to an HTTPS URL. ' . $url . ' -&gt; ' . $target);
    }
  } // test_httpsredirectwp


  function test_httpsredirectfile()
  {
    return array('status' => 'warning', 'data' => '');
  } // test_httpsredirectfile


  function test_hsts()
  {
    global $wpfs;
    if ($wpfs->options['wpfs_hsts'] == 'yes') {
      return true;
    } else {
      return false;
    }
  } // test_hsts

  function test_contentscanner()
  {
    return array('status' => 'warning', 'data' => '');
  } // test_contentscanner


  function test_htaccess()
  {
    $server = strtolower(filter_var($_SERVER['SERVER_SOFTWARE'], FILTER_SANITIZE_STRING));

    if (stripos($server, 'apache') === false && stripos($server, 'litespeed') === false) {
      return false;
    }

    if (!is_writable(get_home_path() . '.htaccess')) {
      return array('status' => 'warning');
    }

    return true;
  } // test_htaccess


  function test_404home()
  {
    return array('status' => 'warning', 'data' => '');
  } // test_404home
} // class wpForceSSL_status_tests

global $wp_force_ssl_tests;
$wp_force_ssl_tests = new wpForceSSL_status_tests();
