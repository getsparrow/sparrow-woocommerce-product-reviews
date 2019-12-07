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
      data-product-identifier="<?php echo $product->get_id(); ?>"
      data-platform="woocommerce"
      data-price="<?php echo (strlen($product->get_regular_price())? $product->get_regular_price() : 0); ?>"
      data-sale-price="<?php echo (strlen($product->get_sale_price())? $product->get_sale_price() : $product->get_price()); ?>"
      data-currency="<?php echo get_woocommerce_currency(); ?>"
      data-name="<?php echo $product->get_name(); ?>"
      data-url="<?php echo get_permalink($product->get_id()); ?>"
      data-image-url="<?php echo wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ), 'full' )[0]; ?>"
      data-description="<?php echo (strlen($product->get_short_description())) ? $product->get_short_description() : ((strlen($product->get_description())) ? $product->get_description() : ''); ?>"
	></sparrow-reviews>