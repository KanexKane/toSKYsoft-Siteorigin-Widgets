<?php
/*
Widget Name: toSKYsoft Product Categories Carousel
Description: Gives you a widget to display your product categories as a carousel.
Author: toSKYsoft
Author URI: https://www.toskysoft.com
*/

/**
 * Add the carousel image sizes
 */
function tss_sow_carousel_register_image_sizes()
{
	$gallery_thumbnail = wc_get_image_size('gallery_thumbnail');
	add_image_size('tss_shop_thumbnail_small', 50, 50, $gallery_thumbnail['crop']);
}
add_action('init', 'tss_sow_carousel_register_image_sizes');

function tss_sow_product_cat_carousel_get_next_posts_page() 
{
	if ( empty( $_REQUEST['_widgets_nonce'] ) || !wp_verify_nonce( $_REQUEST['_widgets_nonce'], 'widgets_action' ) ) return;
	
	$template_vars = array();
	if ( ! empty( $_GET['instance_hash'] ) ) 
	{
		$instance_hash = $_GET['instance_hash'];
		global $wp_widget_factory;
        /** @var SiteOrigin_Widget $widget */
		$widget = ! empty ( $wp_widget_factory->widgets['Toskysoft_SiteOrigin_Widgets_ProductCatCarousel_Widget'] ) ?
            $wp_widget_factory->widgets['Toskysoft_SiteOrigin_Widgets_ProductCatCarousel_Widget'] : null;
		if ( ! empty( $widget ) ) {
            $instance = $widget->get_stored_instance($instance_hash);
            $instance['paged'] = $_GET['paged'];
            $template_vars = $widget->get_template_variables($instance, array());
        }
	}
	ob_start();
	extract( $template_vars );
	include 'tpl/carousel-post-loop.php';
	$result = array( 'html' => ob_get_clean() );
	header('content-type: application/json');
	echo json_encode( $result );

	exit();
}
add_action('wp_ajaxtss_sow_product_cat_carousel_load', 'tss_sow_product_cat_carousel_get_next_posts_page' );
add_action('wp_ajax_nopriv_tss_sow_product_cat_carousel_load', 'tss_sow_product_cat_carousel_get_next_posts_page' );

class Toskysoft_SiteOrigin_Widgets_ProductCatCarousel_Widget extends SiteOrigin_Widget 
{
	/**
	 * Category ancestors.
	 *
	 * @var array
	 */
	public $cat_ancestors;

	/**
	 * Current Category.
	 *
	 * @var bool
	 */
	public $current_cat;

	function __construct() 
	{
		parent::__construct(
			'tss-sow-productcat-carousel',
			__('toSKYsoft SiteOrigin Product Categories Carousel', 'tss-so-widgets'),
			array(
				'description' => __('Display your product categories as a carousel.', 'tss-so-widgets'),
				'instance_storage' => true,
				//'help' => 'https://siteorigin.com/widgets-bundle/post-carousel-widget/'
			),
			array(

			),
			false ,
			plugin_dir_path(__FILE__).'../'
		);
	}

	function initialize() {
		$this->register_frontend_scripts(
			array(
				array(
					'touch-swipe',
					plugin_dir_url( SOW_BUNDLE_BASE_FILE ) . 'js/jquery.touchSwipe' . SOW_BUNDLE_JS_SUFFIX . '.js',
					array( 'jquery' ),
					'1.6.6'
				),
				array(
					'sow-carousel-basic',
					plugin_dir_url(__FILE__) . 'js/carousel' . SOW_BUNDLE_JS_SUFFIX . '.js',
					array( 'jquery', 'touch-swipe' ),
					SOW_BUNDLE_VERSION,
					true
				)
			)
		);
		$this->register_frontend_styles(
			array(
				array(
					'sow-carousel-basic',
					plugin_dir_url(__FILE__) . 'css/style.css',
					array(),
					SOW_BUNDLE_VERSION
				)
			)
		);
	}

