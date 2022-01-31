<?php

if (!class_exists('ReviewNotice')) {
    class ReviewNotice {
        /**
         * The Constructor
         */
        public function __construct() {
            // register actions
            if(is_admin()){
                add_action( 'admin_notices',array($this,'admin_notice_for_reviews'));
                add_action( 'wp_ajax_ctl_dismiss_notice',array($this,'dismiss_review_notice' ) );
                add_action( 'wp_ajax_ctl_dismiss_ele_addon_notice',array($this,'ctl_dismiss_ele_addon_notice' ) );
                
                // Notice if the Elementor is not active
                if ( did_action( 'elementor/loaded' ) ) {
                    if (!class_exists('Timeline_Widget_Addon')) {
                        add_action( 'admin_notices',array($this,'admin_notice_for_elementor_addon'));
                    }
                    }
            }
        }

    public function dismiss_review_notice(){
        $rs=update_option( 'cool-timelne-ratingDiv','yes' );
        echo  json_encode( array("success"=>"true") );
        exit;
    }
    
    public function admin_notice_for_reviews(){

        if( !current_user_can( 'update_plugins' ) ){
            return;
         }
         // get installation dates and rated settings
         $installation_date = get_option( 'cool-timelne-installDate' );
         $alreadyRated =get_option( 'cool-timelne-ratingDiv' )!=false?get_option( 'cool-timelne-ratingDiv'):"no";

         // check user already rated 
        if( $alreadyRated=="yes") {
              return;
            } 

            // grab plugin installation date and compare it with current date
            $display_date = date( 'Y-m-d h:i:s' );
            $install_date= new DateTime( $installation_date );
            $current_date = new DateTime( $display_date );
            $difference = $install_date->diff($current_date);
            $diff_days= $difference->days;
          
            // check if installation days is greator then week
         if (isset($diff_days) && $diff_days>=3) {
                echo $this->create_notice_content();
            }
       }  

       // generated review notice HTML
       function create_notice_content(){
        $ajax_url=admin_url( 'admin-ajax.php' );
        $ajax_callback='ctl_dismiss_notice';
        $wrap_cls="notice notice-info is-dismissible";
        $img_path=COOL_TIMELINE_PLUGIN_URL.'assets/images/cool-timeline-logo.png';
        $p_name="Cool Timeline";
        $like_it_text='Rate Now! ★★★★★';
        $already_rated_text=esc_html__( 'I already rated it', 'cool-timeline' );
        $not_interested=esc_html__( 'Not Interested', 'cool-timeline' );
        $not_like_it_text=esc_html__( 'No, not good enough, i do not like to rate it!', 'cool-timeline' );
        $p_link=esc_url('https://wordpress.org/support/plugin/cool-timeline/reviews/?filter=5#new-post');
        $output='';
        $message="Thanks for using <b>$p_name</b> WordPress plugin. We hope it meets your expectations!
         Please give us a quick rating, it works as a boost for us to keep working on more
          <a href='https://coolplugins.net' target='_blank'>cool plugins</a>!<br/>";
      
        $html='<div data-ajax-url="%8$s"  data-ajax-callback="%9$s" class="cooltimeline-rating-notice-wrapper %1$s" style="display:table;max-width: 820px;">
        <div class="logo_container" style="display:table-cell"><a href="%5$s"><img src="%2$s" alt="%3$s"></a></div>
        <div class="message_container" style="display:table-cell"><p style="font-size:14px">%4$s</p>
        <div class="callto_action">
        <ul>
            <li class="love_it"><a href="%5$s" class="like_it_btn button button-primary" target="_new" title="%6$s">%6$s</a></li>
            <li class="already_rated"><a href="javascript:void(0);" class="already_rated_btn button ctl_dismiss_notice" title="Already Rated! Close This Box.">%7$s</a></li>
            <li class="already_rated"><a href="javascript:void(0);" class="already_rated_btn button ctl_dismiss_notice" title="Not Interested! Close This Box.">%10$s</a></li>
            <li><a href="https://1.envato.market/ct" target="_blank" class="already_rated_btn button">Try Pro</a></li>
        </ul>
        <div class="clrfix"></div>
        </div>
        </div>
        </div>';
   
  $inline_css='<style>.cooltimeline-rating-notice-wrapper.notice.notice-info.is-dismissible {
        padding: 5px;
        display: table;
        width: 100%;
        max-width: 820px;
        clear: both;
        border-radius: 5px;
        border: 2px solid #b7bfc7;
    }
    .cooltimeline-rating-notice-wrapper .logo_container {
        width: 100px;
        display: table-cell;
        padding: 5px;
        vertical-align: middle;
    }
    .cooltimeline-rating-notice-wrapper .logo_container a,
    .cooltimeline-rating-notice-wrapper .logo_container img {
        width:100%;
        height:auto;
        display:inline-block;
    }
    .cooltimeline-rating-notice-wrapper .message_container {
        display: table-cell;
        padding: 5px 20px 5px 5px;
        vertical-align: middle;
    }
    .cooltimeline-rating-notice-wrapper ul li {
        float: left;
        margin: 0px 10px 0 0;
    }
    .cooltimeline-rating-notice-wrapper ul li.already_rated a:before {
        color: #f12945;
        content: "\f153";
        font: normal 18px/22px dashicons;
        display: inline-block;
        vertical-align: middle;
        margin-right: 3px;
    }
    .clrfix{
        clear:both;
    }</style>';
$inline_js="<script>jQuery(document).ready(function ($) {
	$('.ctl_dismiss_notice').on('click', function (event) {
		var thisE = $(this);
		var wrapper=thisE.parents('.cooltimeline-rating-notice-wrapper');
		var ajaxURL=wrapper.data('ajax-url');
		var ajaxCallback=wrapper.data('ajax-callback');
		$.post(ajaxURL, { 'action':ajaxCallback }, function( data ) {
			wrapper.slideUp('fast');
		  }, 'json');
	});
});</script>";

  $output=sprintf($html,
        $wrap_cls,
        $img_path,
        $p_name,
        $message,
        $p_link,
        $like_it_text,
        $already_rated_text,
        $ajax_url,// 8
        $ajax_callback,//9
        $not_interested
        );
        $output.=$inline_css. ' '.$inline_js;
        return $output;
       }


    public function admin_notice_for_elementor_addon(){
        if( !current_user_can( 'update_plugins' ) ){
            return;
         }
         $alreadyRated =get_option( 'dismiss_ele_addon_notice' )!=false?get_option( 'dismiss_ele_addon_notice'):"no";
         // check user already rated 
        if( $alreadyRated=="yes") {
            return;
            } 
         $ajax_url=admin_url( 'admin-ajax.php' );
         $ajax_callback='ctl_dismiss_ele_addon_notice';
         $ele_logo=COOL_TIMELINE_PLUGIN_URL.'assets/images/elementor-addon.png';
         $output='';
       
         $output='<div  data-ajax-url="'.$ajax_url.'"  
         data-ajax-callback="'.$ajax_callback.'" 
         class="ele_addon_notice_wrp notice notice-info">
         <a href="javascript:void(0);" class="button dismiss_it" title="Not Interested! Close This Box.">Not Interested!.</a>
         <a href="https://wordpress.org/plugins/timeline-widget-addon-for-elementor/">
         
         <div class="logo_container"><img src="'.$ele_logo.'"></a>
         </div>
         <div  class="message_container">
          Hi! We checked that you are using <strong>Elementor Page Builder</strong>.
          <br/>Please try latest <a href="https://wordpress.org/plugins/timeline-widget-addon-for-elementor/"><strong>Elementor Timeline Widget Addon</strong></a> plugin developed by <a href="https://coolplugins.net">Cool Plugins</a>
             & <br/> showcase your  life story or company history in a beautiful timeline design.
           
            </div>
          </div><style>
          
          .ele_addon_notice_wrp .logo_container {
            width:58px;
            display:inline-block;
            margin-right: 10px;
            vertical-align: top;
        }
        .ele_addon_notice_wrp .logo_container img {
            width:64px;
            height:auto;
        }
        .ele_addon_notice_wrp .message_container {
            width: calc(100% - 140px);
            display: inline-block;
            margin: 0;
            vertical-align: middle;
            margin: 8px;
            font-size: 16px;
            line-height: 23px;
        }
        .ele_addon_notice_wrp  a.button.dismiss_it {
            float: right;
            position: absolute;
            right: 28px;
            margin: 5px;
        }

        </style>';

        $inline_js="<script>jQuery(document).ready(function ($) {
            $('.ele_addon_notice_wrp').find('a.dismiss_it').on('click', function (event) {
                var thisE = $(this);
                var wrapper=thisE.parents('.ele_addon_notice_wrp');
                var ajaxURL=wrapper.data('ajax-url');
                var dismissAjaxCallback=wrapper.data('ajax-callback');
                $.post(ajaxURL, { 'action':dismissAjaxCallback }, function( data ) {
                    wrapper.slideUp('fast');
                  }, 'json');
            });
        });</script>";
        $output.=$inline_js;

       echo $output;
    }

    public function ctl_dismiss_ele_addon_notice(){
        $rs=update_option( 'dismiss_ele_addon_notice','yes' );
        echo  json_encode( array("success"=>"true") );
        exit;
    }

    } //class end

} 



