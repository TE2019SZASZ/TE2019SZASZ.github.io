<?php
/*
 * WP Force SSL
 * Utility & Helper functions
 * (c) WebFactory Ltd, 2019 - 2022
 */

// include only file
if (!defined('ABSPATH')) {
  die('Do not open this file directly.');
}

class wpForceSSL_Utility
{
  /**
   * Creates a fancy, iOS alike toggle switch
   *
   * @param string $name ID used for checkbox.
   * @param array $options Various options: value, saved_value, option_key, class
   * @param boolean $echo Default: true.
   * @return void
   */
  static function create_toogle_switch($name, $options = array(), $echo = true)
  {
    $default_options = array('value' => '1', 'saved_value' => '', 'option_key' => $name, 'class' => '');
    $options = array_merge($default_options, $options);

    $extra = '';
    if (strpos($options['class'], 'open-pro-dialog') !== false) {
      $extra = 'data-pro-feature="' . $name . '" ';
    }

    $out = '<label ' . $extra . 'for="' . $name . '" class="wfssl-switch tooltip ' . $options['class'] . '">';
    $out .= '<input type="checkbox" id="' . $name . '" ' . self::checked($options['value'], $options['saved_value']) . ' type="checkbox" value="' . $options['value'] . '" name="' . $options['option_key'] . '">';
    $out .= '<span class="wfssl-slider wfssl-round"></span>';
    $out .= '</label>';

    if ($echo) {
      wpForceSSL_Utility::wp_kses_wf($out);
    } else {
      return $out;
    }
  } // create_toggle_switch


  /**
   * Helper for creating checkboxes.
   *
   * @param string $value Checkbox value, in HTML.
   * @param array $current Current, saved value of checkbox.
   * @param boolean $echo Default: false.
   *
   * @return void|string
   */
  static function checked($value, $current, $echo = false)
  {
    $out = '';

    if (!is_array($current)) {
      $current = (array) $current;
    }

    if (in_array($value, $current)) {
      $out = ' checked="checked" ';
    }

    if ($echo) {
      wpForceSSL_Utility::wp_kses_wf($out);
    } else {
      return $out;
    }
  } // checked

