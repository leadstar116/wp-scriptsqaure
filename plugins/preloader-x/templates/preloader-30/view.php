<?php
/**
 * View - Preloader 30
 *
 * @package preloader-30/view.php
 * @copyright Pluginbazar 2020
 */

$primary_color   = preloader()->get_args_option( 'primary_color' );
$bg_color        = preloader()->get_args_option( 'bg_color' );
?>

<div <?php preloader_wrapper_classes(); ?>>
    <div class="preloader-general-inner">
        <div class="shape1"></div>
        <div class="shape2"></div>
        <div class="shape3"></div>
    </div>

	<?php if ( ! empty( $primary_color ) || ! empty( $bg_color ) ): ?>
        <style>
            .preloader-x {
                background-color: <?php echo esc_attr( $bg_color ); ?>;
            }
            .preloader-30 .preloader-general-inner > div[class*="shape"] {
                background-color: <?php echo esc_attr( $primary_color ); ?>;
            }
        </style>
	<?php endif; ?>
</div>