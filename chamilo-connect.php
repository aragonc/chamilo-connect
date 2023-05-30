<?php
/*
Plugin Name: Chamilo Connect
Description: Allows you to connect your Chamilo virtual classroom through a user and course control panel
Version: 1.0
Author: Alex Aragon
Author URI: https://github.com/aragonc
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: chamilo
*/


use function app\wisdmlabs\edwiserBridge\wdm_eb_update_page_id;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function chamilo_activation(){
    global $wpdb;
    $sql = 'CREATE TABLE '.$wpdb->prefix.'chamilo ('
        .'url_chamilo VARCHAR(255) NOT NULL,'
        .'secret_key VARCHAR(256) NOT NULL,'
        .');';
    $wpdb->get_results($sql);
    chamilo_create_pages_user();
}

function chamilo_deactivation(){
    global $wpdb;
    $sql = 'DROP TABLE '.$wpdb->prefix.'chamilo;';
    $wpdb->get_results($sql);
    chamilo_delete_pages();
}

echo "plugin activado";

register_activation_hook(__FILE__, 'chamilo_activation');
register_deactivation_hook(__FILE__, 'chamilo_deactivation');

add_action('admin_menu','chamilo_create_menu');

function chamilo_create_menu(){
    add_menu_page(
        'Configuracion Chamilo',
        'Chamilo Connect',
        'manage_options',
        plugin_dir_path(__FILE__).'admin/configuration.php',
        null,
        plugin_dir_url(__FILE__).'images/chamilo.svg',
        '2'
    );
}
function chamilo_create_pages_user(){
    $template_dir = plugin_dir_path(__FILE__) . 'templates/';
    $pages = apply_filters(
        'create_default_pages',
        array(
            'useraccount' => array(
                'name'       => esc_html_x( 'user-account', 'Page slug', 'chamilo-connect' ),
                'title'      => esc_html_x( 'User Account', 'Page title', 'chamilo-connect' ),
                'content'    => '[user_account]',
                'option_key' => 'chamilo_useraccount_page_id',
                'template' => $template_dir.'tpl_user_account.php'
            ),
            'mycourses'   => array(
                'name'       => esc_html_x( 'my-courses', 'Page slug', 'chamilo-connect' ),
                'title'      => esc_html_x( 'My Courses', 'Page title', 'chamilo-connect' ),
                'content'    => '[user_my_courses]',
                'option_key' => 'chamilo_my_courses_page_id',
                'template' => $template_dir.'tpl_user_my_courses.php'
            ),
            'certificates'     => array(
                'name'       => esc_html_x( 'my-certificates', 'Page slug', 'chamilo-connect' ),
                'title'      => esc_html_x( 'My Certificates', 'Page title', 'chamilo-connect' ),
                'content'    => '[user_my_certificates]',
                'option_key' => 'chamilo_courses_page_id',
                'template' => $template_dir.'tpl_user_my_certificates.php'
            ),
        )
    );
    foreach ( $pages as $page ) {
        chamilo_create_page( esc_sql( $page['name'] ), $page['option_key'], $page['title'], $page['content'] );
    }
}

function chamilo_delete_pages(){
    $slugs = [
        'user-account',
        'my-courses',
        'my-certificates'
    ];
    foreach ( $slugs as $slug ) {
        // Obtener el objeto de la página por su slug
        $page = get_page_by_path($slug);
        if ($page){
            // Eliminar la página
            $result = wp_delete_post($page->ID, true);
        }
    }

}

if ( ! function_exists( 'chamilo_create_page' ) ) {
    /**
     * Create a page and store the ID in an option.
     *
     * @param mixed  $slug         Slug for the new page.
     * @param string $option_key   Option name to store the page's ID.
     * @param string $page_title   (default: '') Title for the new page.
     * @param string $page_content (default: '') Content for the new page.
     *
     * @return int page ID
     */
    function chamilo_create_page( $slug, $option_key = '', $page_title = '', $page_content = '', $page_template = '' ): int
    {
        global $wpdb;

        // get all settings of settings general tab.
        $eb_general_settings = array();
        $eb_general_settings = get_option( 'chamilo_general', array() );

        $option_value = 0;
        if ( '' !== trim( $option_key ) && isset( $eb_general_settings[ $option_key ] ) ) {
            $option_value = $eb_general_settings[ $option_key ];
        }

        if ( $option_value > 0 && get_post( $option_value ) ) {
            return -1;
        }

        if ( strlen( $page_content ) > 0 ) {
            // Search for an existing page with the specified page content (typically a shortcode).
            $page_found_id = $wpdb->get_var( // @codingStandardsIgnoreLine
                $wpdb->prepare(
                    'SELECT ID FROM ' . $wpdb->posts . "
					WHERE post_type='page' AND post_content LIKE %s LIMIT 1;",
                    "%{$page_content}%"
                )
            );
        } else {
            // Search for an existing page with the specified page slug.
            $page_found_id = $wpdb->get_var( // @codingStandardsIgnoreLine
                $wpdb->prepare(
                    'SELECT ID FROM ' . $wpdb->posts . "
					WHERE post_type='page' AND post_name = %s LIMIT 1;",
                    $slug
                )
            );
        }

        if ( $page_found_id ) {
            wdm_chamilo_update_page_id( $option_value, $option_key, $page_found_id, $eb_general_settings );
            return $page_found_id;
        }

        $page_data = array(
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => 1,
            'post_name'      => $slug,
            'post_title'     => $page_title,
            'post_content'   => $page_content,
            'comment_status' => 'closed',
            'page_template' => $page_template
        );
        $page_id   = wp_insert_post( $page_data );
        wdm_chamilo_update_page_id( $option_value, $option_key, $page_id, $eb_general_settings );
        return $page_id;
    }
}
if ( ! function_exists( 'wdm_chamilo_update_page_id' ) ) {
    /**
     * Create a page and store the ID in an option.
     *
     * @param mixed  $option_value   option_value.
     * @param string $option_key   Option name to store the page's ID.
     * @param string $_id   _id.
     * @param string $eb_general_settings eb_general_settings.
     */
    function wdm_chamilo_update_page_id( $option_value, $option_key, $_id, &$eb_general_settings ) {
        if ( ! empty( $option_key ) ) {
            $eb_general_settings[$option_key] = $_id;
            update_option( 'chamilo_general', $eb_general_settings );
        }
    }
}