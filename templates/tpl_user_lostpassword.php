<?php
/*
 * Template Name: Template User Login
 */

get_header();
$urlLogin = home_url().'/user-login';
$urlRegister = home_url().'/user-register';

?>
    <div class="container">
        <section class="page-home page-register">

            <h2 class="title">¿Ha olvidado su contraseña?</h2>
            <div class="row">
                <div class="col-md-5">

                </div>
                <div class="col-md-7">
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
        </section>
    </div>

<?php get_footer(); ?>