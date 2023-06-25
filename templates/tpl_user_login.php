<?php
/*
 * Template Name: Template User Login
 */

get_header();
$urlRegister = home_url().'/user-register';
$urlLostPassword = home_url().'/user-lostpassword';
?>

    <div class="container">
        <section class="page-home page-register">

            <h2 class="title">Acceso al Aula Virtual</h2>
            <div class="row">
                <div class="col-md-5">

                </div>
                <div class="col-md-7">
                    <form method="post" action="" id="login-user" class="login-user">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="email">Correo electrónico (*)</label>
                                    <input type="email" class="form-control" id="email" aria-describedby="emailHelp">
                                    <small id="emailHelp" class="form-text text-muted">
                                        Escribe el correo electrónico con el que te registraste en nuestra aula virtual, solo se aceptan minúsculas
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="password1">Contraseña</label>
                                    <input type="password" class="form-control" id="password1" aria-describedby="passwordHelp">
                                    <small id="passwordHelp" class="form-text text-muted">
                                        Escribe tu contraseña correctamente.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" value="submit" class="btn btn-primary btn-block">Iniciar sesión</button>
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