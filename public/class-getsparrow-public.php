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
class Getsparrow_Public
{

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
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/getsparrow-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/getsparrow-public.js', array('jquery'), $this->version, false);

		wp_enqueue_script('getsparrow_io_widgets', '//cdn.jsdelivr.net/gh/getsparrow/getsparrow.github.io@v1.0.0-beta.14/widgets/latest.min.js');
		// wp_enqueue_script('getsparrow_io_widgets', '//cdn.jsdelivr.net/gh/getsparrow/getsparrow.github.io@development/widgets/latest.min.js');
	}

	public function remove_native_reviews_widget()
	{

		if ( 'yes' === get_option( 'woocommerce_enable_reviews', 'yes' ) ) {
			update_option('woocommerce_enable_reviews', 'no');
		}

		
		function getsparrow_io_remove_product_tabs($tabs) {
			
			unset($tabs['reviews']); 			// Remove the reviews tab
			return $tabs;
		}
		add_filter('woocommerce_product_tabs', 'getsparrow_io_remove_product_tabs', 98);
	}

	public function setup_rich_snippet()
	{
		add_action('wp_head', array($this, 'add_rich_snippet'));
	}

	public function add_rich_snippet()
	{

		if (class_exists('woocommerce') && is_product()) {

			global $product;

			$product = wc_get_product();

			try {
				$stack = \GuzzleHttp\HandlerStack::create();

				$stack->push(
					new \Kevinrob\GuzzleCache\CacheMiddleware(
						new \Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy(
							new \Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage(
								new \Doctrine\Common\Cache\FilesystemCache('/tmp/sparrow-cache/')
							),
							86400, // the TTL in seconds
							new \Kevinrob\GuzzleCache\KeyValueHttpHeader(['Authorization']) // Optional - specify the headers that can change the cache key
						)
					),
					'greedy-cache'
				);

				// Initialize the client with the handler option
				$client = new \GuzzleHttp\Client(['handler' => $stack]);

				// $client = new \GuzzleHttp\Client();
				$res = $client->request('GET', 'https://app.getsparrow.io/api/v1/reviews/?page=1&dataProductIdentifier=' . $product->get_id() . '&dataUrl=' . get_permalink($product->get_id()), [
					'headers' => [
						'Authorization' => 'Bearer ' . get_option('getsparrow_io_access_token')
					]
				]);

				$data = json_decode($res->getBody()->getContents());
			} catch (\Exception $e) {
				return;
			}

			if (is_null($data)) {
				return;
			}

			$embeddedReviewSchema = [];

			foreach ($data->data as $review) {
				array_push($embeddedReviewSchema, [
					"@type" => "Review",
					"reviewRating" => [
						"@type" => "Rating",
						"ratingValue" => $review->rating,
					],
					"author" => [
						"@type" => "Person",
						"name" => $review->customer->first_name
					],
					"reviewBody" => $review->text
				]);
			}

			$schema = [
				"@context" => "http://schema.org",
				"@id" => get_permalink($product->get_id()) . "#product",
				"@type" => "Product",
				"name" => $product->get_name(),
				"image" => wp_get_attachment_url($product->get_image_id()),
				"aggregateRating" => [
					"@type" => "AggregateRating",
					"ratingValue" => $data->meta->average_rating,
					"reviewCount" => $data->meta->total
				],
				"review" => $embeddedReviewSchema
			];

			if ($data->meta->total > 0) {
				echo '<script type="application/ld+json">';
				echo json_encode($schema);
				echo '</script>';
			}
		}
	}

	public function setup_review_widget()
	{

		$displayReviewWidget = get_option($this->option_name . '_display_review_widget');

		if ($displayReviewWidget == true) {


			$reviews_tab_position = get_option($this->option_name . '_reviews_tab_position');

			if ($reviews_tab_position == 'before_related_products') {
				$that = $this;
				
				add_action('woocommerce_after_single_product_summary', function() use(&$that) {
					$that->display_reviews_widget();
				}, 15);

			} elseif ($reviews_tab_position == 'after_related_products') {
				$that = $this;
				add_action('woocommerce_after_single_product', function() use(&$that) {
					$that->display_reviews_widget();
				}, 5);
				
			} else {

				$that = $this;
				
				add_filter('woocommerce_product_tabs', function($tabs) use(&$that) {
					
					$tabs['getsparrow_widget'] = array(
						'title'     => __('Reviews', 'woocommerce'),
						'priority'  => 50,
						'callback'  => function() use(&$that) {
							$that->display_reviews_widget();
						}
					);

					return $tabs;
				}, 98);
			}
		}
	}

	public function setup_star_rating_under_product_title()
	{

		$displayStarRating = get_option($this->option_name . '_display_star_rating_below_product_title');

		if ($displayStarRating == true) {

			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
			add_action('woocommerce_single_product_summary', 'getsparrow_io_replace_star_rating', 9);
			function getsparrow_io_replace_star_rating()
			{
				include 'partials/getsparrow-public-display-star-rating.php';
			}
		}
	}

	public function setup_star_rating_in_product_card()
	{

		$displayStarRating = get_option($this->option_name . '_display_star_rating_in_product_cards');

		if ($displayStarRating == true) {

			add_action('woocommerce_after_shop_loop_item_title', 'getsparrow_io_star_rating_widget');
			function getsparrow_io_star_rating_widget()
			{
				include 'partials/getsparrow-public-display-star-rating.php';
			}
		}
	}


	public function handle_sparrow_widgets_shortcode($attributes, $content = null)
	{

		global $product;
		if (!is_null($product)) {
			ob_start();
?>

			<sparrow-reviews data-access-token="<?php echo get_option('getsparrow_io_access_token'); ?>" data-product-identifier="<?php echo $product->get_id(); ?>" data-platform="woocommerce" data-price="<?php echo (strlen($product->get_regular_price()) ? $product->get_regular_price() : 0); ?>" data-sale-price="<?php echo (strlen($product->get_sale_price()) ? $product->get_sale_price() : $product->get_price()); ?>" data-currency="<?php echo get_woocommerce_currency(); ?>" data-name="<?php echo $product->get_name(); ?>" data-url="<?php echo get_permalink($product->get_id()); ?>" data-image-url="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'full')[0]; ?>" data-description="<?php echo (strlen($product->get_short_description())) ? $product->get_short_description() : ((strlen($product->get_description())) ? $product->get_description() : ''); ?>"></sparrow-reviews>

<?php
		}
		return ob_get_clean();
	}



	public static function display_reviews_widget()
	{
		include_once 'partials/getsparrow-public-display.php';
	}

	public static function display_star_rating_widget()
	{
		include_once 'partials/getsparrow-public-display.php';
	}
}
