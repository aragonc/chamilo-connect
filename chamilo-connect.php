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

require_once plugin_dir_path( __FILE__ ) . 'admin/ChamiloConnect.php';

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function chamilo_activation()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'chamilo_connect'; // Nombre de la tabla con prefijo de WordPress

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        meta_key varchar(255) NOT NULL,
        meta_value longtext NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    chamilo_create_pages_user();
}

function chamilo_deactivation()
{
    global $wpdb;
    $sql = 'DROP TABLE ' . $wpdb->prefix . 'chamilo;';
    $wpdb->get_results($sql);
    chamilo_delete_pages();

    // Eliminar los datos guardados en wp_options
    delete_option('chamilo_connect_url');
    delete_option('chamilo_connect_apikey');
}

register_activation_hook(__FILE__, 'chamilo_activation');
register_deactivation_hook(__FILE__, 'chamilo_deactivation');

add_action('admin_menu', 'chamilo_create_menu');

function chamilo_create_menu()
{
    add_menu_page(
        'Configuracion Chamilo',
        'Chamilo Connect',
        'manage_options',
        'chamilo_connect_configuration',
        'chamilo_configuration_configuration_callback',
        plugin_dir_url(__FILE__) . 'images/chamilo.svg',
        '2'
    );
}

//add css boostrap
// Incluir Bootstrap CSS
function resource_css() {
    $urlPlugin = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'bootstrap_css',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css',
        array(),
        '4.1.3'
    );
    wp_enqueue_style( 'chamilo_css',
        $urlPlugin.'css/style.css',
        array(),
        '1.0'
    );
    wp_enqueue_style( 'strength_css',
        $urlPlugin.'js/strength/strength.css',
        array(),
        '1.0'
    );
}
add_action( 'wp_enqueue_scripts', 'resource_css');

