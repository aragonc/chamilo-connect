<?php
/*
 * Template Name: Template User Login
 */

get_header();

?>

    <div class="container">
        <section class="page-home page-register">

            <h2>Registro de usuario</h2>

            <form method="post" action="" id="register-user">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="firstname">Nombres</label>
                            <input type="text" class="form-control" id="firstname">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="lastname">Apellidos</label>
                            <input type="text" class="form-control" id="lastname">
                        </div>
                    </div>
                </div>

                <form>


                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" aria-describedby="emailHelp">
                        <small id="emailHelp" class="form-text text-muted">
                            Este será tu usuario de acceso para ingresar a nuestra aula virtual, solo se aceptan minúsculas
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="password1">Contraseña</label>
                        <input type="password" class="form-control" id="password1" aria-describedby="passwordHelp">
                        <small id="passwordHelp" class="form-text text-muted">
                            Establece la contraseña que utilizarás para acceder a nuestra aula virtual, usar entre mayúsculas,minúsculas,caracteres especiales, sin espacios
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="password2">Confirme contraseña</label>
                        <input type="password" class="form-control" id="password2">
                    </div>

                    <div class="form-group">
                        <label for="country">Pais</label>
                        <select name="country" class="form-control" id="country">
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                            <option>4</option>
                            <option>5</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="identifier">Nº Documento o Cédula de Identidad</label>
                        <input type="text" class="form-control" id="identifier">
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>

            </form>

        </section>
    </div>

<?php get_footer(); ?>