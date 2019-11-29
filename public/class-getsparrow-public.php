<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://getsparrow.io
 * @since      1.0.0
 *
 * @package    Getsparrow
 * @subpackage Getsparrow/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Getsparrow
 * @subpackage Getsparrow/public
 * @author     Sparrow <hello@getsparrow.io>
 */
class Getsparrow_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The options name to be used in this plugin
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	string 		$option_name 	Option name of this plugin
	 */
	private $option_name = 'getsparrow_io';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Getsparrow_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Getsparrow_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/getsparrow-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Getsparrow_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Getsparrow_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/getsparrow-public.js', array( 'jquery' ), $this->version, false );
      
      	wp_enqueue_script( 'getsparrow_io_widgets', '//getsparrow.github.io/widgets/latest.min.js' );

	}

	public function remove_native_reviews_widget() {
		add_filter( 'woocommerce_product_tabs', 'getsparrow_io_remove_product_tabs', 98 );

		function getsparrow_io_remove_product_tabs( $tabs ) {

			// unset( $tabs['description'] );      	// Remove the description tab
			unset( $tabs['reviews'] ); 			// Remove the reviews tab
			// unset( $tabs['additional_information'] );  	// Remove the additional information tab

			return $tabs;

		}
	}

	public function setup_rich_snippet() {
		add_action('wp_head', array($this, 'add_rich_snippet'));
		
	}
	
	public function add_rich_snippet() {
		
		if ( class_exists( 'woocommerce' ) && is_product()) {

			global $product;

			try {
				
				$client = new \GuzzleHttp\Client();
				$res = $client->request('GET', 'https://app.getsparrow.io/api/v1/reviews/?page=1&dataProductIdentifier='.$product->get_id().'&dataUrl='.get_permalink($product->get_id()), [
					'headers' => [
						'Authorization' => 'Bearer ' . get_option('getsparrow_io_access_token')
					]
				]);

				$data = json_decode($res->getBody()->getContents());
			} catch(\Exception $e) {
				return;
			}
			
			$schema = [
				"@context" => "http://schema.org",
				"@id" => get_permalink($product->get_id()) . "#product",
				"@type" => "Product",
				"name" => $product->get_name(),
				"image" => wp_get_attachment_url( $product->get_image_id() ),
				"aggregateRating" => [
					"@type" => "AggregateRating",
					"ratingValue" => $data->meta->average_rating,
					"reviewCount" => $data->meta->total
				  ]
			];

// 			if($data->meta->total > 0) {
				echo '<script type="application/ld+json">';
				echo json_encode($schema);
				echo '</script>';
// 			}
		}
		
	}
  
  	public function setup_review_widget() {

		$displayReviewWidget = get_option( $this->option_name . '_display_review_widget' );

		if($displayReviewWidget == true) {
			
				
			$reviews_tab_position = get_option( $this->option_name . '_reviews_tab_position' );

			if($reviews_tab_position == 'before_related_products') {
				add_action( 'woocommerce_after_single_product_summary', 'getsparrow_io_reviews_widget', 15 );

				// add_action( 'woocommerce_after_single_product_summary', 'getsparrow_io_reviews_widget', 5 );
				function getsparrow_io_reviews_widget() {
					call_user_func(array(__CLASS__, 'display_reviews_widget'));
				}
			} elseif($reviews_tab_position == 'after_related_products') {
				add_action( 'woocommerce_after_single_product', 'getsparrow_io_reviews_widget', 5 );

				// add_action( 'woocommerce_after_single_product_summary', 'getsparrow_io_reviews_widget', 5 );
				function getsparrow_io_reviews_widget() {
					call_user_func(array(__CLASS__, 'display_reviews_widget'));
				}
			} else {
				add_filter( 'woocommerce_product_tabs', 'getsparrow_io_reviews_widget' );
				function getsparrow_io_reviews_widget( $tabs ) {
					$tabs['desc_tab'] = array(
						'title'     => __( 'Reviews', 'woocommerce' ),
						'priority'  => 50,
						'callback'  => array( __CLASS__, 'display_reviews_widget' ),
					);
					return $tabs;
				}
			}
		}
      
  	}
	  
	public function setup_star_rating_widget() {

		$displayStarRating = get_option( $this->option_name . '_display_star_rating' );

		if($displayStarRating == true) {
			
			add_action( 'woocommerce_after_shop_loop_item_title', 'getsparrow_io_star_rating_widget' );
			function getsparrow_io_star_rating_widget() {
				include 'partials/getsparrow-public-display-star-rating.php';
			}
			
			add_action( 'woocommerce_single_product_summary', 'getsparrow_io_replace_star_rating', 4 );
			function getsparrow_io_replace_star_rating() {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
				add_action( 'woocommerce_single_product_summary', 'getsparrow_io_star_rating_widget', 9 );
			}
		}
	}
  
  	public function display_reviews_widget() {
		include_once 'partials/getsparrow-public-display.php';
	}

	public function display_star_rating_widget() {
		include_once 'partials/getsparrow-public-display.php';
	}

}
