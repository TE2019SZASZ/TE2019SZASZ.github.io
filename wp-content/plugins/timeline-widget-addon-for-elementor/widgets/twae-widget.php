<?php
use Elementor\Widget_Base;
use Elementor\Utils;
use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Scheme_Color;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Typography;

class TWAE_Widget extends \Elementor\Widget_Base {

	public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
		wp_register_style( 'twae-centered-css', TWAE_URL  . 'assets/css/twae-centered-timeline.min.css', array());	
		wp_register_style( 'twae-horizontal-css', TWAE_URL  . 'assets/css/twae-horizontal-styles.min.css', array());	
		wp_register_style( 'twae-fontello-css', TWAE_URL  . 'assets/css/twae-fontello.css', array());	
		wp_register_script( 'twae-horizontal-js', TWAE_URL  . 'assets/js/twae-horizontal.min.js',[ 'elementor-frontend' ],null, true );	
		
	 }
 
     public function get_script_depends() {
		if (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) {
			return [ 'twae-horizontal-js' ];
		}
		 $settings = $this->get_settings_for_display();
		$layout = $settings['twae_layout'];
		if($layout == 'horizontal'){
			return [ 'twae-horizontal-js' ];
		}else{
			return [];	
		}
     }
 
     public function get_style_depends() {
		$styles = ['twae-fontello-css'];
		if (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) {
			return [ 'twae-centered-css','twae-horizontal-css','twae-fontello-css' ];
		}
		$settings = $this->get_settings_for_display();
		$layout = $settings['twae_layout']; 

		if($layout == 'horizontal'){
			array_push($styles, 'twae-horizontal-css');
		}else{
			array_push($styles, 'twae-centered-css');
		}
		
		return $styles ;
     }
	
	public function get_name() {
		return 'timeline-widget-addon';
	}

	public function get_title() {
		return __( 'Timeline Widget Addon', 'twae' );
	}

	public function get_icon() {
		return 'eicon-time-line';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'twae_layout_section',
			[
				'label' => __( 'Layout Settings', 'twae' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
	
		$this->add_control(
			'twae_layout',
			[
				'label' => __( 'Layout', 'twae' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'centered',
				'options'=>[
					'centered'=>'Centered',
					'one-sided'=>'One Sided',
					'horizontal'=>'Horizontal',
				],
				'default' => 'centered',
			]
		);
				
		$this->add_control(
			'twae_slides_to_show',
			[
				'label' => __( 'Slides To Show', 'twae' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '2',
				'condition'   => [
					'twae_layout'   => [
					   'horizontal'
					],
				]
			]
		);

		$this->add_control(
			'twae_slides_height',
			[
				'label' => __( 'Equal Height Slides', 'twae' ),
				'description' => __('Make all slides the same height based on the tallest slide','twae'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'no-height',
				'options'=>[
					'auto-height'=>'Yes',
					'no-height'=>'No',
				],
				'condition'   => [
					'twae_layout'   => [
					   'horizontal'
					],
				]
			]
		);

		$this->add_control(
			'twae_autoplay',
			[
				'label' => __( 'Autoplay', 'twae' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'false',
				'options'=>[
					'true'=>'True',
					'false'=>'False',
				],
				'condition'   => [
					'twae_layout'   => [
					   'horizontal'
					],
				]
			]
		);

		
		$this->end_controls_section();

		
		$this->start_controls_section(
			'twae_typography_section',
			[
				'label' => __( 'Typography Settings', 'twae' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


		/*---- Year Label ----*/
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'twae_year_typography',
				'label' => __( 'Year Typography', 'twae' ),
				'scheme' => Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .twae-wrapper .twae-year',
				'fields_options' => [
					// first mimic the click on Typography edit icon
					'typography' => ['default' => 'yes'],
					// then redifine the Elementor defaults
					'font_size' => ['default' => [ 'unit' => 'px', 'size' => 16 ]],
					'font_weight' => ['default' => 'bold']
				],
			]
		);


		/*---- Date / Custom Label ----*/
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'twae_label_typography',
				'label' => __( 'Story Label Typography', 'twae' ),
				'scheme' => Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .twae-wrapper span.twae-label',
				'fields_options' => [
					// first mimic the click on Typography edit icon
					'typography' => ['default' => 'yes'],
					// then redifine the Elementor defaults
					'font_size' => ['default' => [ 'unit' => 'px', 'size' => 20 ]],
					'font_weight' => ['default' => 600]
				],
			]
		);


		/*---- Small Label Below Date Label ----*/
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'twae_extra_label_typography',
				'label' => __( 'Story Extra Label Typography', 'twae' ),
				'scheme' => Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .twae-wrapper span.twae-extra-label',
				'fields_options' => [
					// first mimic the click on Typography edit icon
					'typography' => ['default' => 'yes'],
					// then redifine the Elementor defaults
					'font_size' => ['default' => [ 'unit' => 'px', 'size' => 15 ]],
					'font_weight' => ['default' => 'normal']
				],
			]
		);


		/*---- Story Title ----*/
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'twae_title_typography',
				'label' => __( 'Story Title Typography', 'twae' ),
				'scheme' => Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .twae-wrapper span.twae-title',
				'fields_options' => [
					// first mimic the click on Typography edit icon
					'typography' => ['default' => 'yes'],
					// then redifine the Elementor defaults
					'font_size' => ['default' => [ 'unit' => 'px', 'size' => 20 ]],
					'font_weight' => ['default' => 600]
				],
			]
		);


		/*---- Story Description ----*/
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'twae_description_typography',
				'label' => __( 'Story Description Typography', 'twae' ),
				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .twae-wrapper .twae-description',
				'fields_options' => [
					// first mimic the click on Typography edit icon
					'typography' => ['default' => 'yes'],
					// then redifine the Elementor defaults
					'font_size' => ['default' => [ 'unit' => 'px', 'size' => 16 ]],
					'font_weight' => ['default' => 'normal']
				],
			]
		);


		/*---- Story ICON ----*/
		$this->add_control(
			'twae_icon_size',
			[
				'label' => __( 'Icon Size', 'twae' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 12,
						'max' => 36,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .twae-wrapper .twae-icon i' => 'font-size: {{SIZE}}{{UNIT}}',
				],
				//'default' => '.75rem',
			]
		);

		$this->add_control(
			'twae_icon_padding',
			[
				'label' => __( 'Icon Padding', 'twae' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .twae-wrapper .twae-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'twae_style_section',
			[
				'label' => __( 'Color Settings', 'twae' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'twae_year_label_color',
			[
				'label' => __( 'Year/Label Color', 'twae' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}} .twae-wrapper .twae-year' => 'color: {{twae_year_label_color}}',
				],
				'default' => '#ffffff',
			]
		);

		$this->add_control(
			'twae_year_label_bgcolor',
			[
				'label' => __( 'Year/Label Background Color', 'twae' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}} .twae-wrapper .twae-year' => 'background-color: {{twae_year_label_bgcolor}}',
				],
				'default' => '#54595F',
			]
		);

		$this->add_control(
			'twae_date_label_color',
			[
				'label' => __( 'Story Date/Label Color', 'twae' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}} .twae-wrapper span.twae-label' => 'color: {{twae_date_label_color}}',
				],
				'default' => '#23A455',
			]
		);

		$this->add_control(
			'twae_extra_label_color',
			[
				'label' => __( 'Story Extra Label Color', 'twae' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}} .twae-wrapper span.twae-extra-label' => 'color: {{twae_extra_label_color}}',
				],
				'default' => '#7A7A7A',
			]
		);

		$this->add_control(
			'twae_story_title_color',
			[
				'label' => __( 'Story Title Color', 'twae' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}} .twae-wrapper .twae-data-container span.twae-title' => 'color: {{twae_story_title_color}}',
					'{{WRAPPER}} .twae-horizontal span.twae-title' => 'color: {{twae_story_title_color}}',
				],
				'default' => '#23A455',
			]
		);

		$this->add_control(
			'twae_description_color',
			[
				'label' => __( 'Story Description Color', 'twae' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}} .twae-wrapper .twae-description' => 'color: {{twae_description_color}}',
				],
				'default' => '#333333',
			]
		);

		$this->add_control(
			'twae_line_color',
			[
				'label' => __( 'Line Color', 'twae' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}} .twae-wrapper .twae-line::before' => 'background-color: {{twae_line_color}}',
					'{{WRAPPER}} .twae-wrapper .twae-timeline-centered .twae-icon' => 'border-color: {{twae_line_color}}',
					'{{WRAPPER}} .twae-wrapper .twae-timeline-centered .twae-year' => 'border-color: {{twae_line_color}}',
					'{{WRAPPER}} .twae-wrapper:before' => 'background-color: {{twae_line_color}}',
					'{{WRAPPER}} .twae-wrapper:after' => 'background-color: {{twae_line_color}}',
					'{{WRAPPER}} .twae-horizontal .twae-pagination.swiper-pagination-progressbar' => 'background-color: {{twae_line_color}}',
					'{{WRAPPER}} .twae-horizontal .twae-button-prev' => 'color: {{twae_line_color}}',
					'{{WRAPPER}} .twae-horizontal .twae-button-next' => 'color: {{twae_line_color}}',
				],
				'default' => '#D6D6D6',
			]
		);

		$this->add_control(
			'twae_icon_bgcolor',
			[
				'label' => __( 'Icon Background Color', 'twae' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}} .twae-wrapper .twae-icon' => 'background-color: {{twae_icon_bgcolor}}',
					'{{WRAPPER}} .twae-horizontal .twae-story-info' => 'border-color: {{twae_icon_bgcolor}}',	
					'{{WRAPPER}} .twae-horizontal .twae-story-info:before' => 'border-bottom-color: {{twae_icon_bgcolor}}',	
					'{{WRAPPER}} .twae-wrapper .twae-data-container:after' => 'border-right-color: {{twae_icon_bgcolor}}',
					'{{WRAPPER}} .twae-wrapper .twae-right-aligned .twae-data-container' => 'border-left-color: {{twae_icon_bgcolor}}',
					'{{WRAPPER}} .twae-wrapper .twae-left-aligned .twae-data-container' => 'border-right-color: {{twae_icon_bgcolor}}',
					'body[data-elementor-device-mode=mobile] {{WRAPPER}} .twae-wrapper .twae-left-aligned .twae-data-container' => 'border-left-color: {{twae_icon_bgcolor}}',
				],
				'default' => '#23A455',
			]
		);

		$this->add_control(
			'twae_story_bgcolor',
			[
				'label' => __( 'Story Background Color', 'twae' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'selectors' => [
					'{{WRAPPER}} .twae-wrapper .twae-data-container' => 'background-color: {{twae_story_bgcolor}}',
					'{{WRAPPER}} .twae-wrapper .twae-icon' => 'color: {{twae_icon_bgcolor}}',
					'{{WRAPPER}} .twae-horizontal .twae-story-info' => 'background-color: {{twae_icon_bgcolor}}',
				],
				'default' => '#fff9ed',

			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'twae_content_section',
			[
				'label' => __( 'Timeline Story Settings', 'twae' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'twae_show_year_label',
			[
				'label' => __( 'Year/Label', 'twae' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'twae' ),
				'label_off' => __( 'Hide', 'twae' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);	
		
		$repeater->add_control(
			'twae_year',
			[
				'label' => __( 'Year/Label', 'twae' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '2020',
				'condition'   => [
					'twae_show_year_label'   => [
					   'yes'
					],
				]

			]
		);

		$repeater->add_control(
			'twae_date_label',
			[
				'label' => __( 'Story Date/Label', 'twae' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '01 Jan 2020',
			]
		);

		$repeater->add_control(
			'twae_extra_label',
			[
				'label' => __( 'Extra Label', 'twae' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Extra Label',
			]
		);

		$repeater->add_control(
			'twae_story_title',
			[
				'label' => __( 'Timeline Story Title', 'twae' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Timeline Story',
			]
		);

		$repeater->add_control(
			'twae_image',
			[
				'label' => __( 'Choose Image', 'twae' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'description' => __('Image Size will not work with default image','twae'),
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);
			
		$repeater->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'twae_thumbnail', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.
				'separator' => 'none',
			]
		);

		$repeater->add_control(
			'twae_description',
			[
				'label' => __( 'Description', 'twae' ),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => 'Add Description Here',
			]
		);

		$repeater->add_control(
			'twae_story_icon',
			[
				'label' => __( 'Icon', 'twae' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fab fa-amazon',
					'library' => 'solid',
				],
			]
		);		

		$this->add_control(
			'twae_list',
			[
				
				'label' => __( 'Timeline Widget Addon For Elementor', 'twae' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'twae_story_title' => __( 'Amazon Founded', 'twae' ),
						'twae_description' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Erat enim res aperta. Ne discipulum abducam, times. Primum quid tu dicis breve? An haec ab eo non dicuntur?','twae'),
						'twae_year'			=> __('1994','twae'),
						'twae_date_label'   => __('Jul 1994','twae'),
						'twae_extra_label'  => __('Amazon History','twae'),
						'twae_image' =>[
							'url' => TWAE_URL  . 'assets/images/amazon-1.png',	
							'id' => '',						
						],
					],
					[
						'twae_story_title' => __( 'Amazon Prime Services', 'twae' ),
						'twae_description' => __('Aliter homines, aliter philosophos loqui putas oportere? Sin aliud quid voles, postea. Mihi enim satis est, ipsis non satis. Negat enim summo bono afferre incrementum diem. Quod ea non occurrentia fingunt, vincunt Aristonem.','twae'),
						'twae_year'			=> __('2005','twae'),
						'twae_date_label'   => __('Feb 2005','twae'),
						'twae_extra_label'  => __('Amazon History','twae'),
						'twae_image' =>[
							'url' => TWAE_URL  . 'assets/images/amazon-2.png',
							'id' => '',							
						],
						
					],
					[
						'twae_story_title' => __( 'Amazon Announced Amazon Fresh Pickup', 'twae' ),
						'twae_description' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.','twae'),
						'twae_year'			=> __('2007','twae'),
						'twae_date_label'   => __('Aug 2007','twae'),
						'twae_extra_label'  => __('Amazon History','twae'),
						'twae_image' =>[
							'url' => TWAE_URL  . 'assets/images/amazon-3.png',
							'id' => '',						
						],
					],
				],
				'title_field' => '{{{ twae_story_title }}}',
			]
		);

		$this->end_controls_section();

	}

	// for frontend
	protected function render() {

		$settings = $this->get_settings_for_display();
		$data	  = $settings['twae_list'];
		$layout = $settings['twae_layout'];
		$autoplay = $settings['twae_autoplay'];
		$sidesHeight = $settings['twae_slides_height'];
		
		$isRTL = is_rtl();
		$dir = '';
		if($isRTL){
			$dir = 'rtl';
		}

			$timeline_layout_wrapper = "twae-centered";
			$timeline_layout = '';
			if($layout == 'one-sided'){
				$timeline_layout = "twae-one-sided-timeline";
				$timeline_layout_wrapper = "twae-one-sided-wrapper";
			}

			$countItem = 1;
			if($layout == 'horizontal'){
				$timeline_layout = "twae-horizontal-timeline";
				$timeline_layout_wrapper = "twae-horizontal-wrapper";
				require TWAE_PATH . 'widgets/frontend-layouts/twae-horizontal-timeline.php';
							
				
			}else{
				require TWAE_PATH . 'widgets/frontend-layouts/twae-centered-timeline.php';
			}
			
		
	}

	// for live editor
 	protected function _content_template() {
		
		?>
	<#
		if( settings.twae_list ) {
			
			#>
				<?php
				$isRTL = is_rtl();
				$dir = '';
				if($isRTL){
					$dir = 'rtl';
				}
				?>
			<#	
			
			if(settings.twae_layout == 'horizontal'){
				var sidesToShow = settings.twae_slides_to_show;
				var sidesHeight = settings.twae_slides_height;
				var autoplay = settings.twae_autoplay;
				if(sidesToShow==''){
					sidesToShow = 2;
				}
				#>
				<?php require TWAE_PATH . 'widgets/editor-layouts/horizontal-template.php';
						
				?>
				<#		
				
			}
			else{
				#>	
			
				<?php require TWAE_PATH . 'widgets/editor-layouts/vertical-template.php'; ?>
			<#
				}		
		}	
		
		#>
		<?php 
		
	} 

}

\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TWAE_Widget() );

