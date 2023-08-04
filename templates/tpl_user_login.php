<?php
/*
 * Template Name: Template User Login
 */
$urlRegister = home_url() . '/user-register';
$urlLostPassword = home_url() . '/user-lostpassword';
$msgError = null;
$chamilo = new ChamiloConnect();

if (isset($_POST['login-submit'])) {
    $params = [
        'user_login' => $_POST['email'],
        'user_password' => $_POST['password'],
        'remember' => true
    ];
    //verify if you are admin in WordPress and not registered in Chamilo LMS
    if ($chamilo->is_user_admin_by_username($params['user_login'])) {
        $userWP = wp_signon($params);
    } else {
        $auth = $chamilo->authenticate($params['user_login'], $params['user_password']);
        $userWP = wp_signon($params);
        add_user_meta($userWP->data->ID, 'api_key_chamilo', $auth);
    }

    if (!is_wp_error($userWP)) {
        wp_redirect('/dashboard');
        exit;
    } else {
        $error_message = $userWP->get_error_message();
        $msgError = 'Error de inicio de sesión: ' . $error_message;
    }
}

$action = $_GET['action'] ?? null;
//$wpnonce = $_GET['_wpnonce'] ?? null;

if ($action == 'logout') {
    wp_logout();
    //wp_redirect('user-login');
    //exit;
}
if (is_user_logged_in()) {
    wp_redirect('dashboard');
    exit;
}

//hide header
$hideHeaderFooter = get_option('chamilo_connect_hide_header_footer');
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
                    <div class="wrap d-md-flex">
                        <div class="img-form pl-5 pr-5">
                            <img src="<?php echo $chamilo->get_url_plugin_chamilo().'/images/login.svg'; ?>" alt="" class="img-fluid">
                        </div>
                        <div class="login-wrap p-4 p-md-5">
                            <h2 class="title">Acceso al Aula Virtual</h2>
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
                                            <small id="emailHelp" class="form-text text-muted">
                                                Escribe el correo electrónico con el que te registraste en nuestra aula virtual,
                                                solo se aceptan minúsculas
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="password">Contraseña</label>
                                            <input type="password" class="form-control" id="password" name="password"
                                                   aria-describedby="passwordHelp" required>
                                            <small id="passwordHelp" class="form-text text-muted">
                                                Escribe tu contraseña correctamente.
                                            </small>
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