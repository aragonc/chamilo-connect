<?php

use GuzzleHttp\Client as GuzzleClient;
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function connectStatus(){
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