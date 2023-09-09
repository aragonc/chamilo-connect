<?php

require('../../../../wp-load.php');
global $wpdb;
$email = $_GET['email'] ?? '';
$chamilo = new ChamiloConnect();
$userAdminChamilo = get_option('chamilo_connect_username');
$passAdminChamilo = get_option('chamilo_connect_password');

$auth = $chamilo->authenticate($userAdminChamilo,$passAdminChamilo);

if ($email !== '') {
    $wp_query = "SELECT user_email FROM wp_users WHERE user_email = '$email'";
    $wp_result = $wpdb->query($wp_query);
    $ch_result = $chamilo->getUserNameExist($auth,$email);

    $compare = (($wp_result > 0) and $ch_result);

    $response = array(
        'wordpress' => $wp_result > 0,
        'chamilo' => $ch_result,
        'result' => $compare
    );

    echo json_encode($response);
} else {
    echo json_encode(array('error' => 'Correo electrónico no proporcionado'));
}