<?php
/**
 * The template for displaying listings
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package listeo
 */
$top_layout = get_option('pp_listings_top_layout','map');
($top_layout == 'half') ? get_header('split') : get_header();

$template_loader = new Listeo_Core_Template_Loader;
$ss_template_loader = new ScriptSquare_Template_Loader;

$content_layout = get_option('pp_listings_layout','list');

$drugs = get_option('scriptsquare_drugs_data');

$sidebar_side = get_option('pp_listings_sidebar_layout');
?>

<!-- Content
================================================== -->
<div class="fs-container">

	<div class="fs-inner-container content">
		<div class="fs-content">

			<!-- Search -->

			<section class="search">
				<a href="#" id="show-map-button" class="show-map-button" data-enabled="<?php  esc_attr_e('Show Map ','listeo'); ?>" data-disabled="<?php  esc_attr_e('Hide Map ','listeo'); ?>"><?php esc_html_e('Show Map ','listeo') ?></a>
				<div class="row">
					<div class="col-md-12">

							<?php echo do_shortcode('[listeo_search_form source="half" more_custom_class="margin-bottom-30"]'); ?>

					</div>
				</div>

			</section>
			<!-- Search / End -->

			<?php $content_layout = get_option('pp_listings_layout','list'); ?>
			<section class="listings-container margin-top-45">
				<!-- Sorting / Layout Switcher -->
				<div class="row fs-switcher">

					<!-- <div class="col-md-6">
						Showing Results
						<p class="showing-results">14 Results Found </p>
					</div> -->

					<?php $top_buttons = get_option('listeo_listings_top_buttons');

					if($top_buttons=='enable'){
						$top_buttons_conf = get_option('listeo_listings_top_buttons_conf');
						if(is_array($top_buttons_conf) && !empty($top_buttons_conf)){

							if (($key = array_search('radius', $top_buttons_conf)) !== false) {
							    unset($top_buttons_conf[$key]);
							}
							if (($key = array_search('filters', $top_buttons_conf)) !== false) {
							    unset($top_buttons_conf[$key]);
							}
							$list_top_buttons = implode("|", $top_buttons_conf);
						}  else {
							$list_top_buttons = '';
						}
						?>

						<?php do_action( 'listeo_before_archive', $content_layout, $list_top_buttons ); ?>

						<?php
					} ?>

				</div>

				<!-- Listings -->
				<div class="row fs-listings">

					<?php

					$container_class = 'content-layout';

					$data = '';
					$data .= ' data-region="'.get_query_var( 'region').'" ';
					$data .= ' data-category="'.get_query_var( 'listing_category').'" ';
					$data .= ' data-feature="'.get_query_var( 'listing_feature').'" ';
					$data .= ' data-service-category="'.get_query_var( 'service_category').'" ';
					$data .= ' data-rental-category="'.get_query_var( 'rental_category').'" ';
					$data .= ' data-event-category="'.get_query_var( 'event_category').'" ';
					$orderby_value = isset( $_GET['listeo_core_order'] ) ? (string) $_GET['listeo_core_order']  : get_option( 'listeo_sort_by','date' );
								?>
					<!-- Listings -->
					<div data-grid_columns="2" <?php echo $data; ?> data-orderby="<?php echo $orderby_value;  ?>" data-style="<?php echo esc_attr($content_layout) ?>" class="listings-container <?php echo esc_attr($container_class) ?>" id="listeo-listings-container">
						<div class="loader-ajax-container" style=""> <div class="loader-ajax"></div> </div>
						<?php
                        if ( $drugs['success'] ) {
                            $count = 0;
                            foreach($drugs['content'] as $drug) {
                                $count++;
                                if($count>20) break;
                                update_option('scriptsquare_drug', $drug);
                                $template_loader->get_template_part( 'content-listing' );
                            }
                        } else {
                            echo 'bbb'; exit;
							$ss_template_loader->get_template_part( 'archive/ss-no-found' );
						} ?>

						<div class="clearfix"></div>
					</div>
					<?php $ajax_browsing = get_option('listeo_ajax_browsing'); ?>
					<div class="pagination-container margin-top-45 margin-bottom-60 row ajax-search">
						<nav class="pagination col-md-12">
						<?php
							if($ajax_browsing == 'on') {
									global $wp_query;
     								$pages = $wp_query->max_num_pages;
									echo listeo_core_ajax_pagination( $pages, 1 );
							} else
							if(function_exists('wp_pagenavi')) {
								wp_pagenavi(array(
									'next_text' => '<i class="fa fa-chevron-right"></i>',
									'prev_text' => '<i class="fa fa-chevron-left"></i>',
									'use_pagenavi_css' => false,
									));
							} else {
								the_posts_navigation();
							}?>
						</nav>
					</div>
					<div class="copyrights margin-top-0"><?php $copyrights = get_option( 'pp_copyrights' , '&copy; Theme by Purethemes.net. All Rights Reserved.' );

				        if (function_exists('icl_register_string')) {
				            icl_register_string('Copyrights in footer','copyfooter', $copyrights);
				            echo icl_t('Copyrights in footer','copyfooter', $copyrights);
				        } else {
				            echo wp_kses($copyrights,array( 'a' => array('href' => array(),'title' => array()),'br' => array(),'em' => array(),'strong' => array(),));
				        } ?>

				    </div>
				</div>
			</section>

		</div>
	</div>
	<div class="fs-inner-container map-fixed">

		<!-- Map -->
		<div id="map-container" class="">
		    <div id="map" class="split-map" data-map-zoom="<?php echo get_option('listeo_map_zoom_global',9); ?>" data-map-scroll="true">
		        <!-- map goes here -->
		    </div>

		</div>

	</div>
</div>

<div class="clearfix"></div>

<?php get_footer('empty'); ?>