	function get_widget_form(){
		return array(
			'title' => array(
				'type' => 'text',
				'label' => __('Title', 'so-widgets-bundle'),
			),

			'default_thumbnail' => array(
				'type'     => 'media',
				'library'  => 'image',
				'label'    => __( 'Default Thumbnail', 'so-widgets-bundle' ),
				'choose'   => __( 'Choose Thumbnail', 'so-widgets-bundle' ),
				'update'   => __( 'Set Thumbnail', 'so-widgets-bundle' ),
				'fallback' => true,
			),

			'image_size' => array(
				'type' => 'image-size',
				'label' => __('Featured Image size', 'so-widgets-bundle'),
				'default' => 'sow-carousel-default',
			),

			// 'posts' => array(
			// 	'type' => 'posts',
			// 	'label' => __('Posts query', 'so-widgets-bundle'),
			// 	'hide' => true,
			// ),

			'orderby' => array(
				'type' => 'select',
				'std' => 'name',
				'label' => __('Order by', 'tss-so-widgets'),
				'options' => array(
					'order' => __('Category order', 'tss-so-widgets'),
					'name' => __('Name', 'tss-so-widgets'),
				),
			),

			'hide_empty' => array(
				'type' => 'checkbox',
				'std' => 0,
				'label' => __('Hide empty categories', 'tss-so-widgets'),
			),
		);
	}

	function get_less_variables( $instance ) {
		$size = siteorigin_widgets_get_image_size( $instance['image_size'] );

		$thumb_width = '';
		$thumb_height = '';
		$thumb_hover_width = '';
		$thumb_hover_height = '';
		if ( ! ( empty( $size['width'] ) || empty( $size['height'] ) ) ) {
			$thumb_width = $size['width'] - $size['width'] * 0.1;
			$thumb_height = $size['height'] - $size['height'] * 0.1;
			$thumb_hover_width = $size['width'];
			$thumb_hover_height = $size['height'];
		}

		return array(
			'thumbnail_width' => $thumb_width . 'px',
			'thumbnail_height'=> $thumb_height . 'px',
			'thumbnail_hover_width' => $thumb_hover_width . 'px',
			'thumbnail_hover_height'=> $thumb_hover_height . 'px',
		);
	}

	public function get_template_variables( $instance, $args ) {
		if ( ! empty( $instance['default_thumbnail'] ) ) {
			$default_thumbnail = wp_get_attachment_image_src( $instance['default_thumbnail'], 'sow-carousel-default' );
		}
		
		// $query = wp_parse_args(
		// 	siteorigin_widget_post_selector_process_query( $instance['posts'] ),
		// 	array(
		// 		'paged' => empty( $instance['paged'] ) ? 1 : $instance['paged']
		// 	)
		// );

		$orderby = isset($instance['orderby']) ? $instance['orderby'] : $this->settings['orderby']['std'];
		$hide_empty = isset($instance['hide_empty']) ? $instance['hide_empty'] : $this->settings['hide_empty']['std'];

		$list_args = array(
			'show_count' 	=> false,
			'hierarchical' 	=> true,
			'taxonomy' 		=> 'product_cat',
			'hide_empty' 	=> $hide_empty,
			'menu_order' 	=> false,
		);

		if ('order' === $orderby) 
		{
			$list_args['menu_order'] = 'asc';
		} 
		else 
		{
			$list_args['orderby'] = 'title';
		}

		if (is_tax('product_cat')) 
		{
			$this->current_cat = $wp_query->queried_object;
			$this->cat_ancestors = get_ancestors($this->current_cat->term_id, 'product_cat');

		} 
		elseif (is_singular('product')) 
		{
			$product_category = wc_get_product_terms($post->ID, 'product_cat', apply_filters('woocommerce_product_categories_widget_product_terms_args', array(
				'orderby' => 'parent',
			)));

			if (!empty($product_category)) {
				$this->current_cat = end($product_category);
				$this->cat_ancestors = get_ancestors($this->current_cat->term_id, 'product_cat');
			}
		}

		$categories = get_categories($list_args );
		
		//$posts = new WP_Query( $list_args );
		
		return array(
			'title' => $instance['title'],
			'categories' => $categories,
			'default_thumbnail' => ! empty( $default_thumbnail ) ? $default_thumbnail[0] : '',
		);
	}

	function get_template_name($instance){
		return 'base';
	}
}

siteorigin_widget_register('tss-sow-productcat-carousel', __FILE__, 'Toskysoft_SiteOrigin_Widgets_ProductCatCarousel_Widget');
