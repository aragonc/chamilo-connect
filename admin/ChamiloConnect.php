<?php

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

$webserviceURL= get_option('chamilo_connect_url').'/plugin/apichamilo/';
$webserviceUsername = get_option('chamilo_connect_username');
$webservicePassword = get_option('chamilo_connect_password');
$webserviceApiKey = get_option('chamilo_connect_apikey');

class ChamiloConnect
{

    public function __construct()
    {
        require_once plugin_dir_path(__FILE__) .'../vendor/autoload.php';
    }

    function get_url_plugin_chamilo(): string
    {
        $plugin_folder_name = 'chamilo-connect';
        return plugins_url($plugin_folder_name);
    }

    function get_path_plugin_chamilo(): string
    {
        return plugin_dir_path(__FILE__);
    }

    function get_header_custom() {
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        $header_content = preg_replace('/<head(.*)<\/head>/s', '', $header_content);
        echo $header_content;
    }

    function get_footer_custom() {
        ob_start();
        wp_footer();
        $footer_content = ob_get_clean();
        $footer_content = preg_replace('/<footer(.*)<\/footer>/s', '', $footer_content);
        echo $footer_content;
    }

    function get_custom_logo_url($size = 'medium', $print = true) {
        $blog_url = home_url();
        $custom_logo_id = get_theme_mod('custom_logo'); // Obtiene el ID de la imagen del logotipo personalizado
        if ($custom_logo_id) {
            $custom_logo_url = wp_get_attachment_image_src($custom_logo_id, $size);
            if ($custom_logo_url) {
                $logo_url = $custom_logo_url[0];
                if($print){
                    echo '<a href="'.$blog_url.'"><img src="' . esc_url($logo_url) . '" class="img-fluid" style="width:300px;" alt="'.get_bloginfo('name').'"></a>';
                } else {
                    return '<a href="'.$blog_url.'"><img src="' . esc_url($logo_url) . '" class="img-fluid" style="width:300px;" alt="'.get_bloginfo('name').'"></a>';
                }

            }
        } else {
            if($print){
                echo '<h1>' . get_bloginfo('name') . '</h1>';
            } else {
                return '<h1>' . get_bloginfo('name') . '</h1>';
            }

        }
    }

