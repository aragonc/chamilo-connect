<?php
/*
 * Template Name: Template User Login
 */
$urlRegister = home_url().'/user-register';
$urlLostPassword = home_url().'/user-lostpassword';
$msgError = null;

if (isset($_POST['login-submit'])) {
    $params =  [
        'user_login' => $_POST['email'],
        'user_password' => $_POST['password'],
        'remember' => true
    ];

    $chamilo = new ChamiloConnect();
    $auth = $chamilo->authenticate($params['user_login'],$params['user_password']);
    $userWP = wp_signon($params);
    add_user_meta($userWP->data->ID, 'api_key_chamilo', $auth);

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

if($action == 'logout'){
    wp_logout();
    //wp_redirect('user-login');
    //exit;
}
if(is_user_logged_in()){
    wp_redirect('dashboard');
    exit;
}
get_header();


?>

    <div class="container">
        <section class="page-home page-register">

            <h2 class="title">Acceso al Aula Virtual</h2>
            <div class="row">
                <div class="col-md-5">

                </div>
                <div class="col-md-7">
                    <?php if($msgError): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $msgError; ?>
                    </div>
                    <?php endif; ?>
                    <form method="post" action="" id="login-user" class="login-user">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="email">Correo electrónico (*)</label>
                                    <input type="text" class="form-control" id="email" name="email" aria-describedby="emailHelp" required>
                                    <small id="emailHelp" class="form-text text-muted">
                                        Escribe el correo electrónico con el que te registraste en nuestra aula virtual, solo se aceptan minúsculas
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="password">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" aria-describedby="passwordHelp" required>
                                    <small id="passwordHelp" class="form-text text-muted">
                                        Escribe tu contraseña correctamente.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" value="submit" id="login-submit" name="login-submit" class="btn btn-primary btn-block">Iniciar sesión</button>
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
        </section>
    </div>

<?php get_footer(); ?>