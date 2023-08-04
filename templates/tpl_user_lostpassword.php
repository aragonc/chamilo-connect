<?php
/*
 * Template Name: Template User Login
 */

$chamilo = new ChamiloConnect();
$urlLogin = home_url().'/user-login';
$urlRegister = home_url().'/user-register';

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
                        <div class="img-form">
                            <img src="<?php echo $chamilo->get_url_plugin_chamilo().'/images/lost_password.svg'; ?>" alt="" class="img-fluid">
                        </div>
                        <div class="login-wrap p-4 p-md-5">
                            <h2 class="title">¿Ha olvidado su contraseña?</h2>
                            <form method="post" action="" id="register-user" class="register-user">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-info text-register">
                                            Si ya tienes una cuenta, <a href="<?php echo $urlLogin; ?>">inicia sesión aquí </a> ó
                                            <a href="<?php echo $urlRegister; ?>"> Registrate </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="email">Correo electrónico (*)</label>
                                            <input type="email" class="form-control" id="email" aria-describedby="emailHelp">
                                            <small id="emailHelp" class="form-text text-muted">
                                                Escriba el nombre de usuario o la dirección de correo electrónico con la que está registrado y le remitiremos su contraseña.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="help">
                                    * Contenido obligatorio
                                </div>
                                <div class="form-group">
                                    <button type="submit" value="submit" class="btn btn-primary btn-block">Recuperar contraseña</button>
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