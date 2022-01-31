/*
 * WP Force SSL
 * (c) WebFactory Ltd 2019 - 2022
 */

(function ($) {
  $('#wfssl-tabs')
    .tabs({
      create: function () {
        if (window.location.hash && window.location.hash != '#' && window.location.hash != '#open-pro-dialog') {
          $('#wfssl-tabs').tabs(
            'option',
            'active',
            $('a[href="' + location.hash + '"]')
              .parent()
              .index()
          );
          window.location.hash = '';
        }
      },
      beforeActivate: function (event, ui) {
        if (ui.newTab.hasClass('wfssl-tab-pro')) {
          return false;
        }
      },
      activate: function (event, ui) {
        localStorage.setItem('wfssl-tabs', $('#wfssl-tabs').tabs('option', 'active'));
      },
      active: localStorage.getItem('wfssl-tabs') || 0,
    })
    .show();

  $(window).on('hashchange', function () {
    $('#wfssl-tabs').tabs(
      'option',
      'active',
      $('a[href="' + location.hash + '"]')
        .parent()
        .index()
    );
  });

  // helper for switching tabs & linking anchors in different tabs
  $('.settings_page_wpfs-settings').on('click', '.change-tab', function (e) {
    e.preventDefault();
    $('#wfssl-tabs').tabs('option', 'active', $(this).data('tab'));

    // get the link anchor and scroll to it
    target = this.href.split('#')[1];
    if (target) {
      $.scrollTo('#' + target, 500, { offset: { top: -50, left: 0 } });
    }

    $(this).blur();
    return false;
  }); // jump to tab/anchor helper

  // helper for scrolling to anchor
  $('.settings_page_wpfs-settings').on('click', '.scrollto', function (e) {
    e.preventDefault();

    // get the link anchor and scroll to it
    target = this.href.split('#')[1];
    if (target) {
      $.scrollTo('#' + target, 500, { offset: { top: -50, left: 0 } });
    }

    $(this).blur();
    return false;
  }); // scroll to anchor helper

  // display a loading message while an action is performed
  function block_ui(message) {
    tmp = swal({
      text: message,
      type: false,
      imageUrl: wpfs.loading_icon_url,
      onOpen: () => {
        $(swal.getImage()).addClass('wfssl_flicker');
      },
      heightAuto: false,
      imageWidth: 100,
      imageHeight: 100,
      imageAlt: message,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      showConfirmButton: false,
      width: 600,
    });

    return tmp;
  } // block_ui

  // test SSL certificate
  $('.wpfs_test_ssl').on('click', function (e) {
    e.preventDefault();

    var _ajax_nonce = wpfs.nonce_test_ssl;
    var action = 'wpfs_test_ssl';
    var form_data = '_ajax_nonce=' + _ajax_nonce + '&action=' + action;

    block_ui(wpfs.testing);

    $.post({
      url: wpfs.ajaxurl,
      data: form_data,
    })

      .always(function (data) {
        swal.close();
      })

      .done(function (result) {
        if (typeof result.success != 'undefined' && result.success) {
          jQuery.get(wpfs.home_url).always(function (data, text, xhr) {
            wphe_changed = false;

            if (xhr.status.substr(0, 1) != '2') {
              swal({ type: 'error', heightAuto: false, title: wpfs.undocumented_error });
            } else {
              swal({
                type: 'success',
                heightAuto: false,
                title: wpfs.test_success,
                html: result.data,
              });
            }
          });
        } else if (typeof result.success != 'undefined' && !result.success) {
          swal({ heightAuto: false, type: 'error', title: wpfs.test_failed, html: result.data });
        } else {
          swal({ heightAuto: false, type: 'error', title: wpfs.undocumented_error });
        }
      })

      .fail(function (data) {
        if (data.data) {
          swal({
            type: 'error',
            heightAuto: false,
            title: wpfs.documented_error + ' ' + data.data,
          });
        } else {
          swal({ heightAuto: false, type: 'error', title: wpfs.undocumented_error });
        }
      });

    return false;
  }); // test SSL certificate

  // save settings
  $('.settings_page_wpfs-settings').on('click', '.save-ssl-options', function (e) {
    e.preventDefault();

    var _ajax_nonce = wpfs.nonce_save_settings;
    var action = 'wpfs_save_settting';
    var form_data = $('#wpfs_form').serialize() + '&_ajax_nonce=' + _ajax_nonce + '&action=' + action;

    block_ui(wpfs.saving);

    $.post({
      url: wpfs.ajaxurl,
      data: form_data,
    })
      .always(function (data) {
        swal.close();
      })
      .done(function (result) {
        if (typeof result.success != 'undefined' && result.success) {
          load_test_results(true);
          swal({
            type: 'success',
            heightAuto: false,
            title: wpfs.save_success,
            showConfirmButton: false,
            timer: 1400,
          });
        } else if (typeof result.success != 'undefined' && !result.success) {
          swal({ heightAuto: false, type: 'error', title: result.data });
        } else {
          swal({ heightAuto: false, type: 'error', title: wpfs.undocumented_error });
        }
      })
      .fail(function (data) {
        if (data.data) {
          swal({
            type: 'error',
            heightAuto: false,
            title: wpfs.documented_error + ' ' + data.data,
          });
        } else {
          swal({ heightAuto: false, type: 'error', title: wpfs.undocumented_error });
        }
      });

    return false;
  });

  load_test_results(false);

  $('.settings_page_wpfs-settings').on('click', '.run-tests', function () {
    load_test_results(true);
  });

  function load_test_results(force) {
    $('#status_progress_wrapper').hide();
    $('.run-tests').hide();
    $('#status_tasks').hide();
    $('#test-results-wrapper').html(
      '<div class="loading-wrapper"><img class="wfssl_flicker" src="' +
        wpfs.loading_icon_url +
        '" alt="Loading. Please wait." title="Loading. Please wait."><p>Loading. Please wait.</p></div>'
    );

    $.ajax({
      url: ajaxurl,
      data: {
        action: 'wpfs_run_tests',
        _ajax_nonce: wpfs.nonce_run_tests,
        force: force,
      },
    })
      .done(function (data) {
        if (data.success) {
          tests_total = 0;
          tests_pass = 0;
          tests_fail = 0;
          tests_warning = 0;
          tests_results = data.data;
          tests_results_html = '<table class="form-table">';
          for (test in tests_results) {
            tests_total++;

            tests_results_html += '<tr data-status="' + tests_results[test].status + '"><td>';

            switch (tests_results[test].status) {
              case 'fail':
                tests_results_html += '<div class="wfssl-badge wfssl-badge-red" title="Test failed">failed</div>';
                tests_fail++;
                break;
              case 'warning':
                tests_results_html += '<div class="wfssl-badge wfssl-badge-yellow" title="Test warning">warning</div>';
                tests_warning++;
                break;
              case 'pass':
                tests_results_html += '<div class="wfssl-badge wfssl-badge-green" title="Test passed">passed</div>';
                tests_pass++;
                break;
            }
            tests_results_html += '</td>';
            tests_results_html +=
              '<td>' + tests_results[test].title + '<br /><small>' + tests_results[test].description + '</small></td>';
            tests_results_html += '</tr>';
          }

          tests_results_html += '</table>';
          var progress = Math.floor(((tests_warning + tests_pass) / tests_total) * 100);
          $('#status_progress').css('width', progress + '%');
          $('#status_progress_text').html(progress + '%');
          $('#wfssl-failed-tests').html(tests_fail);
          $('#status_progress_wrapper').show();
          $('#status_tasks').html(
            '<div class="status-tasks status-tasks-selected" data-tasks="all">All Tests (' +
              tests_total +
              ')</div><div class="status-tasks" data-tasks="pass">Passed (' +
              tests_pass +
              ')</div><div class="status-tasks" data-tasks="warning">Need Attention (' +
              tests_warning +
              ')</div><div class="status-tasks" data-tasks="fail">Failed (' +
              tests_fail +
              ')</div>'
          );
          $('#status_tasks').show();
          $('#test-results-wrapper').html(tests_results_html);
          $('.run-tests').show();
        } else {
          swal.fire({
            type: 'error',
            title: wpfs.undocumented_error,
          });
        }
      })
      .fail(function (data) {
        swal.fire({
          type: 'error',
          title: wpfs.undocumented_error,
        });
      });
  }

  $('.settings_page_wpfs-settings').on('click', '.status-tasks', function (e) {
    $('.status-tasks').removeClass('status-tasks-selected');
    $(this).addClass('status-tasks-selected');
    var test_status = $(this).data('tasks');
    if (test_status == 'all') {
      $('tr[data-status="pass"]').show();
      $('tr[data-status="warning"]').show();
      $('tr[data-status="fail"]').show();
    } else if (test_status == 'pass') {
      $('tr[data-status="pass"]').show();
      $('tr[data-status="warning"]').hide();
      $('tr[data-status="fail"]').hide();
    } else if (test_status == 'warning') {
      $('tr[data-status="pass"]').hide();
      $('tr[data-status="warning"]').show();
      $('tr[data-status="fail"]').hide();
    } else if (test_status == 'fail') {
      $('tr[data-status="pass"]').hide();
      $('tr[data-status="warning"]').hide();
      $('tr[data-status="fail"]').show();
    }
  });

  // load SSL Certificate info
  load_ssl_cert_info();

  $('.settings_page_wpfs-settings').on('click', '.refresh-certificate-info', function () {
    $('#ssl_cert_details').html(
      'Loading certificate information ... <span class="wfssl-green wfssl_rotating dashicons dashicons-update"></span>'
    );
    load_ssl_cert_info(true);
  });

  function load_ssl_cert_info(force) {
    $.ajax({
      url: ajaxurl,
      data: {
        action: 'wpfs_test_ssl',
        _ajax_nonce: wpfs.nonce_test_ssl,
        force: force,
      },
    })
      .always(function (data) {})
      .done(function (data) {
        if (data.success) {
          ssl_cert_info = data.data;
          ssl_cert_info_html = '';

          if (ssl_cert_info.error == true) {
            ssl_cert_info_html += 'Your SSL certificate is <strong class="wfssl-red">NOT</strong> valid.';
            ssl_cert_info_html += '<div class="ssl_cert_error">' + ssl_cert_info.data + '</div>';
            if (wpfs.is_localhost) {
              ssl_cert_info_html +=
                '<div class="clear"><br /><strong>The site is not publicly available. It\'s on a localhost.</strong></div>';
            }
            ssl_cert_info_html += '<span class="wfssl-red dashicons dashicons-dismiss"></span>';
          } else {
            ssl_cert_info_html += 'Your SSL certificate is <strong class="wfssl-green">VALID</strong>.';
            ssl_cert_info_html +=
              '<div class="ssl_cert_info"><strong>Issued To:</strong> ' + ssl_cert_info.data.issued_to + '</div>';
            ssl_cert_info_html +=
              '<div class="ssl_cert_info"><strong>Issuer:</strong> ' + ssl_cert_info.data.issuer + '</div>';
            ssl_cert_info_html +=
              '<div class="ssl_cert_info"><strong>Valid From:</strong> ' + ssl_cert_info.data.valid_from + '</div>';
            ssl_cert_info_html +=
              '<div class="ssl_cert_info"><strong>Valid To:</strong> ' + ssl_cert_info.data.valid_to + '</div>';
            ssl_cert_info_html += '<span class="wfssl-green dashicons dashicons-yes-alt"></span>';
          }

          ssl_cert_info_html +=
            '<div class="button button-primary refresh-certificate-info" style="margin-top: 20px;">Refresh Certificate Info</div>';
          $('#ssl_cert_details').html(ssl_cert_info_html);
        } else {
          swal.fire({
            type: 'error',
            title: wpfs.undocumented_error,
          });
        }
      })
      .fail(function (data) {
        swal.fire({
          type: 'error',
          title: wpfs.undocumented_error,
        });
      });
  }

  // PRO related stuff
  $('li.wfssl-tab-pro').on('click', function (e) {
    e.preventDefault();

    open_upsell('tab');

    return false;
  });

  $('#wpwrap').on('click', '.open-pro-dialog', function (e) {
    e.preventDefault();
    $(this).blur();

    pro_feature = $(this).data('pro-feature');
    if (!pro_feature) {
      pro_feature = $(this).parent('label').attr('for');
    }
    open_upsell(pro_feature);

    return false;
  });

  $('#wpfssl-pro-dialog').dialog({
    dialogClass: 'wp-dialog wpfssl-pro-dialog',
    modal: true,
    resizable: false,
    width: 850,
    height: 'auto',
    show: 'fade',
    hide: 'fade',
    close: function (event, ui) {},
    open: function (event, ui) {
      $(this).siblings().find('span.ui-dialog-title').html('WP Force SSL PRO is here!');
      wpfssl_fix_dialog_close(event, ui);
    },
    autoOpen: false,
    closeOnEscape: true,
  });

  function clean_feature(feature) {
    feature = feature || 'free-plugin-unknown';
    feature = feature.toLowerCase();
    feature = feature.replace(' ', '-');

    return feature;
  }

  function open_upsell(feature) {
    feature = clean_feature(feature);

    $('#wpfssl-pro-dialog').dialog('open');

    $('#wpfssl-pro-table .button-buy').each(function (ind, el) {
      tmp = $(el).data('href-org');
      tmp = tmp.replace('pricing-table', feature);
      $(el).attr('href', tmp);
    });
  } // open_upsell

  if (window.localStorage.getItem('wpfssl_upsell_shown') != 'true') {
    open_upsell('welcome');

    window.localStorage.setItem('wpfssl_upsell_shown', 'true');
    window.localStorage.setItem('wpfssl_upsell_shown_timestamp', new Date().getTime());
  }

  if (window.location.hash == '#open-pro-dialog') {
    open_upsell('url-hash');
    window.location.hash = '';
  }
})(jQuery);

function wpfssl_fix_dialog_close(event, ui) {
  jQuery('.ui-widget-overlay').bind('click', function () {
    jQuery('#' + event.target.id).dialog('close');
  });
} // wpfssl_fix_dialog_close
