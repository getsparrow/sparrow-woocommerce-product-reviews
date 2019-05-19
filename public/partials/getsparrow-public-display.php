<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://getsparrow.io
 * @since      1.0.0
 *
 * @package    Getsparrow
 * @subpackage Getsparrow/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php global $product; ?>

<sparrow-reviews
	data-access-token="<?php echo get_option('getsparrow_io_access_token'); ?>"
      data-product-identifier="<?php echo $product->id; ?>"
      data-platform="woocommerce"
      data-price="<?php echo (strlen($product->regular_price)? $product->regular_price : 0); ?>"
      data-sale-price="<?php echo (strlen($product->sale_price)? $product->sale_price : $product->price); ?>"
      data-currency="<?php echo get_woocommerce_currency(); ?>"
      data-name="<?php echo $product->name; ?>"
      data-url="<?php echo get_permalink($product->id); ?>"
      data-image-url="<?php echo wp_get_attachment_image_src( get_post_thumbnail_id( $product->id ), 'full' )[0]; ?>"
      data-description="<?php echo (strlen($product->short_description)) ? $product->short_description : ((strlen($product->description)) ? $product->description : ''); ?>"
	></sparrow-reviews>