<?php

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

$webserviceURL= get_option('chamilo_connect_url').'/main/webservices/api/';
$webserviceUsername = get_option('chamilo_connect_username');
$webservicePassword = get_option('chamilo_connect_password');

class ChamiloConnect
{

    public function __construct()
    {
        require_once plugin_dir_path(__FILE__) .'../vendor/autoload.php';
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function createUser($values, $apikey){
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
                    'action' => 'save_user',
                    'username' => $webserviceUsername,
                    'api_key' => $apikey,
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
                            'identificador' => $values['identifier'],
                            'rut_factura' => $values['rut'],
                        ],
                    ],
                    'language' => 'spanish',
                    //'phone' => '',
                    //'expiration_date' => '',
                ],
            ]
        );
        if ($response->getStatusCode() !== 200) {
            throw new Exception('Entry denied with code : ' . $response->getStatusCode());
        }

        $jsonResponse = json_decode($response->getBody()->getContents());

        if ($jsonResponse->error) {
            throw new Exception('User not created because : ' . $jsonResponse->message);
        }

        return $jsonResponse->data[0];

    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function getUserExists($username, $apiKeyChamilo){
        global $webserviceURL;
        global $webserviceUsername;
        if(empty($username)){
            return false;
        }

        $client = new GuzzleClient([
            'base_uri' => $webserviceURL,
        ]);

        $response = $client->post('v2.php', [
            'form_params' => [
                'action' => 'username_exist',
                'username' => $webserviceUsername,
                'api_key' => $apiKeyChamilo,
                'loginname' => $username
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Entry denied with code : ' . $response->getStatusCode());
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
    public function authenticate() {
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
        if ($response->getStatusCode() !== 200) {
            throw new Exception('Entry denied with code : ' . $response->getStatusCode());
        }

        $jsonResponse = json_decode($response->getBody()->getContents());

        if ($jsonResponse->error)
        {
            throw new Exception('Authentication failed because : ' . $jsonResponse->message);
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

}