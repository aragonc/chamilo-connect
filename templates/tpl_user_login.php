<?php
/*
 * Template Name: Template User Login
 */
$urlRegister = home_url() . '/register';
$urlLostPassword = home_url() . '/lost-password';
$msgError = null;
$chamilo = new ChamiloConnect();
$userExistsWP = false;
$userWP = [];

if (isset($_POST['login-submit'])) {
    $params = [
        'user_login' => $_POST['email'],
        'user_password' => $_POST['password'],
        'remember' => true
    ];

    //verify if you are admin in WordPress and not registered in Chamilo LMS
    if ($chamilo->is_user_admin_by_username_wp($params['user_login'])) {
        $userWP = wp_signon($params);
        if (is_wp_error($userWP)) {
            $error_message = $userWP->get_error_message();
            $msgError = $error_message;

        } else {
            wp_redirect('/dashboard');
            exit;
        }
        //exit;
    } else {

        // Verificamos si el usuario existe en Chamilo LMS a travès de su key
        $auth = $chamilo->authenticate($params['user_login'], $params['user_password']);

        if (!empty($auth)) {

            // Verificamos si ahora ese usuario existe dentro de Wordpress
            $userExistsWP = $chamilo->user_exists_by_email_wp($params['user_login']);

            if ($userExistsWP) {
                $userWP = wp_signon($params);
                update_user_meta($userWP->data->ID, 'api_key_chamilo', $auth);
            } else {
                $profile = $chamilo->getUserProfile($params['user_login'], $auth);
                $userParams = [
                    'first_name' => $profile['first_name'],
                    'last_name' => $profile['last_name'],
                    'user_email' => $profile['email'],
                    'user_login' => $profile['username'],
                    'user_pass' => $params['user_password'],
                    'display_name' => $profile['full_name'],
                    'country' => $profile['country'],
                ];
                $userID = wp_insert_user($userParams);
                add_user_meta($userID, 'api_key_chamilo', $auth);
                $userWP = wp_signon($params);
            }
            if (is_wp_error($userWP)) {
                $error_message = $userWP->get_error_message();
                $msgError = 'Error de inicio de sesión: ' . $error_message;
            }
            wp_redirect('/dashboard');
            exit;
        } else {
            $msgError = 'Usuario o contraseña incorrectos. Por favor, intenta nuevamente.';
        }
    }
}

$action = $_GET['action'] ?? null;
//$wpnonce = $_GET['_wpnonce'] ?? null;

if ($action == 'logout') {
    wp_logout();
}
if (is_user_logged_in()) {
    wp_redirect('dashboard');
    exit;
}

//hide header
$hideHeaderFooter = get_option('chamilo_connect_hide_header_footer');
$titleLogin = get_option('chamilo_login_title');
$helpLogin = get_option('chamilo_login_help');
$imageLogin = get_option('chamilo_login_image_url', '');
$hideInputsDescription = boolval(get_option('chamilo_login_inputs_description'));

if ($hideHeaderFooter) {
    $chamilo->get_header_custom();
} else {
    get_header();
}

?>

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-5">
                    <?php $chamilo->get_custom_logo_url(); ?>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-12 col-lg-10">
                    <div class="wrap d-md-flex bg-color">
                        <div class="img-form">
                            <?php if (!empty($imageLogin)):
                                $imageUrlLogin = home_url() . '/wp-content/uploads/chamilo/' . $imageLogin;
                                ?>
                                <img src="<?php echo $imageUrlLogin; ?>" alt=""
                                     class="img-fluid">
                            <?php else: ?>
                                <img src="<?php echo $chamilo->get_url_plugin_chamilo() . '/images/login.svg'; ?>"
                                     alt=""
                                     class="img-fluid">
                            <?php endif; ?>
                        </div>
                        <div class="login-wrap">
                            <div class="padding-login">


                                <h2 class="title"><?php echo $titleLogin; ?></h2>
                                <?php if ($msgError): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?php echo $msgError; ?>
                                    </div>
                                <?php endif; ?>
                                <form method="post" action="" id="login-user" class="login-user">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="email">Correo electrónico (*)</label>
                                                <input type="text" class="form-control" id="email" name="email"
                                                       aria-describedby="emailHelp" required>
                                                <?php if (!$hideInputsDescription): ?>
                                                    <small id="emailHelp" class="form-text text-muted">
                                                        Escribe el correo electrónico con el que te registraste en
                                                        nuestra aula virtual, solo se aceptan minúsculas.
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="password">Contraseña</label>
                                                <input type="password" class="form-control" id="password"
                                                       name="password"
                                                       aria-describedby="passwordHelp" required>
                                                <?php if (!$hideInputsDescription): ?>
                                                    <small id="passwordHelp" class="form-text text-muted">
                                                        Escribe tu contraseña correctamente.
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" value="submit" id="login-submit" name="login-submit"
                                                class="btn btn-primary btn-block">Iniciar sesión
                                        </button>
                                        <a href="<?php echo $urlRegister; ?>" class="btn btn-default btn-block">
                                            Registro
                                        </a>
                                    </div>

                                    <div class="lost-password">
                                        <a href="<?php echo $urlLostPassword; ?>">
                                            ¿Ha olvidado su contraseña?
                                        </a>
                                    </div>

                                </form>
                                <div class="help"><?php echo $helpLogin; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php

//hide footer
if ($hideHeaderFooter) {
    $chamilo->get_footer_custom();
} else {
    get_footer();
}

?>