// Incluir Bootstrap JS y dependencia popper
function resource_js() {
    $urlPlugin = plugin_dir_url( __FILE__ );
    wp_enqueue_script( 'popper_js',
        'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js',
        array(),
        '1.14.3',
        true
    );
    wp_enqueue_script( 'bootstrap_js',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js',
        array('jquery','popper_js'),
        '4.1.3',
        true
    );
    wp_enqueue_script('strength_js',
        $urlPlugin.'js/strength/strength.min.js',
        array(),
        '1.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'resource_js');

function chamilo_create_pages_user()
{
    $template_dir = plugin_dir_path(__FILE__) . 'templates/';
    $pages = apply_filters(
        'create_default_pages',
        array(
            'useraccount' => array(
                'name' => esc_html_x('user-account', 'Page slug', 'chamilo-connect'),
                'title' => esc_html_x('User Account', 'Page title', 'chamilo-connect'),
                'content' => '[user_account]',
                'option_key' => 'chamilo_user_account_page_id',
                'template' => 'tpl_user_account.php'
            ),
            'dashboard' => array(
                'name' => esc_html_x('dashboard', 'Page slug', 'chamilo-connect'),
                'title' => esc_html_x('User Dashboard', 'Page title', 'chamilo-connect'),
                'content' => '[user_dashboard]',
                'option_key' => 'chamilo_user_dashboard_page_id',
                'template' => 'tpl_user_dashboard.php'
            ),
            'mycourses' => array(
                'name' => esc_html_x('my-courses', 'Page slug', 'chamilo-connect'),
                'title' => esc_html_x('My Courses', 'Page title', 'chamilo-connect'),
                'content' => '[user_my_courses]',
                'option_key' => 'chamilo_my_courses_page_id',
                'template' => 'tpl_user_my_courses.php'
            ),
            'certificates' => array(
                'name' => esc_html_x('my-certificates', 'Page slug', 'chamilo-connect'),
                'title' => esc_html_x('My Certificates', 'Page title', 'chamilo-connect'),
                'content' => '[user_my_certificates]',
                'option_key' => 'chamilo_certificates_page_id',
                'template' => 'tpl_user_my_certificates.php'
            ),
            'login' => array(
                'name' => esc_html_x('user-login', 'Page slug', 'chamilo-connect'),
                'title' => esc_html_x('Login', 'Page title', 'chamilo-connect'),
                'content' => '[user_login]',
                'option_key' => 'chamilo_login_page_id',
                'template' => 'tpl_user_login.php'
            ),
            'register' => array(
                'name' => esc_html_x('user-register', 'Page slug', 'chamilo-connect'),
                'title' => esc_html_x('Register', 'Page title', 'chamilo-connect'),
                'content' => '[user_register]',
                'option_key' => 'chamilo_register_page_id',
                'template' => 'tpl_user_register.php'
            ),
            'lostpassword' => array(
                'name' => esc_html_x('user-lostpassword', 'Page slug', 'chamilo-connect'),
                'title' => esc_html_x('Lost Password', 'Page title', 'chamilo-connect'),
                'content' => '[user_lostpassword]',
                'option_key' => 'chamilo_lostpassword_page_id',
                'template' => 'tpl_user_lostpassword.php'
            ),
        )
    );
    foreach ($pages as $page) {
        chamilo_create_page(esc_sql($page['name']), $page['option_key'], $page['title'], $page['content'], $page['template']);
    }
}

/**
 * Add "Custom" template to page attribute template section.
 */
function chamilo_add_template_to_select($post_templates, $wp_theme, $post, $post_type): array
{
    // Add custom template named template-custom.php to select dropdown
    return [
        'tpl_user_login.php' => __('User Login'),
        'tpl_user_account.php' => __('User Account'),
        'tpl_user_my_certificates.php' => __('User Certificates'),
        'tpl_user_my_courses.php' => __('User Courses'),
        'tpl_user_register.php' => __('User Register'),
        'tpl_user_lostpassword.php' => __('User Lost Password'),
        'tpl_user_dashboard.php' => __('User Dashboard')
    ];
}
add_filter( 'theme_page_templates', 'chamilo_add_template_to_select', 10, 4 );

/**
 * @throws Exception
 */
function chamilo_load_plugin_template($template) {
    $page_custom = get_page_template_slug();
    switch ($page_custom){
        case 'tpl_user_login.php':
            if ($theme_file = locate_template(array('tpl_user_login.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__).'templates/tpl_user_login.php';
            }
            break;
        case 'tpl_user_register.php':
            if ($theme_file = locate_template(array('tpl_user_register.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__).'templates/tpl_user_register.php';
            }
            break;
        case 'tpl_user_dashboard.php':
            if ($theme_file = locate_template(array('tpl_user_dashboard.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__).'templates/tpl_user_dashboard.php';
            }
            break;
        case 'tpl_user_account.php':
            if ($theme_file = locate_template(array('tpl_user_account.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__).'templates/tpl_user_account.php';
            }
            break;
        case 'tpl_user_my_certificates.php':
            if ($theme_file = locate_template(array('tpl_user_my_certificates.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__).'templates/tpl_user_my_certificates.php';
            }
            break;
        case 'tpl_user_my_courses.php':
            if ($theme_file = locate_template(array('tpl_user_my_courses.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__).'templates/tpl_user_my_courses.php';
            }
            break;
        case 'tpl_user_lostpassword.php':
            if ($theme_file = locate_template(array('tpl_user_lostpassword.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__).'templates/tpl_user_lostpassword.php';
            }
            break;
        default:
    }

    return $template;
}

add_filter( 'template_include', 'chamilo_load_plugin_template' );

if (!function_exists('chamilo_create_page')) {
    /**
     * Create a page and store the ID in an option.
     *
     * @param string $slug Slug for the new page.
     * @param string $option_key Option name to store the page's ID.
     * @param string $page_title (default: '') Title for the new page.
     * @param string $page_content (default: '') Content for the new page.
     * @param string $page_template (default: '') Theme page template.
     * @return int page ID
     */
    function chamilo_create_page(string $slug, string $option_key = '', string $page_title = '', string $page_content = '', string $page_template = ''): int
    {
        global $wpdb;

        // get all settings of settings general tab.
        //$eb_general_settings = array();
        $eb_general_settings = get_option('chamilo_general', array());

        $option_value = 0;
        if ('' !== trim($option_key) && isset($eb_general_settings[$option_key])) {
            $option_value = $eb_general_settings[$option_key];
        }

        if ($option_value > 0 && get_post($option_value)) {
            return -1;
        }

        if (strlen($page_content) > 0) {
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

        if ($page_found_id) {
            wdm_chamilo_update_page_id($option_value, $option_key, $page_found_id, $eb_general_settings);
            return $page_found_id;
        }

        $page_data = array(
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1,
            'post_name' => $slug,
            'post_title' => $page_title,
            'post_content' => $page_content,
            'comment_status' => 'closed',
            'page_template' => $page_template
        );

        $page_id = wp_insert_post($page_data);
        wdm_chamilo_update_page_id($option_value, $option_key, $page_id, $eb_general_settings);
        return $page_id;
    }
}
if (!function_exists('wdm_chamilo_update_page_id')) {
    /**
     * Create a page and store the ID in an option.
     *
     * @param mixed $option_value option_value.
     * @param string $option_key Option name to store the page's ID.
     * @param string $_id _id.
     * @param string $eb_general_settings eb_general_settings.
     */
    function wdm_chamilo_update_page_id($option_value, $option_key, $_id, &$eb_general_settings)
    {
        if (!empty($option_key)) {
            $eb_general_settings[$option_key] = $_id;
            update_option('chamilo_general', $eb_general_settings);
        }
    }
}

function chamilo_delete_pages()
{
    $slugs = [
        'user-account',
        'my-courses',
        'my-certificates',
        'user-login',
        'user-register',
        'user-lostpassword',
        'dashboard'
    ];
    foreach ($slugs as $slug) {
        // Obtener el objeto de la página por su slug
        $page = get_page_by_path($slug);
        if ($page) {
            // Eliminar la página
            wp_delete_post($page->ID, true);
        }
    }

}

/**
 * @throws \GuzzleHttp\Exception\GuzzleException
 */
function chamilo_configuration_configuration_callback()
{
    // Obtener los valores actuales de los datos guardados
    $urlChamilo = get_option('chamilo_connect_url');
    $apiKeyChamilo = get_option('chamilo_connect_apikey');
    $userAdminChamilo = get_option('chamilo_connect_username');
    $passAdminChamilo = get_option('chamilo_connect_password');

    if (isset($_POST['save'])) {
        $urlChamilo = $_POST['url_chamilo'];
        $apiKeyChamilo = $_POST['apikey_chamilo'];

        $userAdminChamilo = $_POST['username_chamilo'];
        $passAdminChamilo = $_POST['password_chamilo'];

        update_option('chamilo_connect_url', $urlChamilo);
        update_option('chamilo_connect_apikey', $apiKeyChamilo);
        update_option('chamilo_connect_username', $userAdminChamilo);
        update_option('chamilo_connect_password', $passAdminChamilo);
    }
    $rps = false;
    if ( isset( $_POST['test'] ) ) {
        $chamilo = new ChamiloConnect();
        $rps = $chamilo->connectStatus();

    }
    ?>

    <div class="wrap">
        <h1>Configuración Chamilo Connect</h1>
        <?php if($rps): ?>
            <div class="update-nag notice notice-info inline">
                Conección establecida correctamente con el Chamilo <strong><a target="_blank" href="<?php echo $urlChamilo; ?>"><?php echo $urlChamilo; ?></a></strong>
            </div>
        <?php endif; ?>
        <div id="pw_wrap">
            <form method="post" >
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="url_chamilo">Dirección de Chamilo (URL)</label>
                        </th>
                        <td>
                            <input type="text" id="url_chamilo" name="url_chamilo" class="regular-text"
                                   value="<?php echo $urlChamilo; ?>" required>
                            <p class="description">Escribe la url del aula virtual chamilo para conectar</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="apikey_chamilo">API KEY Chamilo:</label>
                        </th>
                        <td>
                            <input type="text" id="apikey_chamilo" name="apikey_chamilo" class="regular-text"
                                   value="<?php echo $apiKeyChamilo; ?>" required>
                            <p class="description">El Segurity Key lo puedes encontrar en el archivo de
                                configuration.php de tu aula virtual Chamilo</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="username_chamilo">Usuario Administrador de Chamilo</label>
                        </th>
                        <td>
                            <input type="text" id="username_chamilo" name="username_chamilo" class="regular-text"
                                   value="<?php echo $userAdminChamilo; ?>">
                            <p class="description">Te sugerimos colocar un usuario administrador diferente solo, para la conexión con Chamilo</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="password_chamilo">Contraseña Administrador de Chamilo</label>
                        </th>
                        <td>
                            <input type="password" id="password_chamilo" name="password_chamilo" class="regular-text"
                                   value="<?php echo $passAdminChamilo; ?>">
                            <p class="description">El usuario administrador de la conexión debe de tener una contraseña fuerte</p>
                        </td>
                    </tr>

                    </tbody>
                </table>
                <div class="submit">
                    <?php
                    submit_button('Guardar datos de conexión', 'primary', 'save', false);
                    submit_button('Probar la conexión', 'success', 'test', false);
                    ?>
                </div>
            </form>
        </div>
    </div>

    <?php


}
