<?php

if (!class_exists('CoolTimelineShortcode')) {

    class CoolTimelineShortcode {

        /**
         * The Constructor
         */
        public function __construct() {
           
            // register cool timeline shortcode
			 add_action('init', array($this, 'cooltimeline_register_shortcode'));
			//adding required assets
			 add_action('wp_enqueue_scripts', array($this, 'ctl_load_scripts_styles'));
			 add_action( 'plugins_loaded', array($this,'load_ctl_for_elementor'));
			// Call actions and filters in after_setup_theme hook
		 	add_action( 'after_setup_theme',array($this, 'ctl_crm'),999 );
			// custom excerpt length filter for story content
			add_filter('excerpt_length', array($this, 'ctl_excerpt_length'), 999);
		}
		function ctl_crm() {
			add_filter('excerpt_more', array($this,'ctl_excerpt_more'), 999);
		}
		 // add more link to excerpt
		 function ctl_excerpt_more($more) {
			global $post;
			 $ctl_options_arr =get_option('cool_timeline_options');
			 $ctl_display_readmore = isset($ctl_options_arr['display_readmore'])?$ctl_options_arr['display_readmore']:'yes';
			  if ($post->post_type == 'cool_timeline' && !is_single() ){
				   if($ctl_display_readmore=='yes'){
						  $read_more_txt = apply_filters('cool_timeline_read_more_text',__('Read More', 'cool-timeline'));
						   return '..<a class="read_more ctl_read_more" href="'. get_permalink($post->ID) . '">'.$read_more_txt .'</a>';
					 }  
			   }else{
					return $more;
			   }
		  }
		  	// story content length filter callback
		function ctl_excerpt_length( $length ) {
			global $post;
			$ctl_options_arr = get_option('cool_timeline_options');
			$ctl_content_length = isset($ctl_options_arr['content_length'])?$ctl_options_arr['content_length']:100;
			if ($post->post_type == 'cool_timeline' && !is_single() ){
				return $ctl_content_length;
				}
			return $length;
		}

		function load_ctl_for_elementor(){
		if (did_action( 'elementor/loaded' ) ) {
			add_shortcode('cool-timeline', array($this,'cooltimeline_view'));
			}
			
		}

		/*
		  cool-timeline shortcode main
		*/
        function cooltimeline_register_shortcode() {
        	 add_shortcode('cool-timeline', array($this,'cooltimeline_view'));
		 }

		 // shortcode callback
	    function cooltimeline_view($atts, $content = null) {
			$attribute = shortcode_atts(array(
               'post_per_page' => 10,
				'layout'=>'',
				'order'=>'',
				'story-content'=>'',
				'animation'=>'',
				'date-format'=>'',
				'icons'=>'',
				'show-posts'=>'',
				'skin'=>'default'
                    ), $atts);
			$skin=esc_attr($attribute['skin']);
			$ctl_options_arr = get_option('cool_timeline_options');
			$layout=$attribute['layout'];

			 // set stories animations
			 if($attribute['animation']){
				if($attribute['animation']=='fadeInUp'){
					$ctl_animation='fade-up';
				}else{
					$ctl_animation=$attribute['animation'];
				}
			 }else{
			  $ctl_animation ='fade-up';
			 }

			// loading timeline assets on shortcode specific page
			$this->ctl_load_assets($layout,$ctl_animation);
				
			// get all settings
			 $ctl_title_text =isset($ctl_options_arr['title_text'])?$ctl_options_arr['title_text']:'';
        	 $ctl_title_tag =isset($ctl_options_arr['title_tag'])?$ctl_options_arr['title_tag']:'H2';

		 if(isset($ctl_options_arr['user_avatar']['id'])){
				$user_avatar =wp_get_attachment_image_src($ctl_options_arr['user_avatar']['id'],'medium');
				}
		    $ctl_post_per_page =isset($ctl_options_arr['post_per_page'])?$ctl_options_arr['post_per_page']:10;
			$ctl_no_posts= isset($ctl_options_arr['no_posts'])?$ctl_options_arr['no_posts']:"No timeline post found";
			$ctl_content_length =  isset($ctl_options_arr['content_length'])?$ctl_options_arr['content_length']:100;
		
			$disable_months =isset($ctl_options_arr['disable_months'])?$ctl_options_arr['disable_months']:"no";
			$title_alignment = isset($ctl_options_arr['title_alignment'])?$ctl_options_arr['title_alignment']:"center";
		    $display_readmore= isset($ctl_options_arr['display_readmore'])?$ctl_options_arr['display_readmore']:"yes";
            $ctl_title_text = $ctl_title_text ? $ctl_title_text : 'Timeline';
        
			$ctl_posts_orders='';
			$story_desc_type='';
			if(isset($attribute['order'])&& !empty($attribute['order'])){
				$ctl_posts_orders =$attribute['order'];
			}else{
				$ctl_posts_orders = isset($ctl_options_arr['posts_orders'])?$ctl_options_arr['posts_orders']:"DESC";
			}
			if(isset($attribute['story-content'])&& !empty($attribute['story-content'])){
				$story_desc_type =$attribute['story-content'];
			}else{
				$story_desc_type = isset($ctl_options_arr['desc_type'])?$ctl_options_arr['desc_type']:"short";
			}
		
			// create dynamic classes
			$wrp_cls = 'white-timeline';
			$post_skin_cls = 'light-grey-post';
			$wrapper_cls = 'white-timeline-wrapper';$output='';$ctl_html='';$ctl_avtar_html='';
			// set default var for later use
			$display_year = '';	$wrp_cls='';$ctl_format_html='';$ctl_html_no_cont='';$st_cls=''; $post_content="";
			 $horizontal_html="";
			 // custom date format
			$format =__('d/M/Y','cool-timeline');
		    $year_position = 2;
			$i = 0;

			/* checking timeline layout and creating classes */

			if ($attribute['layout'] == "one-side") {
		    $layout_cls = 'one-sided';
		    $layout_wrp = 'one-sided-wrapper';
				}elseif ($attribute['layout'] == "compact"){
			 $layout_cls = 'compact';
		    $layout_wrp = 'compact-wrapper';
			}  else {
			    $layout_cls = '';
			    $layout_wrp = 'both-sided-wrapper';
			}
	
		// create classes according to the active layout
		if($attribute['layout']){
			$wrp_cls=$attribute['layout'];
			}else{
			 $wrp_cls='default-layout';
			}
			if($attribute['icons']=="YES"){
			$clt_icons="icons_yes";
			}else{
			 $clt_icons="icons_no";
			}
			    
                  

           if($layout !="simple"){
				$st_cls='simple-layout';
			}else{
				$st_cls='default-layout';
			}
      // date format for timeline stories
        $date_format=$this->ctl_date_formats($attribute['date-format'],$ctl_options_arr);     
		
		// timeline stories custom loop
			$args = array(
		   'post_type' => 'cool_timeline', 
		   'posts_per_page' => $ctl_post_per_page,
		   'post_status' => array( 'publish', 'future','Scheduled'),
		   'orderby' => 'ctl_story_timestamp',
		   'order' =>$ctl_posts_orders
		   );
		   $args['meta_query']= array(
			array(
				'key'=> 'ctl_story_timestamp',
				'compare' => 'EXISTS',
				'type'    => 'NUMERIC'
			  ));

		   	// paged for pagination 
			$args['paged']= (get_query_var('paged')) ? get_query_var('paged') : 1;
			$paged=$args['paged'];
		
		   if ($attribute['show-posts']) {
			$args['posts_per_page'] = $attribute['show-posts'];
			if($attribute['layout']=="horizontal"){
				$args['posts_per_page'] =-1;
			}
			} else {
				$args['posts_per_page'] = $ctl_post_per_page;
			}

			// start Main query
			$ctl_loop = new WP_Query(apply_filters( 'ctl_stories_query',$args));
			$total_stories= $ctl_loop->found_posts;
            if ($ctl_loop->have_posts()){		
                while ($ctl_loop->have_posts()) : $ctl_loop->the_post();
				$p_id=get_the_ID();
              	global $post;
				  $img_cont_size = get_post_meta($p_id, 'image_container_type', true);
				  $container_cls=isset($img_cont_size)?$img_cont_size:"Full";	
				  $container_cls=strtolower($container_cls);
				// grabing stories posted date for later use
				$posted_date=ctlfree_get_story_date($p_id,$date_format);
				
				// grabing stories content according to the  dynamic settings 
				 if($story_desc_type=='full'){
					$post_content =apply_filters( 'the_content', get_the_content() );
					$post_content = apply_filters('cool_timeline_story_content',$post_content);
				}else{
					$post_content =apply_filters( 'cool_timeline_story_content',get_the_excerpt());
					}
				// adding clasess for alernate styles.
				 if ($i % 2 == 0) {
					$even_odd = "even";
					} else {
					$even_odd = "odd";
					}
				// Generating Stories Hightlighted Year only for default and one sided timeline layouts
				$s_date_only=ctlfree_get_story_date($p_id,$format);
				$post_date = explode('/', $s_date_only);

				// add story Year circle only in defaut and one sided vertical layout
        if(in_array($layout,array("simple","compact"),TRUE)!=true){
				if(isset($post_date[$year_position])){
                    $post_year = $post_date[$year_position];
                    if ($post_year != $display_year) {
                     $display_year = $post_year;
                     $year_cls=$this->ctl_story_cls($post_year);
						$ctle_year_lbl = sprintf('<span class="ctl-timeline-date">%s</span>', $post_year);
						$ctl_html .= '<div  data-aos="'. esc_attr($ctl_animation).'"  class="timeline-year scrollable-section"
						data-section-title="' . esc_attr($post_year) . '" id="clt-' . esc_attr($post_year) . '">
						<div class="icon-placeholder">' . $ctle_year_lbl. '</div>
						<div class="timeline-bar"></div>
						</div>';
						}
				}
			}	

			
				// timeline stories all classes
				$compt_cls=$layout=="compact"?"timeline-mansory":'';
				$p_cls=array();
		        $p_cls[]="timeline-post";
		        $p_cls[]=esc_attr($even_odd);
		        $p_cls[]=esc_attr($post_skin_cls);
		        $p_cls[]=esc_attr($clt_icons);
		        $p_cls[]=$layout=="compact"?"timeline-mansory":'';
				$p_cls[]= $skin.'-skin';
			
		        $p_cls=apply_filters('ctl_story_clasess',$p_cls);
					
				// generate timeline story content html
				$ctl_html .='<!-- .timeline-post-start-->';
				$ctl_html .='<div  class="'.implode(" ",$p_cls).'"  id="story-'.esc_attr($p_id).'">';
				if($layout!="compact"){
				$ctl_html .='<div  data-aos="'.esc_attr($ctl_animation).'" class="timeline-meta">';
				if($disable_months=="no"){
					$ctl_html .= '<div class="meta-details">'.esc_html($posted_date) . '</div>';
				} 
				$ctl_html .= '</div>';
				}
				// grabing stories icon
				if(function_exists('get_fa')){
		        $post_icon=get_fa(true);
				}
		        if(isset($post_icon)){
		            $icon=$post_icon;
		        }else{
		           $icon = '<i class="fa fa-clock-o" aria-hidden="true"></i>';
		         }
		 
				 // if icon settings is enable from shortcode
  			if (isset($attribute['icons']) && $attribute['icons'] == "YES") {
			$ctl_html .='<div   data-aos="'. esc_attr($ctl_animation).'" class="timeline-icon icon-larger iconbg-turqoise icon-color-white">
                    	<div class="icon-placeholder">'.$icon.'</div>
                        <div class="timeline-bar"></div>
                    </div>';
			}else {
			$ctl_html .= '<div  data-aos="'.esc_attr($ctl_animation).'" class="timeline-icon icon-dot-full"><div class="timeline-bar"></div></div>';
			}

	 $ctl_html .= '<div  data-aos="'.esc_attr($ctl_animation).'" class="timeline-content clearfix">';
// story date for compact layout
	 if($layout =="compact"){
	 if($disable_months=="no"){
		$ctl_html .= '<h2 class="content-title">' . $posted_date . '</h2>';
			} 
		}else{
		     $ctl_html .= '<h2 class="content-title">' . esc_html(get_the_title($p_id)) . '</h2>';
 		}	
    $ctl_html .= '<div class="ctl_info event-description ' .esc_attr($container_cls) . '">';
    $ctl_html .=$this->clt_story_featured_img($p_id,$layout);
    $ctl_html .= '<div class="content-details">';

	 if($layout =="compact"){
	 $ctl_html .= '<h3 class="content-title-cmt">'.esc_html(get_the_title($p_id)).'</h3>';
		}
	 $ctl_html .=$post_content;
	 $ctl_html .= '</div></div></div><!-- timeline content --></div><!-- .timeline-post-end -->';
	
	$ctl_format_html = '';
	
// creating Horizontal layout HTML
if($layout =="horizontal"){
	//var_dump($icon);
	$h_story_content='';
	$post_year=ctlfree_get_story_date($p_id,__('Y','cool-timeline'));
	$year_cls=$this->ctl_story_cls($post_year);
	$horizontal_html.='<li class="year-'.esc_attr($post_year).' '.esc_attr($even_odd).' '.esc_attr($year_cls).'">';
	if (isset($attribute['icons']) && $attribute['icons'] == "YES") {
		$horizontal_html.='<span class="icon-placeholder">'.$icon.'</span>';
	}
	// grabing Highlighted Year
	if($attribute['date-format']==""||$attribute['date-format']=="Y"){
		$story_main_date=ctlfree_get_story_date($p_id,__('Y','cool-timeline'));
	}else{
		$story_main_date=$posted_date;
	}
			if( $display_readmore=="yes"){
				$horizontal_html.='<a ref="prettyPhoto" href="#ctl-story-'.esc_attr($p_id).'">';
			}
			$horizontal_html.='<div class="ctl-story-year"><span class="rm_year">'.esc_html($story_main_date).'</span></div>';
			$horizontal_html.='<div class="ctl-story-title"><p class="story_title">';
			$horizontal_html.=esc_html(get_the_title($p_id));
			$horizontal_html.='</p></div>';
			if( $display_readmore=="yes"){
				$horizontal_html.='</a>';
			}
			
		$horizontal_html.='<div id="ctl-story-'.esc_attr($p_id).'" class="ctl_hide"><div class="ctl-popup-content">
			<h2>'.esc_html(get_the_title($p_id)).'</h2>';
			$story_posted_date=ctlfree_get_story_date($p_id,__(apply_filters( 'cool_timeline_story_date','F j, Y'),'cool-timeline'));
			$horizontal_html.='<div class="story-posted-date">'.esc_html($story_posted_date).'</div>';
			$horizontal_html .=$this->clt_story_featured_img($p_id,$layout);
			$horizontal_html.='<div class="story-content">'.$post_content.'</div>';
		$horizontal_html.='</div></div>';
	   $horizontal_html.='</li>';
	}
	$post_content = '';
	$i++;		
	endwhile;
    wp_reset_postdata();
	} else {
		$ctl_html_no_cont .= '<div class="no-content"><h4>';
		//$ctl_html_no_cont.=$ctl_no_posts;
		$ctl_html_no_cont .= __('Sorry,You have not added any story yet', 'cool-timeline');
		$ctl_html_no_cont .= '</h4></div>';
	}
	
	/*
		Timeline Main Container Wrapper
	*/
	
$timeline_id="ctl-free-one";	
$output .='<! ========= Cool Timeline Free '. COOL_TIMELINE_CURRENT_VERSION.' =========>';
$main_wrp_cls=array();

// Generating Wrapper for Vertical timeline
if($layout!="horizontal"){  
		// creating classes
        $main_wrp_cls[]="cool_timeline";
        $main_wrp_cls[]="cool-timeline-wrapper";
        $main_wrp_cls[]=$layout_wrp;
        $main_wrp_cls[]=$wrapper_cls;
		$main_wrp_cls[]=$skin.'-skin-tm';
        $main_wrp_cls=apply_filters('ctl_wrapper_clasess',$main_wrp_cls);     

		$output .= '<div class="'.implode(" ",$main_wrp_cls).'">';
		if (isset($user_avatar[0]) && !empty($user_avatar[0])) {
					$ctl_avtar_html .= '<div class="avatar_container row"><span title="' .esc_attr($ctl_title_text) . '">
					<img  class="center-block img-responsive img-circle" alt="'.esc_attr($ctl_title_text). '" src="' .esc_url($user_avatar[0]) . '"></span></div> ';
				}
			  //  if ($title_visibilty == "yes") {
                $output .= sprintf('<%s class="timeline-main-title center-block">%s</%s>', $ctl_title_tag, $ctl_title_text, $ctl_title_tag);
            //}	
				$output .= $ctl_avtar_html;
			$output .= '<div class="cool-timeline white-timeline ultimate-style ' .esc_attr($layout_wrp). ' ' .esc_attr($wrp_cls).
			' ' .$layout_cls.'">';
			$output .= '<div data-animations="'.esc_attr($ctl_animation).'"  id="' .esc_attr($timeline_id). '" class="cooltimeline_cont  clearfix '.esc_attr($clt_icons).'">';
			if($layout=="compact"){
				$output .='<div class="center-line">
            </div>';
			}
			$output .= $ctl_html;
            $output .= $ctl_html_no_cont;
            $output .= '</div>';

		$output .= '<div class="clearfix"></div></div>';
		//if ($enable_pagination == "yes") {
		$output .=ctl_pagination($ctl_loop->max_num_pages, "", $paged);
		//}
		$output .=' </div><!-- end
 		================================================== -->';

 		}else{
			$slidetoshow=3;
			
		if($attribute['show-posts']>$total_stories){
			$slidetoshow=$total_stories;
		}else{
			$slidetoshow=$attribute['show-posts'];
		}
		$theme_cls='';
	
	
	// Horizontal timeline Wrapper		
$output .='<div class="ctl-preloader-loader" style="text-align:center;"><img alt="loading" src="'.COOL_TIMELINE_PLUGIN_URL.'/assets/images/preloader.gif"></div>
		<style>
		.cool_timeline_horizontal{
		opacity:0;
		}</style>';

	  $output .= '<div class="cool_timeline_horizontal"><ul data-slide-to-show="'.esc_attr($slidetoshow).'" class="ctl_road_map_wrp">';
 			$output .=$horizontal_html;
 			$output .='</ul></div>';
 		}
            return $output ;
		}
	/*
     Date format settings for stories
     */

	function ctl_date_formats($date_format,$ctl_options_arr){
		
		$date_formats='';
   	 if(!empty($date_format)){
              if($date_format=="default"){
                  $date_formats = isset($ctl_options_arr['ctl_date_formats']) ? $ctl_options_arr['ctl_date_formats'] : __('F j','cool-timeline');
                }else{
                     $df=$date_format;
                     $date_formats =__("$df",'cool-timeline');     
                     }  
       }else{
          	$date_formats =__('F j','cool-timeline');
            }

            return $date_formats;
	 }

	 /*
	 Get story featured image  
	 */

	 function clt_story_featured_img($post_id ,$layout){
 		$img_cont_size = get_post_meta($post_id, 'img_cont_size', true);
		 $img_html='';
		$imgAlt = get_post_meta(get_post_thumbnail_id($post_id),'_wp_attachment_image_alt', true); 
		$alt_text=$imgAlt?$imgAlt:get_the_title($post_id);
		 $img_f_url = wp_get_attachment_url(get_post_thumbnail_id($post_id));
		 $story_img_link = '<a title="' . esc_attr(get_the_title($post_id)). '"  href="' .esc_url($img_f_url). '" class="ctl_prettyPhoto">';
			
		    if ( has_post_thumbnail($post_id) ) {
				if ($img_cont_size == "small"){
					$contCls='pull-left';
		      		$img=get_the_post_thumbnail( $post_id,apply_filters('cool_timeline_story_img_size','medium'), array( 'class' => 'story-img left_small','alt'=>$alt_text) );
				 } else {
					$contCls='full-width';
					$img=get_the_post_thumbnail( $post_id,apply_filters('cool_timeline_story_img_size','large'), array( 'class' => 'story-img','alt'=>$alt_text) );
				}
					$img_html .= '<div class="'.$contCls.'">';
					if($layout!="horizontal"){
					$img_html.=$story_img_link;  
					}
					$img_html.=$img;
					if($layout!="horizontal"){
					$img_html.='</a>';  
					}
					$img_html.='</div>';

		      }
		     
		   return  $img_html;
		}


		/*
		* Registered All assets 
		*/
        function ctl_load_scripts_styles() {
			/*
			 * google fonts
			 */
			
			$ctl_options_arr = get_option('cool_timeline_options');
		
			$post_content_face=isset($ctl_options_arr['post_content_typo']['face'])?$ctl_options_arr['post_content_typo']['face']:"";
			$post_title=isset($ctl_options_arr['post_title_typo']['face'])?$ctl_options_arr['post_title_typo']['face']:"";
			$main_title=isset($ctl_options_arr['main_title_typo']['face'])?$ctl_options_arr['main_title_typo']['face']:"";
			$selected_fonts = array($post_content_face,$post_title,$main_title);
		   /*
            * google fonts
            */
            // Remove any duplicates in the list
            $selected_fonts = array_unique($selected_fonts);
            // If it is a Google font, go ahead and call the function to enqueue it
            $gfont_arr=array();

        if(is_array($selected_fonts)){

            foreach ($selected_fonts as $font) {
                if ($font && $font != 'inherit') {
                    if ($font == 'Raleway')
                        $font = 'Raleway:100';
                    $font = str_replace(" ", "+", $font);
                     $gfont_arr[]=$font;
                }
            }
           if(is_array($gfont_arr)){
             $allfonts=implode("|",$gfont_arr);
	             if($allfonts){
	             wp_register_style("ctl-gfonts", "https://fonts.googleapis.com/css?family=$allfonts", false, null, 'all');
	       		  }
              }
           
		  }
		  	// includes google fonts
            wp_register_style("ctl-default-fonts", "https://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic,800", false, COOL_TIMELINE_CURRENT_VERSION, 'all');
	
			wp_register_style('ctl-styles', COOL_TIMELINE_PLUGIN_URL . 'assets/css/ctl_styles.min.css',null, COOL_TIMELINE_CURRENT_VERSION,'all' );	
		
		  	// register popup assets
			wp_register_style('ctl-prettyPhoto', COOL_TIMELINE_PLUGIN_URL . 'assets/css/prettyPhoto.css', null, COOL_TIMELINE_CURRENT_VERSION, 'all');
			wp_register_script('ctl-prettyPhoto', COOL_TIMELINE_PLUGIN_URL . 'assets/js/jquery.prettyPhoto.js', array('jquery'), COOL_TIMELINE_CURRENT_VERSION, true);  
			// includes slick slider for horizontal timeline
			wp_register_script('ctl-slick-js','https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), COOL_TIMELINE_CURRENT_VERSION, true);
			wp_register_style("ctl-slick-css", "https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css");
			
			wp_register_script('ctl-scripts', COOL_TIMELINE_PLUGIN_URL . 'assets/js/ctl_scripts.min.js', array('jquery'), COOL_TIMELINE_CURRENT_VERSION, false);
			wp_register_script('ctl-horizontal-tm-js', COOL_TIMELINE_PLUGIN_URL . 'assets/js/ctl_hori_scripts.min.js', array('jquery'), COOL_TIMELINE_CURRENT_VERSION, false);
			// load settings for compacy layout
			wp_register_script('ctl-masonry', COOL_TIMELINE_PLUGIN_URL . 'assets/js/masonry.pkgd.min.js', array('jquery'), COOL_TIMELINE_CURRENT_VERSION, false);
			wp_register_script('ctl-compact-js', COOL_TIMELINE_PLUGIN_URL . 'assets/js/ctl_compact_scripts.min.js', array('jquery','ctl-masonry'), COOL_TIMELINE_CURRENT_VERSION, false);
		  	// on scroll animations 
			wp_register_style('aos-css',COOL_TIMELINE_PLUGIN_URL. 'assets/css/aos.css', null, COOL_TIMELINE_CURRENT_VERSION, 'all');
			wp_register_script('aos-js', COOL_TIMELINE_PLUGIN_URL . 'assets/js/aos.js', array('jquery'), COOL_TIMELINE_CURRENT_VERSION, true);
	  
			 }
		// loading js and css according to the layout type in shortcode	 
		function ctl_load_assets($layout,$ctl_animation){
			// all common assets
		
			wp_enqueue_style('ctl-default-fonts');
			wp_enqueue_style('ctl-gfonts');
			wp_enqueue_style('ctl-prettyPhoto');
			wp_enqueue_script('ctl-prettyPhoto');	
			wp_enqueue_style('ctl-styles');
			// vertical layout only assets.
			if($layout!="horizontal"){
				if($ctl_animation !='none'){
					wp_enqueue_script('aos-js');
					wp_enqueue_style('aos-css');
				}
				
				wp_enqueue_script('ctl-scripts');
			}
		// compact layout dynamic Js
		if($layout == "compact"){
			wp_enqueue_script('ctl-masonry');
			wp_enqueue_script( 'ctl-compact-js');
			
			}else if($layout== "horizontal"){
				 // Horizontal Timeline Assets.
				wp_enqueue_style('ctl-slick-css');
				wp_enqueue_script('ctl-slick-js');
				wp_enqueue_script('ctl-horizontal-tm-js');
			}	
			// include fontawesome 5 version
			wp_enqueue_style('ctl-font-awesome',CT_FA_URL.'css/font-awesome/css/all.min.css');
			wp_enqueue_style('ctl-font-shims','https://use.fontawesome.com/releases/v5.7.2/css/v4-shims.css'); 
		
		}	 
	
		// dyamic class according to the year
		function ctl_story_cls($year){
			$now = new DateTime();
			$yNow = $now->format('Y');

			if($year<$yNow){
				return "past-year";
			}else if($year>$yNow){
				return "future-year";
			}else{
			return "current-year";	
			}
		}

    }

} // end class