    function user_exists_by_email_wp($email): bool
    {
        $user = get_user_by('email', $email);
        // Check if the user exists
        if ($user) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws Exception
     */
    function generate_token(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * @throws Exception
     */
    function send_token_email($params) {
        $urlPath = self::get_path_plugin_chamilo();
        $html_file = $urlPath . '../templates/tpl_email_token.html';
        $email = $params['email'];
        if (file_exists($html_file)) {
            $html_content = file_get_contents($html_file);
            // Reemplaza marcadores en el HTML con los valores de $params
            foreach ($params as $key => $value) {
                $html_content = str_replace("{{" . $key . "}}", $value, $html_content);
            }
            $subject = 'Recuperación de contraseña';

            // Envía el correo electrónico con el contenido HTML
            $headers = array('Content-Type: text/html; charset=UTF-8');
            return wp_mail($email, $subject, $html_content, $headers);
        } else {
            return false; // El archivo HTML no existe
        }

    }

    /* FUNCTIONS API REST CHAMILO */

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function createUser($values)
    {
        global $webserviceURL;
        global $webserviceUsername;
        global $webserviceApiKey;
        $client = new GuzzleClient([
            'base_uri' => $webserviceURL,
        ]);

        $response = $client->post(
            'v2.php',
            [
                'form_params' => [
                    // data for the user who makes the request
                    'action' => 'save_user',
                    'username' => $webserviceUsername,
                    'api_key' => $webserviceApiKey,
                    // data for new user
                    'firstname' => $values['first_name'],
                    'lastname' => $values['last_name'],
                    'status' => 5, // student
                    'email' => $values['user_email'],
                    'loginname' => $values['user_login'],
                    'password' => $values['user_pass'],
                    'country' => $values['country'],
                    'original_user_id_name' => 'myplatform_user_id', // field to identify the user in the external system
                    'original_user_id_value' => '1234', // ID for the user in the external system
                    'extra' => [
                        [
                            'field_name' => 'identificador',
                            'field_value' => $values['identifier'],
                        ],
                        [
                            'field_name' => 'rut_factura',
                            'field_value' => $values['rut'],
                        ]
                    ],
                    'language' => 'spanish',
                    //'phone' => '',
                    //'expiration_date' => '',
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Entry denied with code : '.$response->getStatusCode());
        }

        $content = $response->getBody()->getContents();
        $jsonResponse = json_decode($content, true);

        if ($jsonResponse['error']) {
            throw new Exception('cant get user profile because : '.$jsonResponse['message']);
        }
        return $jsonResponse['data'][0];

    }


    /**
     * @throws GuzzleException
     */
    public function connectStatus(): bool
    {
        global $webserviceURL;
        global $webserviceUsername;
        global $webservicePassword;
        $client = new GuzzleClient([
            'base_uri' => $webserviceURL,
        ]);

        $response = $client->post('v2.php', [
            'form_params' => [
                'action' => 'authenticate',
                'username' => $webserviceUsername,
                'password' => $webservicePassword,
            ],
        ]);
        $rsp = true;
        if ($response->getStatusCode() !== 200) {
            $rsp = false;
        }

        return $rsp;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function getUserNameExist($apiKey, $loginName)
    {
        global $webserviceURL;
        global $webserviceUsername;
        $client = new GuzzleClient([
            'base_uri' => $webserviceURL,

        ]);


        $response = $client->post(
            'v2.php',
            [
                'form_params' => [
                    // data for the user who makes the request
                    'action' => 'username_exist',
                    'username' => $webserviceUsername,
                    'api_key' => $apiKey,
                    'loginname' => $loginName,
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Entry denied with code : '.$response->getStatusCode());
        }

        $content = $response->getBody()->getContents();
        $jsonResponse = json_decode($content, true);

        if ($jsonResponse['error']) {
            throw new Exception('cant get user profile because : '.$jsonResponse['message']);
        }
        return $jsonResponse['data'][0];
    }


    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function authenticate($username, $password): string
    {
        global $webserviceURL;

        if (empty($username) || empty($password)) {
            return false;
        }

        $client = new GuzzleClient([
            'base_uri' => $webserviceURL,
        ]);

        $response = $client->post('v2.php', [
            'form_params' => [
                'action' => 'authenticate',
                'username' => $username,
                'password' => $password,
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new Exception('Entry denied with code : ' . $response->getStatusCode());
        }

        $jsonResponse = json_decode($response->getBody()->getContents());

        if ($jsonResponse->error)
        {
            return false;
            //throw new Exception('Authentication failed because : ' . $jsonResponse->message);
        }

        return $jsonResponse->data->apiKey;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function getUserCourses($apiKey){
        global $webserviceURL;
        global $webserviceUsername;
        $client = new GuzzleClient([
            'base_uri' => $webserviceURL,
        ]);

        $response = $client->post(
            'v2.php',
            [
                'form_params' => [
                    // data for the user who makes the request
                    'action' => 'user_courses',
                    'username' => $webserviceUsername,
                    'api_key' => $apiKey,
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Entry denied with code : '.$response->getStatusCode());
        }

        $content = $response->getBody()->getContents();
        $jsonResponse = json_decode($content, true);

        if ($jsonResponse['error']) {
            throw new Exception('Can not make user_courses : '.$jsonResponse['message']);
        }
        return $jsonResponse['data'];
    }

    /**
     * @param $username
     * @param $apiKey
     *
     * @return array
     * @throws GuzzleException
     * @throws Exception
     */
    function getSessions($username,$apiKey): array
    {
        global $webserviceURL;

        $client = new GuzzleClient([
            'base_uri' => $webserviceURL,
        ]);

        $response = $client->post(
            'v2.php',
            [
                'form_params' => [
                    // data for the user who makes the request
                    'action' => 'user_sessions',
                    'username' => $username,
                    'api_key' => $apiKey,
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Entry denied with code : ' . $response->getStatusCode());
        }

        $jsonResponse = json_decode($response->getBody()->getContents());

        if ($jsonResponse->error) {
            throw new Exception('Courses not added because : ' . $jsonResponse->message);
        }
        return $jsonResponse->data;
    }

    /**
     * @param $apiKey
     *
     * @throws Exception|GuzzleException
     *
     */
    function getUserProfile($username, $apiKey)
    {
        global $webserviceURL;
        $client = new GuzzleClient([
            'base_uri' => $webserviceURL,
        ]);

        $response = $client->post(
            'v2.php',
            [
                'form_params' => [
                    // data for the user who makes the request
                    'action' => 'user_profile',
                    'username' => $username,
                    'api_key' => $apiKey,
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Entry denied with code : '.$response->getStatusCode());
        }

        $content = $response->getBody()->getContents();
        $jsonResponse = json_decode($content, true);

        if ($jsonResponse['error']) {
            throw new Exception('cant get user profile because : '.$jsonResponse['message']);
        }
        return $jsonResponse['data'];
    }


    /**
     * @param $email
     *
     * @throws Exception|GuzzleException
     *
     */
    function getUserEmailProfile($email)
    {
        global $webserviceURL;
        global $webserviceApiKey;
        global $webserviceUsername;

        $client = new GuzzleClient([
            'base_uri' => $webserviceURL,
        ]);

        $response = $client->post(
            'v2.php',
            [
                'form_params' => [
                    // data for the user who makes the request
                    'action' => 'get_user_email',
                    'email' => $email,
                    'api_key' => $webserviceApiKey,
                    'username' => $webserviceUsername,
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Entry denied with code : '.$response->getStatusCode());
        }

        $content = $response->getBody()->getContents();
        $jsonResponse = json_decode($content, true);

        if ($jsonResponse['error']) {
            throw new Exception('cant get user profile because : '.$jsonResponse['message']);
        }
        return $jsonResponse['data'];
    }


    function is_user_admin_by_username_wp($username): bool
    {
        global $wpdb;

        // Obtenemos el ID del usuario usando el nombre de usuario
        $user = get_user_by('login', $username);
        $user_id = $user ? $user->ID : 0;

        if ($user_id) {
            // Obtenemos los roles del usuario
            $user_roles = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s", $user_id, $wpdb->prefix . 'capabilities'));

            if ($user_roles) {
                $capabilities = maybe_unserialize($user_roles[0]->meta_value);
                // Comprobamos si el usuario tiene el rol de administrador
                if (isset($capabilities['administrator']) && $capabilities['administrator'] === true) {
                    return true;
                }
            }
        }

        return false;
    }

}