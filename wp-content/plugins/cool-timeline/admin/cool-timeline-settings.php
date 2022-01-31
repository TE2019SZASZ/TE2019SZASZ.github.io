<?php

function ctl_free_option_panel() {
    /**
     * configure your admin page
     */
    $config = array(
        'menu' => array('top' => 'cool_timeline'), //sub page to settings page
        'page_title' => __('Cool Timeline Settings','apc2'), //The name of this page 
        'capability' => 'manage_options', // The capability needed to view the page 
        'option_group' => 'cool_timeline_options', //the name of the option to create in the database
        'id' => 'cool_timeline_page', // meta box id, unique per page
        'fields' => array(), // list of fields (can be added by field arrays)
        'local_images' => false, // Use local or hosted images (meta box images for add/remove)
        'use_with_theme' => false          //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
    );

    /**
     * cool timeline settings panel
     */
    $options_panel = new BF_Admin_Page_Class($config);
    $options_panel->OpenTabs_container('');

    /**
     * define your admin page tabs listing
     */
    $options_panel->TabsListing(array(
            'links' => array(
            'options_1' => __('General Settings', 'apc2'),
                      'options_2' => __('Style Settings', 'apc2'),
                      'options_3' => __('Typography Settings', 'apc2'),
            'options_4' => __('Advance Settings', 'apc2'),
        )
    ));

    /**
     * Open admin page first tab
     */
   $options_panel->OpenTab('options_1');

    /**
     * Add fields to your admin page first tab
     * 
     * Simple options:
     * input text, checbox, select, radio 
     * textarea
     */
    //title
   $options_panel->Title(__("General Settings", "apc2"));
    //An optionl descrption paragraph
    //text field
    $options_panel->addText('title_text', array('name' => __('Timeline Title (Default)  ', 'apc2'), 'std' => 'Cool Timeline', 'desc' => __('', 'apc2')));

    //select field
    $options_panel->addSelect('title_tag', array('h1' => 'H1',
        'h2' => 'H2',
        'h3' => 'H3',
        'h4' => 'H4',
        'h5' => 'H5',
        'h6' => 'H6'), array('name' => __('Title Heading Tag ', 'apc2'), 'std' => array('h1'), 'desc' => __('', 'apc2')));
    $options_panel->addRadio('title_alignment', array('left' => 'Left',
          'center' => 'Center','right'=>'Right'), array('name' => __('Title Alignment ?', 'apc2'), 'std' => array('center'), 'desc' => __('', 'apc2')));
    $options_panel->addText('post_per_page', array('name' => __('Number of stories to display ?', 'apc'), 'std' => 10, 'desc' => __('This option is overridden by shortcode. Please check shortcode generator.', 'apc')));
        $options_panel->addText('content_length', array('name' => __('Content Length ', 'apc2'), 'std' => 50, 'desc' => __('Please enter no of words', 'apc2')));
        //Image field
        $options_panel->addRadio('desc_type', array('short' => 'Short (Default)',
        'full' => 'Full (with HTML)'), array('name' => __('Stories Description?', 'cool-timeline'), 'std' => array('short'), 'desc' => __('This option is overridden by shortcode in version 1.7. Please check shortcode generator.', 'cool-timeline')));

        $options_panel->addRadio('posts_orders', array('DESC' => 'DESC',
        'ASC' => 'ASC'), array('name' => __('Stories Order ?', 'cool-timeline'), 'std' => array('DESC'), 'desc' => __('This option is overridden by shortcode in version 1.7. Please check your shortcode generator.', 'cool-timeline')));
      //select field

     $options_panel->addImage('user_avatar',array('name'=> __('Timeline default Image','apc2'), 'desc' => __('','apc2')));
    $options_panel->addRadio('display_readmore', array('yes' => 'Yes',
          'no' => 'No'), array('name' => __('Display read more ?', 'apc2'), 'std' => array('yes'), 'desc' => __('', 'apc2')));
     $options_panel->CloseTab();

    /**
     * Open admin page 2 tab
     */
   $options_panel->OpenTab('options_2');
    $options_panel->Title(__("Style Settings", "apc2"));
    /**
     * To Create a Conditional Block first create an array of fields (just like a repeater block
     * use the same functions as above but add true as a last param
     */
    //   $Conditinal_fields[] = $options_panel->addText('con_text_field_id', array('name' => __('My Text ', 'apc')), true);
    $Conditinal_fields[] =$options_panel->addColor('bg_color', array('name' => __('Background Color', 'apc2')), true);


  /**
   * Then just add the fields to the repeater block
   */
  //conditinal block
  $options_panel->addCondition('background', array(
      'name' => __('Container Background ', 'apc2'),
      'desc' => __('', 'apc2'),
      'fields' => $Conditinal_fields,
      'std' => false
  ));

  //Color field
  $options_panel->addColor('content_bg_color',array('name'=> __('Story Background Color','apc2'),'std'=>'#c9dfe8', 'desc' => __('','apc2')));
  $options_panel->addColor('circle_border_color',array('name'=> __('Circle Color','apc2'),'std'=>'#38aab7', 'desc' => __('','apc2')));
  $options_panel->addColor('line_color',array('name'=> __('Line Color','apc2'),'std'=>'#025149', 'desc' => __('','apc2')));
  //Color field
  $options_panel->addColor('first_post',array('name'=> __('First Color','apc2'),'std'=>'#29b246', 'desc' => __('','apc2')));
  $options_panel->addColor('second_post',array('name'=> __('Second Color','apc2'),'std'=>'#ce792f', 'desc' => __('','apc2')));
  $options_panel->addCode('custom_styles', array('name' => 'Custom Styles', 'syntax' => 'css'));
  $options_panel->CloseTab();
    /**
     * Open admin page third tab
     */
  $options_panel->OpenTab('options_3');
    //title
    $options_panel->Title(__("Typography Settings", "apc2"));
    $options_panel->addTypo('main_title_typo', array('name' => __("Main Title", "apc2"), 'std' => array('size' => '14px', 'color' => '#000000', 'face' => 'Montserrat', 'style' => 'normal'), 'desc' => __('', 'apc2')));
    $options_panel->addTypo('post_title_typo', array('name' => __("Story Title", "apc2"), 'std' => array('size' => '14px', 'color' => '#000000', 'face' => 'Montserrat', 'style' => 'normal'), 'desc' => __('', 'apc2')));
    $options_panel->addRadio('post_title_text_style', array('lowercase' => 'Lowercase',
        'uppercase' => 'Uppercase','capitalize'=>'Capitalize'), array('name' => __('Story Title Style ?', 'apc2'), 'std' => array('capitalize'), 'desc' => __('', 'apc2')));    
    $options_panel->addTypo('post_content_typo', array('name' => __("Post Content", "apc2"), 'std' => array('size' => '14px', 'color' => '#000000', 'face' => 'Montserrat', 'style' => 'normal'), 'desc' => __('', 'apc2')));
    $options_panel->CloseTab();
    $options_panel->OpenTab('options_4');
    $options_panel->addParagraph(__('<div class="advance_options"><a target="_blank" href="https://1.envato.market/ct">
    <img src="'.COOL_TIMELINE_PLUGIN_URL.'assets/images/pro-features-list.png"></a></div>', "cool-timeline"));
    $options_panel->CloseTab();
   }


