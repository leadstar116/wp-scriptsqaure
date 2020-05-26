<?php
/*
 * Plugin Name: Listeo - Forms&Fields Editor
 * Version: 1.4.2
 * Plugin URI: http://www.purethemes.net/
 * Description: Editor for Listeo - Directory Plugin from Purethemes.net
 * Author: Purethemes.net
 * Author URI: http://www.purethemes.net/
 * Requires at least: 4.7
 * Tested up to: 4.8.2
 *
 * Text Domain: listeo-fafe
 * Domain Path: /languages/
 *
 * @package WordPress
 * @author Lukasz Girek
 * @since 1.0.0
 */


class Listeo_Forms_And_Fields_Editor {


    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;

	/**
     * Initiate our hooks
     * @since 0.1.0
     */
	public function __construct($file = '', $version = '1.0.0') {
        $this->_version = $version;
        add_action( 'admin_menu', array( $this, 'add_options_page' ) ); //create tab pages
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) ); 

        // Load plugin environment variables
        $this->file = __FILE__;
        $this->dir = dirname( $this->file );
        $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

        include( 'includes/class-listeo-forms-builder.php' );
        include( 'includes/class-listeo-fields-builder.php' );
        include( 'includes/class-listeo-reviews-criteria.php' );
        include( 'includes/class-listeo-user-fields-builder.php' );
        include( 'includes/class-listeo-submit-builder.php' );
        //include( 'includes/class-listeo-import-export.php' );

        $this->fields  = Listeo_Fields_Editor::instance();
        $this->submit  = Listeo_Submit_Editor::instance();
        $this->forms  = Listeo_Forms_Editor::instance();
        //$this->users  = Listeo_User_Fields_Editor::instance();
        //$this->import_export  = Listeo_Forms_Import_Export::instance();
        $this->reviews_criteria  = Listeo_Reviews_Criteria::instance();
        
        add_action( 'admin_init', array( $this,'listeo_process_settings_export' ));
        add_action( 'admin_init', array( $this,'listeo_process_settings_import' ));
        
    }


    public function enqueue_scripts_and_styles($hook){

    if ( !in_array( $hook, array('listeo-editor_page_listeo-submit-builder','listeo-editor_page_listeo-forms-builder','listeo-editor_page_listeo-fields-builder','listeo-editor_page_listeo-reviews-criteria') ) ){
        return;
    }

        wp_enqueue_script('listeo-fafe-script', esc_url( $this->assets_url ) . 'js/admin.js', array('jquery','jquery-ui-droppable','jquery-ui-draggable', 'jquery-ui-sortable', 'jquery-ui-dialog','jquery-ui-resizable'));
        
        wp_register_style( 'listeo-fafe-styles', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
        wp_enqueue_style( 'listeo-fafe-styles' );
        wp_enqueue_style (  'wp-jquery-ui-dialog');
    }

      /**
     * Add menu options page
     * @since 0.1.0
     */
    public function add_options_page() {        
        
            add_menu_page('Listeo Forms and Fields Editor', 'Listeo Editor', 'manage_options', 'listeo-fields-and-form',array( $this, 'output' ));
               
            //add_submenu_page( 'listeo-fields-and-form', 'Property Fields', 'Property Fields', 'manage_options', 'realte-fields-builder', array( $this, 'output' ));
    }

    public function output(){ 
        if ( ! empty( $_GET['import'] ) ) {
                echo '<div class="updated"><p>' . __( 'The file was imported successfully.', 'listeo' ) . '</p></div>';
        }?>
        <div class="metabox-holder">
            <div class="postbox">
                <h3><span><?php _e( 'Export Settings' ); ?></span></h3>
                <div class="inside">
                    <p><?php _e( 'Export fields and forms settings for this site as a .json file. This allows you to easily import the configuration into another site or make a backup.' ); ?></p>
                    <form method="post">
                        <p><input type="hidden" name="listeo_action" value="export_settings" /></p>
                        <p>
                            <?php wp_nonce_field( 'listeo_export_nonce', 'listeo_export_nonce' ); ?>
                            <?php submit_button( __( 'Export' ), 'secondary', 'submit', false ); ?>
                        </p>
                    </form>
                </div><!-- .inside -->
            </div><!-- .postbox -->

            <div class="postbox">
                <h3><span><?php _e( 'Import Settings' ); ?></span></h3>
                <div class="inside">
                    <p><?php _e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.' ); ?></p>
                    <form method="post" enctype="multipart/form-data">
                        <p>
                            <input type="file" name="import_file"/>
                        </p>
                        <p>
                            <input type="hidden" name="listeo_action" value="import_settings" />
                            <?php wp_nonce_field( 'listeo_import_nonce', 'listeo_import_nonce' ); ?>
                            <?php submit_button( __( 'Import' ), 'secondary', 'submit', false ); ?>
                        </p>
                    </form>
                </div><!-- .inside -->
            </div><!-- .postbox -->
        </div><!-- .metabox-holder -->
        <?php
    }

   
    /**
         * Process a settings export that generates a .json file of the shop settings
         */
        function listeo_process_settings_export() {

            if( empty( $_POST['listeo_action'] ) || 'export_settings' != $_POST['listeo_action'] )
                return;

            if( ! wp_verify_nonce( $_POST['listeo_export_nonce'], 'listeo_export_nonce' ) )
                return;

            if( ! current_user_can( 'manage_options' ) )
                return;

            $settings = array();
            $settings['property_types']         = get_option('listeo_property_types_fields');
            $settings['property_rental']        = get_option('listeo_rental_periods_fields');
            $settings['property_offer_types']   = get_option('listeo_offer_types_fields');

            $settings['submit']                 = get_option('listeo_submit_form_fields');
            
            $settings['price_tab']              = get_option('listeo_price_tab_fields');
            $settings['main_details_tab']       = get_option('listeo_main_details_tab_fields');
            $settings['details_tab']            = get_option('listeo_details_tab_fields');
            $settings['location_tab']           = get_option('listeo_locations_tab_fields');

            $settings['sidebar_search']         = get_option('listeo_sidebar_search_form_fields');
            $settings['full_width_search']      = get_option('listeo_full_width_search_form_fields');
            $settings['half_map_search']        = get_option('listeo_search_on_half_map_form_fields');
            $settings['home_page_search']       = get_option('listeo_search_on_home_page_form_fields');
            $settings['home_page_alt_search']   = get_option('listeo_search_on_home_page_alt_form_fields');

            ignore_user_abort( true );

            nocache_headers();
            header( 'Content-Type: application/json; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename=listeo-settings-export-' . date( 'm-d-Y' ) . '.json' );
            header( "Expires: 0" );

            echo json_encode( $settings );
            exit;
        }

        /**
     * Process a settings import from a json file
     */
    function listeo_process_settings_import() {

        if( empty( $_POST['listeo_action'] ) || 'import_settings' != $_POST['listeo_action'] )
            return;

        if( ! wp_verify_nonce( $_POST['listeo_import_nonce'], 'listeo_import_nonce' ) )
            return;

        if( ! current_user_can( 'manage_options' ) )
            return;

        $extension = end( explode( '.', $_FILES['import_file']['name'] ) );

        if( $extension != 'json' ) {
            wp_die( __( 'Please upload a valid .json file' ) );
        }

        $import_file = $_FILES['import_file']['tmp_name'];

        if( empty( $import_file ) ) {
            wp_die( __( 'Please upload a file to import' ) );
        }

        // Retrieve the settings from the file and convert the json object to an array.
        $settings = json_decode( file_get_contents( $import_file ), true );

        update_option('listeo_property_types_fields'   ,$settings['property_types']);
        update_option('listeo_rental_periods_fields'   ,$settings['property_rental']);
        update_option('listeo_offer_types_fields'      ,$settings['property_offer_types']);

        update_option('listeo_submit_form_fields'      ,$settings['submit']);

        update_option('listeo_price_tab_fields'        ,$settings['price_tab']);
        update_option('listeo_main_details_tab_fields' ,$settings['main_details_tab']);
        update_option('listeo_details_tab_fields'      ,$settings['details_tab']);
        update_option('listeo_locations_tab_fields'    ,$settings['location_tab']);

        update_option('listeo_sidebar_search_form_fields',$settings['sidebar_search']);
        update_option('listeo_full_width_search_form_fields',$settings['full_width_search']);
        update_option('listeo_search_on_half_map_form_fields',$settings['half_map_search']);
        update_option('listeo_search_on_home_page_form_fields',$settings['home_page_search']);
        update_option('listeo_search_on_home_page_alt_form_fields',$settings['home_page_alt_search']);

       
        wp_safe_redirect( admin_url( 'admin.php?page=listeo-fields-and-form&import=success' ) ); exit;

    }

 
}

$Listeo_Form_Editor = new Listeo_Forms_And_Fields_Editor();