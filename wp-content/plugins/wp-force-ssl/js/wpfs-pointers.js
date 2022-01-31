/*
 * WP Force SSL
 * Backend GUI pointers
 * (c) WebFactory Ltd, 2017 - 2022
 */

jQuery(document).ready(function($){
  if (typeof wp_force_ssl_pointers  == 'undefined') {
    return;
  }

  $.each(wp_force_ssl_pointers, function(index, pointer) {
    if (index.charAt(0) == '_') {
      return true;
    }
    $(pointer.target).pointer({
        content: '<h3>WP Force SSL</h3><p>' + pointer.content + '</p>',
        pointerWidth: 380,
        position: {
            edge: pointer.edge,
            align: pointer.align
        },
        close: function() {
                $.get(ajaxurl, {
                    notice_name: index,
                    _ajax_nonce: wp_force_ssl_pointers._nonce_dismiss_pointer,
                    action: 'wpfs_dismiss_notice'
                });
        }
      }).pointer('open');
  });
});
