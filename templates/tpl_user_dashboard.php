<?php
/*
 * Template Name: Template User Dashboard
 */
get_header();

//Obteniendo cursos y sesiones del usuario
$current_user = wp_get_current_user();
$userID = $current_user->ID;
$username = $current_user->user_login;

$apiKeyChamilo = get_user_meta($userID,'api_key_chamilo', true);

$chamilo = new ChamiloConnect();

$row = $chamilo->getSessions($username,$apiKeyChamilo);

?>

<div class="container">
    <section class="page-home page-register">
        <h2 class="title">Dashboard</h2>
    </section>

    <ul>
        <?php

        foreach ($row as $item){
            foreach ($item->sessions as $session){
                var_dump($session);
                echo '<li>'.$session->name.'</li>';
            }

        }

        ?>

    </ul>

</div>

<?php
get_footer();
?>