  static function wp_kses_wf($html)
  {
    add_filter('safe_style_css', function ($styles) {
      $styles_wf = array(
        'text-align',
        'margin',
        'color',
        'float',
        'border',
        'background',
        'background-color',
        'border-bottom',
        'border-bottom-color',
        'border-bottom-style',
        'border-bottom-width',
        'border-collapse',
        'border-color',
        'border-left',
        'border-left-color',
        'border-left-style',
        'border-left-width',
        'border-right',
        'border-right-color',
        'border-right-style',
        'border-right-width',
        'border-spacing',
        'border-style',
        'border-top',
        'border-top-color',
        'border-top-style',
        'border-top-width',
        'border-width',
        'caption-side',
        'clear',
        'cursor',
        'direction',
        'font',
        'font-family',
        'font-size',
        'font-style',
        'font-variant',
        'font-weight',
        'height',
        'letter-spacing',
        'line-height',
        'margin-bottom',
        'margin-left',
        'margin-right',
        'margin-top',
        'overflow',
        'padding',
        'padding-bottom',
        'padding-left',
        'padding-right',
        'padding-top',
        'text-decoration',
        'text-indent',
        'vertical-align',
        'width',
        'display',
      );

      foreach ($styles_wf as $style_wf) {
        $styles[] = $style_wf;
      }
      return $styles;
    });

    $allowed_tags = wp_kses_allowed_html('post');
    $allowed_tags['input'] = array(
      'type' => true,
      'style' => true,
      'class' => true,
      'id' => true,
      'checked' => true,
      'disabled' => true,
      'name' => true,
      'size' => true,
      'placeholder' => true,
      'value' => true,
      'data-*' => true,
      'size' => true,
      'disabled' => true
    );

    $allowed_tags['textarea'] = array(
      'type' => true,
      'style' => true,
      'class' => true,
      'id' => true,
      'checked' => true,
      'disabled' => true,
      'name' => true,
      'size' => true,
      'placeholder' => true,
      'value' => true,
      'data-*' => true,
      'cols' => true,
      'rows' => true,
      'disabled' => true
    );

    $allowed_tags['select'] = array(
      'type' => true,
      'style' => true,
      'class' => true,
      'id' => true,
      'checked' => true,
      'disabled' => true,
      'name' => true,
      'size' => true,
      'placeholder' => true,
      'value' => true,
      'data-*' => true,
      'multiple' => true,
      'disabled' => true
    );

    $allowed_tags['option'] = array(
      'type' => true,
      'style' => true,
      'class' => true,
      'id' => true,
      'checked' => true,
      'disabled' => true,
      'name' => true,
      'size' => true,
      'placeholder' => true,
      'value' => true,
      'selected' => true,
      'data-*' => true
    );
    $allowed_tags['optgroup'] = array(
      'type' => true,
      'style' => true,
      'class' => true,
      'id' => true,
      'checked' => true,
      'disabled' => true,
      'name' => true,
      'size' => true,
      'placeholder' => true,
      'value' => true,
      'selected' => true,
      'data-*' => true,
      'label' => true
    );

    $allowed_tags['a'] = array(
      'href' => true,
      'data-*' => true,
      'class' => true,
      'style' => true,
      'id' => true,
      'target' => true,
      'data-*' => true,
      'role' => true,
      'aria-controls' => true,
      'aria-selected' => true,
      'disabled' => true
    );

    $allowed_tags['div'] = array(
      'style' => true,
      'class' => true,
      'id' => true,
      'data-*' => true,
      'role' => true,
      'aria-labelledby' => true,
      'value' => true,
      'aria-modal' => true,
      'tabindex' => true
    );

    $allowed_tags['li'] = array(
      'style' => true,
      'class' => true,
      'id' => true,
      'data-*' => true,
      'role' => true,
      'aria-labelledby' => true,
      'value' => true,
      'aria-modal' => true,
      'tabindex' => true
    );

    $allowed_tags['span'] = array(
      'style' => true,
      'class' => true,
      'id' => true,
      'data-*' => true,
      'aria-hidden' => true
    );

    $allowed_tags['style'] = array(
      'class' => true,
      'id' => true,
      'type' => true
    );

    $allowed_tags['form'] = array(
      'style' => true,
      'class' => true,
      'id' => true,
      'method' => true,
      'action' => true,
      'data-*' => true
    );

    echo wp_kses($html, $allowed_tags);

    add_filter('safe_style_css', function ($styles) {
      $styles_wf = array(
        'text-align',
        'margin',
        'color',
        'float',
        'border',
        'background',
        'background-color',
        'border-bottom',
        'border-bottom-color',
        'border-bottom-style',
        'border-bottom-width',
        'border-collapse',
        'border-color',
        'border-left',
        'border-left-color',
        'border-left-style',
        'border-left-width',
        'border-right',
        'border-right-color',
        'border-right-style',
        'border-right-width',
        'border-spacing',
        'border-style',
        'border-top',
        'border-top-color',
        'border-top-style',
        'border-top-width',
        'border-width',
        'caption-side',
        'clear',
        'cursor',
        'direction',
        'font',
        'font-family',
        'font-size',
        'font-style',
        'font-variant',
        'font-weight',
        'height',
        'letter-spacing',
        'line-height',
        'margin-bottom',
        'margin-left',
        'margin-right',
        'margin-top',
        'overflow',
        'padding',
        'padding-bottom',
        'padding-left',
        'padding-right',
        'padding-top',
        'text-decoration',
        'text-indent',
        'vertical-align',
        'width'
      );

      foreach ($styles_wf as $style_wf) {
        if (($key = array_search($style_wf, $styles)) !== false) {
          unset($styles[$key]);
        }
      }
      return $styles;
    });
  }


  static function clear_3rd_party_cache()
  {
    wp_cache_flush();

    if (function_exists('rocket_clean_domain')) {
      rocket_clean_domain();
    }

    if (function_exists('w3tc_pgcache_flush')) {
      w3tc_pgcache_flush();
    }

    if (function_exists('wpfc_clear_all_cache')) {
      wpfc_clear_all_cache();
    }
    if (function_exists('w3tc_flush_all')) {
      w3tc_flush_all();
    }
    if (function_exists('wp_cache_clear_cache')) {
      wp_cache_clear_cache();
    }
    if (method_exists('LiteSpeed_Cache_API', 'purge_all')) {
      LiteSpeed_Cache_API::purge_all();
    }
    if (class_exists('Endurance_Page_Cache')) {
      $epc = new Endurance_Page_Cache;
      $epc->purge_all();
    }
    if (class_exists('SG_CachePress_Supercacher') && method_exists('SG_CachePress_Supercacher', 'purge_cache')) {
      SG_CachePress_Supercacher::purge_cache(true);
    }
    if (class_exists('SiteGround_Optimizer\Supercacher\Supercacher')) {
      SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
    }
    if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
      $GLOBALS['wp_fastest_cache']->deleteCache(true);
    }
    if (is_callable(array('Swift_Performance_Cache', 'clear_all_cache'))) {
      Swift_Performance_Cache::clear_all_cache();
    }
    if (is_callable(array('Hummingbird\WP_Hummingbird', 'flush_cache'))) {
      Hummingbird\WP_Hummingbird::flush_cache(true, false);
    }
  }
} // wpForceSSL_Utility
