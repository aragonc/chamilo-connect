<?php
/*
 * Template Name: Template User Dashboard
 */
get_header();

//Obteniendo cursos y sesiones del usuario
$current_user = wp_get_current_user();
$userID = $current_user->ID;
$username = $current_user->user_login;

$row = [];
if($current_user->roles[0] != 'administrator'){
    $apiKeyChamilo = get_user_meta($userID,'api_key_chamilo', true);
    var_dump($apiKeyChamilo);
    $chamilo = new ChamiloConnect();
    $row = $chamilo->getSessions($username,$apiKeyChamilo);
}

?>

<div class="container">
    <section class="page-home page-register">
        <h1>Hola, <strong><?php echo $current_user->display_name; ?></strong>. ¡Te damos la bienvenida a Educación Chile!</h1>
        <p>El aprendizaje no tiene fin y cada nueva lección aprendida es un tesoro para potenciar tu desarrollo laboral y profesional.</p>
        <h2 class="title">Dashboard</h2>
    </section>

    <ul>
        <?php

        foreach ($row as $item){
            foreach ($item->sessions as $session){
                echo '<li>'.$session->name.'</li>';
            }

        }

        ?>

    </ul>

</div>

<?php
get_footer();
?>
