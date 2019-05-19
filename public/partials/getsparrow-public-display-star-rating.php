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

<sparrow-stars
      data-access-token="<?php echo get_option('getsparrow_io_access_token'); ?>"
      data-product-identifier="<?php echo $product->id; ?>"
      data-url="<?php echo get_permalink($product->id); ?>"
      ></sparrow-stars>