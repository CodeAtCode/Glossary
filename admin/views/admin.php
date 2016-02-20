<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @license   GPL-2.0+
 * @link      http://codeat.co
 * @copyright 2016 GPL 2.0+
 */
?>

<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <div id="tabs" class="settings-tab">
        <ul>
            <li><a href="#tabs-1"><?php _e( 'Settings' ); ?></a></li>
            <li><a href="#tabs-3"><?php _e( 'Import/Export', $this->plugin_slug ); ?></a></li>
        </ul>
        <div id="tabs-1" class="wrap">
            <?php
            $cmb = new_cmb2_box( array(
                'id' => $this->plugin_slug . '_options',
                'hookup' => false,
                'show_on' => array( 'key' => 'options-page', 'value' => array( $this->plugin_slug ), ),
                'show_names' => true,
                    ) );
            $cmb->add_field( array(
                'name' => __( 'Add support in specific post type', $this->plugin_slug ),
                'id' => 'posttypes',
                'type' => 'multicheck_posttype',
            ) );
            $cmb->add_field( array(
                'name' => __( 'Link only the first occurence', $this->plugin_slug ),
                'id' => 'first_occurence',
                'type' => 'checkbox',
            ) );
            $cmb->add_field( array(
                'name' => __( 'Choose when enable', $this->plugin_slug ),
                'id' => 'is',
                'type' => 'multicheck',
                'options' => array(
                    'home' => __( 'Home', $this->plugin_slug ),
                    'category' => __( 'Category archive', $this->plugin_slug ),
                    'tag' => __( 'Tag archive', $this->plugin_slug ),
                    'arc_glossary' => __( 'Glossary Archive', $this->plugin_slug ),
                    'tax_glossary' => __( 'Glossary Taxonomy', $this->plugin_slug )
                )
            ) );
            $cmb->add_field( array(
                'name' => __( 'Enable tooltip on terms', $this->plugin_slug ),
                'id' => 'tooltip',
                'type' => 'checkbox',
            ) );
            $cmb->add_field( array(
                'name' => __( 'Tooltip style', $this->plugin_slug ),
                'id' => 'tooltip_style',
                'type' => 'select',
                'options' => array(
                    'classic' => 'Classic',
                    'box' => 'Box',
                    'line' => 'Line',
                )
            ) );
            $cmb->add_field( array(
                'name' => __( 'Excerpt char size', $this->plugin_slug ),
                'id' => 'excerpt_limit',
                'type' => 'text_number',
                'default' => '60'
            ) );
            $cmb->add_field( array(
                'name' => __( 'Enable image in tooltip', $this->plugin_slug ),
                'description' => __( 'Check it if you want also term\'s featured image showing on hover', $this->plugin_slug ),
                'id' => 't_image',
                'type' => 'checkbox',
            ) );
            cmb2_metabox_form( $this->plugin_slug . '_options', $this->plugin_slug . '-settings' );
            ?>

            <!-- @TODO: Provide other markup for your options page here. -->
        </div>
        <div id="tabs-3" class="metabox-holder">
            <div class="postbox">
                <h3 class="hndle"><span><?php _e( 'Export Settings', $this->plugin_slug ); ?></span></h3>
                <div class="inside">
                    <p><?php _e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.', $this->plugin_slug ); ?></p>
                    <form method="post">
                        <p><input type="hidden" name="g_action" value="export_settings" /></p>
                        <p>
                            <?php wp_nonce_field( 'g_export_nonce', 'g_export_nonce' ); ?>
                            <?php submit_button( __( 'Export' ), 'secondary', 'submit', false ); ?>
                        </p>
                    </form>
                </div>
            </div>

            <div class="postbox">
                <h3 class="hndle"><span><?php _e( 'Import Settings', $this->plugin_slug ); ?></span></h3>
                <div class="inside">
                    <p><?php _e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', $this->plugin_slug ); ?></p>
                    <form method="post" enctype="multipart/form-data">
                        <p>
                            <input type="file" name="g_import_file"/>
                        </p>
                        <p>
                            <input type="hidden" name="g_action" value="import_settings" />
                            <?php wp_nonce_field( 'g_import_nonce', 'g_import_nonce' ); ?>
                            <?php submit_button( __( 'Import' ), 'secondary', 'submit', false ); ?>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="right-column-settings-page metabox-holder">
        <div class="postbox">
            <h3 class="hndle"><span><?php _e( 'Plugin by', $this->plugin_slug ); ?></span></h3>
            <div class="inside">
                <a href="http://codeat.co"><img src="http://codeat.co/wp-content/uploads/2016/02/logo-light-admin.png" alt="Codeat"></a>
            </div>
        </div>
    </div>
</div>
