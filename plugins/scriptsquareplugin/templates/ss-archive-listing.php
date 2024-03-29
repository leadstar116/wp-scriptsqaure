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

if($top_layout == 'half') {
	$ss_template_loader->get_template_part( 'ss-archive-listing-half' );
} else {
	$sidebar_side = get_option('pp_listings_sidebar_layout');
	$drugs = get_option('scriptsquare_drugs_data');
?>

<!-- Content
================================================== -->
<div class="container <?php echo esc_attr($sidebar_side); if( $top_layout == 'map') { echo esc_attr(' margin-top-40'); } ?> ?>" >
	<div class="row sticky-wrapper">


			<?php switch ($sidebar_side) {
				case 'full-width':
					?><div class="col-md-12"><?php
					break;
				case 'left-sidebar':
					?><div class="col-lg-9 col-md-8 listings-column-content mobile-content-container"><?php
					break;
				case 'right-sidebar':
					?><div class="col-lg-9 col-md-8 padding-right-30 listings-column-content mobile-content-container"><?php
					break;

				default:
					?><div class="col-lg-9 col-md-8 padding-right-30 listings-column-content"><?php
					break;
			} ?>
			<!-- Search -->

			<?php
			if( $top_layout == 'search') {
				 echo do_shortcode('[listeo_search_form action='.get_post_type_archive_link( 'listing' ).' source="home" custom_class="gray-style margin-top-40 margin-bottom-40"]');
				} ?>

			<!-- Search Section / End -->

			<?php $top_buttons = get_option('listeo_listings_top_buttons');

			if($top_buttons=='enable'){
				$top_buttons_conf = get_option('listeo_listings_top_buttons_conf');
				if(is_array($top_buttons_conf) && !empty($top_buttons_conf)){
					$list_top_buttons = implode("|", $top_buttons_conf);
				}  else {
					$list_top_buttons = '';
				}
				?>
					<div class="row margin-bottom-15">
					<?php do_action( 'listeo_before_archive', $content_layout, $list_top_buttons ); ?>
				</div>
				<?php
			} ?>
				<?php
					$container_class = 'content-layout';
					$per_page = get_option('scriptsquare_items_per_page');
					$offset = 0; //page 1
				 ?>

				<!-- Listings -->
				<div class="listings-container <?php echo esc_attr($container_class) ?>" id="listeo-listings-container">
					<div class="loader-ajax-container" style=""> <div class="loader-ajax"></div> </div>
					<?php if($content_layout == 'list'): ?>
						<div class="row">
					<?php endif;
					if ( $drugs['success'] ) :
						for($i = $offset; $i < $offset+$per_page; $i++) {
							if($i >= count($drugs['content'])) {
								break;
							}
							update_option('scriptsquare_drug', $drugs['content'][$i]);
							$template_loader->get_template_part('content-listing');
						}
						?>
						<div class="clearfix"></div>
						</div>
						<div class="col-lg-12 col-md-12 pagination-container  margin-top-0 margin-bottom-60 ajax-search">
							<?php
								$pages = get_option('scriptsquare_max_num_pages');
								echo scriptsquare_ajax_pagination( $pages, 1 );
							?>
						</div>
						<?php
					else :
						$ss_template_loader->get_template_part( 'archive/ss-no-found' );
					endif; ?>
					<?php if($content_layout == 'list'): ?>
						</div>
					<?php endif; ?>
				</div>
		</div>
		<?php if($sidebar_side != 'full-width') : ?>
			<!-- Sidebar
			================================================== -->
			<div class="col-lg-3 col-md-4 listings-sidebar-content mobile-sidebar-container">
				<?php $template_loader->get_template_part( 'sidebar-listeo' );?>
			</div>
			<!-- Sidebar / End -->
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
<?php } //eof split ?>
