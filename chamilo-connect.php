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

require_once plugin_dir_path(__FILE__) . 'admin/ChamiloConnect.php';

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function chamilo_activation()
{
    global $wpdb;

    $table_chamilo_connect = $wpdb->prefix . 'chamilo_connect';
    $table_chamilo_recovery = $wpdb->prefix . 'chamilo_recovery_tokens';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_chamilo_connect (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        meta_key varchar(255) NOT NULL,
        meta_value longtext NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql .= "
        CREATE TABLE $table_chamilo_recovery (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_email VARCHAR(100) NOT NULL DEFAULT '',
            token VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (user_email) REFERENCES wp_users(user_email)
        ) $charset_collate; ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    chamilo_create_pages_user();
}

function chamilo_deactivation()
{
    global $wpdb;

    $table_chamilo_connect = $wpdb->prefix . 'chamilo_connect';
    $table_chamilo_recovery = $wpdb->prefix . 'chamilo_recovery_tokens';

    $sql = 'DROP TABLE ' . $table_chamilo_connect . ';';
    $wpdb->get_results($sql);
    $sql = 'DROP TABLE ' . $table_chamilo_recovery . ';';
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

    // Agrega un submenú al menú principal
    add_submenu_page(
        'chamilo_connect_configuration', // Identificador del menú principal
        'Opción del Submenú',
        'Configuración de paginas',
        'manage_options',
        'chamilo_submenu_pages', // Slug único para el submenú
        'chamilo_submenu_pages_callback' // Función que muestra el contenido del submenú
    );
}

function chamilo_submenu_pages_callback()
{
    $chamilo = new ChamiloConnect();
    $titleLogin = get_option('chamilo_login_title');
    $titleRegister = get_option('chamilo_register_title', 'Registro de usuario');
    $helpLogin = get_option('chamilo_login_help');
    $imageLogin = get_option('chamilo_login_image_url', '');
    $imageRegister = get_option('chamilo_register_image_url', '');
    $hideInputsLogin = get_option('chamilo_login_inputs_description');
    $hideInputsRegister = get_option('chamilo_register_inputs_description');

    $upload_error = null;

    if (isset($_POST['save-login'])) {
        $titleLogin = $_POST['title_login'];
        $helpLogin = $_POST['help_login'];
        $hideInputsLogin = isset($_POST['hide_inputs_description']) ? 1 : 0;

        update_option('chamilo_login_title', $titleLogin);
        update_option('chamilo_login_help', $helpLogin);
        update_option('chamilo_login_inputs_description', $hideInputsLogin);
        if (isset($_FILES['image_login'])) {
            $imageLoginCrop = $chamilo->saveImageCrop($_FILES['image_login']);
            update_option('chamilo_login_image_url', $imageLoginCrop['image_name']);
        }
    }

    if (isset($_POST['save-register'])) {
        $titleRegister = $_POST['title_register'];
        $hideInputsRegister = isset($_POST['hide_inputs_description_register']) ? 1 : 0;

        update_option('chamilo_register_title', $titleRegister);
        update_option('chamilo_register_inputs_description', $hideInputsRegister);

        if (isset($_FILES['image_register'])) {
            $imageRegisterCrop = $chamilo->saveImageCrop($_FILES['image_register']);
            update_option('chamilo_register_image_url', $imageRegisterCrop['image_name']);
        }
    }

    if (isset($_POST['delete_image_register'])) {
        $chamilo->deleteImage($imageRegister);
        delete_option('chamilo_register_image_url');
    }
    ?>
    <div class="wrap">
        <h1>Configuración páginas del usuario</h1>

        <?php if (!empty($upload_error)) : ?>
            <div class="error">
                <p><?php echo esc_html($upload_error); ?></p>
            </div>
        <?php endif; ?>
        <div class="nav-tab-wrapper">
            <a class="nav-tab nav-tab-active" href="#tab-login">Login</a>
            <a class="nav-tab" href="#tab-register">Register</a>
        </div>

        <div id="tab-login" class="tab-content">
            <form id="form-page-login" method="post" enctype="multipart/form-data">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="title_login">Titulo de la página de login</label>
                        </th>
                        <td>
                            <input type="text" id="title_login" name="title_login" class="regular-text"
                                   value="<?php echo $titleLogin; ?>" required>
                            <p class="description">Escribe el titulo que se mostrara en la página de login</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="help_login">Texto de ayuda</label>
                        </th>
                        <td>
                            <input type="text" id="help_login" name="help_login" class="regular-text"
                                   value="<?php echo $helpLogin; ?>" required>
                            <p class="description">Escribe un texto de ayuda descriptivo para la página de login</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="image_login">Imagen de la página de login</label>
                        </th>
                        <td>

                            <?php if (!empty($imageLogin)):
                                $imageUrlThumbnail = get_site_url() . '/wp-content/uploads/chamilo/thumbnail_' . $imageLogin;
                                ?>
                                <div class="thumbnail_login">
                                    <img src="<?php echo $imageUrlThumbnail; ?>">
                                </div>
                                <input type="submit" name="delete_image_login" class="button success"
                                       value="Eliminar Imagen">
                            <?php else: ?>
                                <input type="file" name="image_login" id="image_login">
                                <p class="description">La imagen debe de tener una dimesión de 650 px de ancho y 800 px
                                    de alto.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Ocultar descripción de inputs
                        </th>
                        <td>
                            <label for="hide_inputs_description">
                                <input type="checkbox" name="hide_inputs_description"
                                       id="hide_inputs_description" <?php checked($hideInputsLogin, true); ?>
                                       value="<?php echo $hideInputsLogin; ?>">
                                Permite ocultar la descripción de los inputs del formulario de login.
                            </label>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="submit">
                    <?php
                    submit_button('Guardar configuración', 'primary', 'save-login', false);
                    ?>
                </div>
            </form>
        </div>
        <div id="tab-register" class="tab-content" style="display: none;">
            <form id="form-page-register" method="post" enctype="multipart/form-data">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="title_register">Titulo de la página de registro</label>
                        </th>
                        <td>
                            <input type="text" id="title_register" name="title_register" class="regular-text"
                                   value="<?php echo $titleRegister; ?>" required>
                            <p class="description">Escribe el titulo que se mostrara en la página de login</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="image_register">Imagen de la página de registro</label>
                        </th>
                        <td>

                            <?php if (!empty($imageRegister)):
                                $imageUrlRegisterThumbnail = get_site_url() . '/wp-content/uploads/chamilo/thumbnail_' . $imageRegister;
                                ?>
                                <div class="thumbnail_login">
                                    <img src="<?php echo $imageUrlRegisterThumbnail; ?>">
                                </div>
                                <input type="submit" name="delete_image_register" class="button success"
                                       value="Eliminar Imagen">
                            <?php else: ?>
                                <input type="file" name="image_register" id="image_register">
                                <p class="description">La imagen debe de tener una dimesión de 650 px de ancho y 800 px
                                    de alto.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Ocultar descripción de inputs
                        </th>
                        <td>
                            <label for="hide_inputs_description_register">
                                <input type="checkbox" name="hide_inputs_description_register"
                                       id="hide_inputs_description_register" <?php checked($hideInputsRegister, true); ?>
                                       value="<?php echo $hideInputsRegister; ?>">
                                Permite ocultar la descripción de los inputs del formulario de registro.
                            </label>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="submit">
                    <?php
                    submit_button('Guardar configuración', 'primary', 'save-register', false);
                    ?>
                </div>
            </form>
        </div>

    </div>
    <script>
        jQuery(function ($) {
            // Manejar el cambio de pestaña al hacer clic en el enlace
            $('.nav-tab-wrapper a').click(function (e) {
                e.preventDefault();
                var tabId = $(this).attr('href');

                // Ocultar todas las pestañas de contenido
                $('.tab-content').hide();
                // Mostrar la pestaña de contenido correspondiente al enlace clicado
                $(tabId).show();

                // Agregar y quitar la clase "nav-tab-active" para resaltar la pestaña activa
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
            });
        });
    </script>
    <?php
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
    $hideHeaderFooter = get_option('chamilo_connect_hide_header_footer');

    if (isset($_POST['save'])) {
        $urlChamilo = $_POST['url_chamilo'];
        $apiKeyChamilo = $_POST['apikey_chamilo'];
        $userAdminChamilo = $_POST['username_chamilo'];
        $passAdminChamilo = $_POST['password_chamilo'];
        $hideHeaderFooter = isset($_POST['hide_header_footer']) ? 1 : 0;

        update_option('chamilo_connect_url', $urlChamilo);
        update_option('chamilo_connect_apikey', $apiKeyChamilo);
        update_option('chamilo_connect_username', $userAdminChamilo);
        update_option('chamilo_connect_password', $passAdminChamilo);
        update_option('chamilo_connect_hide_header_footer', $hideHeaderFooter);
    }
    $rps = false;
    if (isset($_POST['test'])) {
        $chamilo = new ChamiloConnect();
        $rps = $chamilo->connectStatus();
    }
    ?>

    <div class="wrap">
        <h1>Configuración Chamilo Connect</h1>
        <?php if ($rps): ?>
            <div class="update-nag notice notice-info inline">
                Conección establecida correctamente con el Chamilo <strong><a target="_blank"
                                                                              href="<?php echo $urlChamilo; ?>"><?php echo $urlChamilo; ?></a></strong>
            </div>
        <?php endif; ?>
        <div id="pw_wrap">
            <form method="post">
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
                            <p class="description">Te sugerimos colocar un usuario administrador diferente solo, para la
                                conexión con Chamilo</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="password_chamilo">Contraseña Administrador de Chamilo</label>
                        </th>
                        <td>
                            <input type="password" id="password_chamilo" name="password_chamilo" class="regular-text"
                                   value="<?php echo $passAdminChamilo; ?>">
                            <p class="description">El usuario administrador de la conexión debe de tener una contraseña
                                fuerte</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Ocultar el header y footer
                        </th>
                        <td>
                            <label for="hide_header_footer">
                                <input type="checkbox" name="hide_header_footer"
                                       id="hide_header_footer" <?php checked($hideHeaderFooter, true); ?>
                                       value="<?php echo $hideHeaderFooter; ?>">
                                Permite ocultar la cabecera y el pie de pagina de las paginas de inicio de sesión,
                                registro y contraseña olvidada.
                            </label>
                        </td>
                    </tr>

                    </tbody>
                </table>
                <div class="submit">
                    <?php
                    submit_button('Guardar configuración', 'primary', 'save', false);
                    submit_button('Probar la conexión', 'success', 'test', false);
                    ?>
                </div>
            </form>
        </div>
    </div>
    <script>
        jQuery(document).ready(function ($) {
            // Capturar el evento clic en el checkbox
            $('#hide_header_footer').on('click', function () {
                // Obtener el valor actual del checkbox
                var currentValue = $(this).val();

                // Cambiar el valor del checkbox
                if (currentValue === '1') {
                    $(this).val('0');
                } else {
                    $(this).val('1');
                }
            });
        });
    </script>
    <?php
}


//add css boostrap
// Incluir Bootstrap CSS
function resource_css()
{
    $urlPlugin = plugin_dir_url(__FILE__);
    wp_enqueue_style('bootstrap_css',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css',
        array(),
        '4.1.3',
        'all'
    );
    wp_enqueue_style('chamilo_css',
        $urlPlugin . 'css/style.css',
        array(),
        '1.1',
        'all'
    );
    wp_enqueue_style('strength_css',
        $urlPlugin . 'js/strength/strength.css',
        array(),
        '1.0'
    );
}

add_action('wp_enqueue_scripts', 'resource_css', 9999);

// Incluir Bootstrap JS y dependencia popper
function resource_js()
{
    $urlPlugin = plugin_dir_url(__FILE__);
    wp_enqueue_script('popper_js',
        'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js',
        array(),
        '1.14.3',
        true
    );
    wp_enqueue_script('bootstrap_js',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js',
        array('jquery', 'popper_js'),
        '4.1.3',
        true
    );
    wp_enqueue_script('strength_js',
        $urlPlugin . 'js/strength/strength.min.js',
        array(),
        '1.0',
        true
    );
}

add_action('wp_enqueue_scripts', 'resource_js');

function chamilo_create_pages_user()
{
    $template_dir = plugin_dir_path(__FILE__) . 'templates/';
    $pages = apply_filters(
        'create_default_pages',
        array(
            'useraccount' => array(
                'name' => esc_html_x('account', 'Page slug', 'chamilo-connect'),
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
                'name' => esc_html_x('login', 'Page slug', 'chamilo-connect'),
                'title' => esc_html_x('Login', 'Page title', 'chamilo-connect'),
                'content' => '[user_login]',
                'option_key' => 'chamilo_login_page_id',
                'template' => 'tpl_user_login.php'
            ),
            'register' => array(
                'name' => esc_html_x('register', 'Page slug', 'chamilo-connect'),
                'title' => esc_html_x('Register', 'Page title', 'chamilo-connect'),
                'content' => '[user_register]',
                'option_key' => 'chamilo_register_page_id',
                'template' => 'tpl_user_register.php'
            ),
            'lostpassword' => array(
                'name' => esc_html_x('lost-password', 'Page slug', 'chamilo-connect'),
                'title' => esc_html_x('Lost Password', 'Page title', 'chamilo-connect'),
                'content' => '[user_lostpassword]',
                'option_key' => 'chamilo_lostpassword_page_id',
                'template' => 'tpl_user_lostpassword.php'
            ),
            'recoverpassword' => array(
                'name' => esc_html_x('recover-password', 'Page slug', 'chamilo-connect'),
                'title' => esc_html_x('Recover Password', 'Page title', 'chamilo-connect'),
                'content' => '[user_recover_password]',
                'option_key' => 'chamilo_recoverpassword_page_id',
                'template' => 'tpl_recover_password.php'
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
        'tpl_user_dashboard.php' => __('User Dashboard'),
        'tpl_recover_password.php' => __('Recover Password')
    ];
}

add_filter('theme_page_templates', 'chamilo_add_template_to_select', 10, 4);

/**
 * @throws Exception
 */
function chamilo_load_plugin_template($template)
{
    $page_custom = get_page_template_slug();
    switch ($page_custom) {
        case 'tpl_user_login.php':
            if ($theme_file = locate_template(array('tpl_user_login.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__) . 'templates/tpl_user_login.php';
            }
            break;
        case 'tpl_user_register.php':
            if ($theme_file = locate_template(array('tpl_user_register.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__) . 'templates/tpl_user_register.php';
            }
            break;
        case 'tpl_user_dashboard.php':
            if ($theme_file = locate_template(array('tpl_user_dashboard.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__) . 'templates/tpl_user_dashboard.php';
            }
            break;
        case 'tpl_user_account.php':
            if ($theme_file = locate_template(array('tpl_user_account.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__) . 'templates/tpl_user_account.php';
            }
            break;
        case 'tpl_user_my_certificates.php':
            if ($theme_file = locate_template(array('tpl_user_my_certificates.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__) . 'templates/tpl_user_my_certificates.php';
            }
            break;
        case 'tpl_user_my_courses.php':
            if ($theme_file = locate_template(array('tpl_user_my_courses.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__) . 'templates/tpl_user_my_courses.php';
            }
            break;
        case 'tpl_user_lostpassword.php':
            if ($theme_file = locate_template(array('tpl_user_lostpassword.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__) . 'templates/tpl_user_lostpassword.php';
            }
            break;
        case 'tpl_recover_password.php':
            if ($theme_file = locate_template(array('tpl_recover_password.php'))) {
                $template = $theme_file;
            } else {
                $template = plugin_dir_path(__FILE__) . 'templates/tpl_recover_password.php';
            }
            break;
        default:
    }

    return $template;
}

add_filter('template_include', 'chamilo_load_plugin_template');

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
        'account',
        'my-courses',
        'my-certificates',
        'login',
        'register',
        'lost-password',
        'dashboard',
        'recover-password'
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

function remove_admin_bar_for_subscribers()
{
    if (current_user_can('subscriber') && !is_admin()) {
        show_admin_bar(false);
    }
}

add_action('after_setup_theme', 'remove_admin_bar_for_subscribers');

function get_user_login_bar()
{
    $chamilo = new ChamiloConnect();
    $urlSite = get_bloginfo('url');
    $plugin_url = plugin_dir_url(__FILE__);
    $logout_url = wp_logout_url(home_url());

    $current_user = wp_get_current_user();
    $userID = $current_user->ID;

    if (!empty($userID)) {
        if ($current_user->roles[0] != 'administrator') {
            if ($userID != 0) {
                $username = $current_user->user_login;
                $apiKeyChamilo = get_user_meta($userID, 'api_key_chamilo', true);
                $profile = $chamilo->getUserProfile($username, $apiKeyChamilo);
            }
        }
    }

    $avatar = $plugin_url . 'images/profile.svg';
    if (!empty($profile)) {
        $avatar = $profile['pictureUri'];
    }

    ?>

    <?php if (is_user_logged_in()): ?>

    <ul class="navbar ml-auto user-login">
        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <!-- Counter - Alerts -->
                <span class="badge badge-danger badge-counter">3+</span>
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                 aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Alerts Center
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3">
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">December 12, 2019</div>
                        <span class="font-weight-bold">A new monthly report is ready to download!</span>
                    </div>
                </a>

                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
            </div>
        </li>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow mr-auto">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false">

                <img class="img-profile rounded-circle" src="<?php echo $avatar; ?>">

            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Mi perfil
                </a>

                <a class="dropdown-item" href="#">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Mis cursos
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?php echo $logout_url; ?>">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Cerrar sesión
                </a>
            </div>
        </li>

    </ul>

<?php else: ?>
    <div class="header-bar-login">
        <ul class="btn-list no-list">
            <li class="btn-list-item">
                <a href="<?php echo $urlSite; ?>/login" class="btn-cta btn-login">
                    Ingresar
                </a>
            </li>
            <li class="btn-list-item">
                <a href="<?php echo $urlSite; ?>/register" class="btn-cta btn-register">
                    Registrarse
                </a>
            </li>
        </ul>
    </div>
<?php endif; ?>


    <?php

}

add_shortcode('login_bar', 'get_user_login_bar');
