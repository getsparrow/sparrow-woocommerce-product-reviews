<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://getsparrow.io
 * @since      1.0.0
 *
 * @package    Getsparrow
 * @subpackage Getsparrow/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Getsparrow
 * @subpackage Getsparrow/admin
 * @author     Sparrow <hello@getsparrow.io>
 */
class Getsparrow_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/getsparrow-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/getsparrow-admin.js', array( 'jquery' ), $this->version, false );

	}
  
  /**
	 * Add an options page under the Settings submenu
	 *
	 * @since  1.0.0
	 */
	public function add_options_page() {
	
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Sparrow Settings', 'getsparrow_io' ),
			__( 'Sparrow', 'getsparrow_io' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_options_page' )
		);
	
	}

	public function check_installation() {

		if(get_option('getsparrow_io_access_token') == null) {
			echo '<div class="notice notice-error is-dismissible">
					<p>
						<strong>Welcome to Sparrow</strong> - you are almost ready to start generating product reviews.</br>
						<a style="margin-top: .75rem" href="options-general.php?page=getsparrow" class="button-primary">Complete Setup</a>
					</p>
				</div>';
		}

		if ( !file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) { 

			$action = 'install-plugin';
			$slug = 'woocommerce';
			$url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => $action,
						'plugin' => $slug
					),
					admin_url( 'update.php' )
				),
				$action.'_'.$slug
			);

			echo '<div class="notice notice-error is-dismissible">
					<p>
						<strong>Woocommerce is not installed</strong> - install and activate Woocommerce to get the best out of Sparrow.</br>
						<a style="margin-top: .75rem" href="'.$url.'" class="button-primary">Install and activate</a>
					</p>
				</div>';
		} elseif(! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$path = 'woocommerce/woocommerce.php';
			$link = wp_nonce_url(admin_url('plugins.php?action=activate&plugin='.$path), 'activate-plugin_'.$path);

			echo '<div class="notice notice-error is-dismissible">
					<p>
						<strong>Woocommerce is not active</strong> - install and activate Woocommerce to get the best out of Sparrow.</br>
						<a style="margin-top: .75rem" href="'.$link.'" class="button-primary">Activate</a>
					</p>
				</div>';
		}
		
	}

	/**
	 * Add settings link to plugin page
	 *
	 * @param $links
	 * @return mixed
	 */
	public function add_settings_link($links)
	{
		$settings_link = '<a href="options-general.php?page='.$this->plugin_name.'">' . __('Settings') . '</a>';
		array_push($links, $settings_link);
		return $links;
	}
  
  /**
	 * Render the options page for plugin
	 *
	 * @since  1.0.0
	 */
	public function display_options_page() {
		include_once 'partials/getsparrow-admin-display.php';
	}
  
  public function register_settings() {
	
	// Add a General section
	add_settings_section(
		$this->option_name . '_general',
		__( 'General', 'getsparrow_io' ),
		array( $this, $this->option_name . '_general_cb' ),
		$this->plugin_name
	);

    add_settings_field(
		$this->option_name . '_access_token',
		__( '<br/>Access Token</br><br/><small><em>Please enter Sparrow Access Token.</em></small><br/><br/><small>Don\'t have an Access Token? You can geenrate one from your accounts dashboard <a target="_blank" href="https://app.getsparrow.com/account/tokens">here</a>.</small></br></br><small>Don\'t have a Sparrow account? Sign up for one <a target="_blank" href="https://getsparrow.com">here</a></small>', 'getsparrow_io' ),
		array( $this, $this->option_name . '_access_token_cb' ),
		$this->plugin_name,
		$this->option_name . '_general',
		array( 'label_for' => $this->option_name . '_access_token' )
	);
	register_setting( $this->plugin_name, $this->option_name . '_access_token', 'string' );

	add_settings_field(
		$this->option_name . '_display_star_rating',
		__( 'Display Star Rating?<br/><br/><span><small><em>Enable star rating display below the product card on category and loops?</em></small></span>', 'getsparrow_io' ),
		array( $this, $this->option_name . '_display_star_rating_cb' ),
		$this->plugin_name,
		$this->option_name . '_general',
		array( 'label_for' => $this->option_name . '_display_star_rating' )
	);
	register_setting( $this->plugin_name, $this->option_name . '_display_star_rating', 'boolean' );
	
	add_settings_field(
		$this->option_name . '_display_review_widget',
		__( 'Display Reviews Widget?<br/><br/><span><small><em>Enable star rating display below the product card on category and loops?</em></small></span>', 'getsparrow_io' ),
		array( $this, $this->option_name . '_display_review_widget_cb' ),
		$this->plugin_name,
		$this->option_name . '_general',
		array( 'label_for' => $this->option_name . '_display_review_widget' )
	);
    register_setting( $this->plugin_name, $this->option_name . '_display_review_widget', 'boolean' );
	
	add_settings_field(
		$this->option_name . '_reviews_tab_position',
		__( 'Reviews Tab Position?<br/><br/><small><em>Where do you want to display the reviews widget</em></small>', 'getsparrow_io' ),
		array( $this, $this->option_name . '_reviews_tab_position_cb' ),
		$this->plugin_name,
		$this->option_name . '_general',
		array( 'label_for' => $this->option_name . '_reviews_tab_position' )
	);
    register_setting( $this->plugin_name, $this->option_name . '_reviews_tab_position', 'string' );
    
  }
  
  /**
	 * Render the text for the general section
	 *
	 * @since  1.0.0
	 */
	public function getsparrow_io_general_cb() {
		echo '<p>' . __( '', 'getsparrow_io' ) . '</p>';
	}
  
  /**
	 * Render the radio input field for position option
	 *
	 * @since  1.0.0
	 */
	public function getsparrow_io_reviews_tab_position_cb() {
		$reviews_tab_position = get_option( $this->option_name . '_reviews_tab_position' );
		echo '<select name="'.$this->option_name.'_reviews_tab_position" id="'.$this->option_name.'_reviews_tab_position">';
		echo
			"<option value='tab' " . selected('tab', $reviews_tab_position, false) . ">Tab</option>
			<option value='before_related_products' " . selected('before_related_products', $reviews_tab_position, false) . ">Before Related Products</option>
			<option value='after_related_products' " . selected('after_related_products', $reviews_tab_position, false) . ">After Related Products</option>
		</select>";
		// $access_token = get_option( $this->option_name . '_reviews_tab_position' );

		// echo '<input type="text" name="' . $this->option_name . '_access_token' . '" id="' . $this->option_name . '_access_token' . '" value="' . $access_token . '"> ';
	}

	public function getsparrow_io_access_token_cb() {
		$access_token = get_option( $this->option_name . '_access_token' );
		echo '<textarea cols="100" rows="10" type="text" name="' . $this->option_name . '_access_token' . '" id="' . $this->option_name . '_access_token' . '">' . $access_token . '</textarea>';
	}

	public function getsparrow_io_display_star_rating_cb() {
		$displayStarRating = get_option( $this->option_name . '_display_star_rating' );
		// echo '<input type="checkbox" name="' . $this->option_name . '_display_star_rating' . '" id="' . $this->option_name . '_display_star_rating"' .  checked(true, $displayStarRating, false) . ' value="true" />';
		echo '<input type="checkbox" name="' . $this->option_name . '_display_star_rating' . '" id="' . $this->option_name . '_display_star_rating' . '" value="1" '. checked(1, $displayStarRating, false) .' />';
	}
	
	/**
	 * Displays checkbox for display reviews widget
	 *
	 * @since  1.0.5
	 *
	 */
	public function getsparrow_io_display_review_widget_cb() {
		$displayReviewWidget = get_option( $this->option_name . '_display_review_widget' );
		// echo '<input type="checkbox" name="' . $this->option_name . '_display_star_rating' . '" id="' . $this->option_name . '_display_star_rating"' .  checked(true, $displayStarRating, false) . ' value="true" />';
		echo '<input type="checkbox" name="' . $this->option_name . '_display_review_widget' . '" id="' . $this->option_name . '_display_review_widget' . '" value="1" '. checked(1, $displayReviewWidget, false) .' />';
	}

	/**
	 * Sanitize the text position value before being saved to database
	 *
	 * @param  string $position $_POST value
	 * @since  1.0.0
	 * @return string           Sanitized value
	 */
	public function getsparrow_io_sanitize_position( $position ) {
		if ( in_array( $position, array( 'before', 'after' ), true ) ) {
	        return $position;
	    }
	}
}
