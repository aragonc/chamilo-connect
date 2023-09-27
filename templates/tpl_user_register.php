<?php
/*
 * Template Name: Template User Register
 */

if (is_user_logged_in()) {
    header(home_url() . "/user-account");
    exit;
} else {
    $urlHome = home_url();
    $urlLogin = home_url() . '/login';
    $urlLostPassword = home_url() . '/lost-password';
    include(plugin_dir_path(__FILE__) . '../countries/countries.php');
    $countries = getCountries();
    $chamilo = new ChamiloConnect();
    $error_message = null;
    $userAdminChamilo = get_option('chamilo_connect_username');
    $passAdminChamilo = get_option('chamilo_connect_password');

    $imageRegister = get_option('chamilo_register_image_url', '');

    $hideInputsRegister = boolval(get_option('chamilo_register_inputs_description'));

    $titleRegister = get_option('chamilo_register_title', 'Registro de usuario');

    if (isset($_POST['register-submit'])) {
        $params = [
            'first_name' => $_POST['firstname'],
            'last_name' => $_POST['lastname'],
            'user_email' => $_POST['email'],
            'user_login' => $_POST['email'],
            'user_pass' => $_POST['password'],
            'display_name' => $_POST['firstname'] . ', ' . $_POST['lastname'],
            'country' => $_POST['country'],
            'identifier' => $_POST['identifier'],
            'rut' => $_POST['rut']
        ];

        // comprobar en Chamilo
        $userWP = wp_insert_user($params);
        $userChamilo = $chamilo->createUser($params);

        if (is_wp_error($userWP)) {
            $error_message = $userWP->get_error_message();
        } else {
            add_user_meta($userWP, 'country', $_POST['country']);
            add_user_meta($userWP, 'identifier', $_POST['identifier']);
            add_user_meta($userWP, 'rut', $_POST['rut']);

            wp_redirect("/login",);
            exit;
        }

    } else {
        //hide header
        $hideHeaderFooter = get_option('chamilo_connect_hide_header_footer');
        if ($hideHeaderFooter) {
            $chamilo->get_header_custom();
        } else {
            get_header();
        }
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

                            <?php if (!empty($imageRegister)):
                                $image_url = home_url() . '/wp-content/uploads/chamilo/' . $imageRegister;
                            ?>

                            <img src="<?php echo $image_url; ?>" alt="" class="img-fluid">

                            <?php else: ?>
                                <img src="<?php echo $chamilo->get_url_plugin_chamilo().'/images/register.svg'; ?>" alt="" class="img-fluid">
                            <?php endif; ?>

                        </div>
                        <div class="login-wrap">

                            <div class="padding-login">
                            <h2 class="title"><?php echo $titleRegister; ?></h2>
                            <?php if (!is_null($error_message)): ?>
                                <div id="msg-error-rut" class="alert alert-danger">
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            <div id="msg-error-rut" style="display: none;" class="alert alert-danger">
                                Debe de ingresar un RUT Válido
                            </div>
                            <form method="post" action="" id="register-user" class="register-user">
                                <div class="form-group row">
                                    <label for="firstname" class="col-sm-3 col-form-label">Nombres</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="firstname" id="firstname" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="lastname" class="col-sm-3 col-form-label">Apellidos</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="lastname" id="lastname" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="email" class="col-sm-3 col-form-label">Correo electrónico</label>
                                    <div class="col-sm-9">
                                        <input type="email" class="form-control" id="email" name="email"
                                               aria-describedby="emailHelp" required>
                                        <div id="email-status" class="alert alert-danger" role="alert" style="display: none;">
                                        </div>
                                        <?php if(!$hideInputsRegister): ?>
                                            <small id="emailHelp" class="form-text text-muted">
                                                Este será tu usuario de acceso para ingresar, solo se aceptan minúsculas
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password" class="col-sm-3 col-form-label">Contraseña</label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control" id="password" name="password"
                                               aria-describedby="passwordHelp" required>
                                        <div id="paswordtrength"></div>
                                        <?php if(!$hideInputsRegister): ?>
                                            <small id="passwordHelp" class="form-text text-muted">
                                                Establece la contraseña que utilizarás para acceder
                                                (mayúsculas,minúsculas,caracteres especiales, sin espacios)
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="country" class="col-sm-3 col-form-label">Pais</label>
                                    <div class="col-sm-9">
                                        <select name="country" class="form-control" id="country" required>
                                            <?php foreach ($countries as $country):
                                                $selected = '';
                                                if (strtoupper($country['alpha2']) == 'CL') {
                                                    $selected = 'selected';
                                                }
                                                ?>
                                                <option value="<?php echo strtoupper($country['alpha2']); ?>" <?php echo $selected; ?>><?php echo $country['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div id="input_dni" class="form-group row" style="display:none;">
                                    <label for="identifier" class="col-sm-3 col-form-label">Nº Documento o Cédula de Identidad</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="identifier" class="form-control" id="identifier">
                                        <?php if(!$hideInputsRegister): ?>
                                            <small id="identifier_help" class="form-text text-muted">
                                                Escribe tu DNI o Documento de identidad
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div id="input_rut" class="form-group row">
                                    <label for="rut" class="col-sm-3 col-form-label">RUT Identificador Nacional</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="rut" class="form-control" id="rut"
                                               placeholder="Ej: 11222333-K">
                                        <?php if(!$hideInputsRegister): ?>
                                            <small id="rut_hep" class="form-text text-muted">
                                                Ingresar RUN sin puntos, con guión y con dígito verificador. Ej: 11222333-K
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!--<div class="help">
                                    * Esta información la utilizaremos para tu certificado de aprobación.
                                </div>-->

                                <div class="form-group">
                                    <button type="submit" id="register-submit" name="register-submit" value="register-submit"
                                            class="btn btn-primary btn-block" disabled>Registrarme
                                    </button>
                                </div>
                            </form>
                            <div>
                                <div class="alert alert-info text-register">
                                    Si ya tienes una cuenta, <a href="<?php echo $urlLogin; ?>">inicia sesión aquí </a>
                                    ó <a href="<?php echo $urlLostPassword; ?>">¿Ha olvidado su contraseña?</a>
                                </div>
                            </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function ($) {
            let rut = $("#rut");
            let dni = $("#dni");
            let checkRut = true;

            $('#password').keyup(function (e) {
                let html = '';
                let strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
                let mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
                let enoughRegex = new RegExp("(?=.{6,}).*", "g");
                if (false == enoughRegex.test($(this).val())) {
                    html = '<div class="alert alert-primary" role="alert">Ingresa más de 6 caracteres</div>';
                    $('#paswordtrength').html(html);
                } else if (strongRegex.test($(this).val())) {
                    html = '<div class="alert alert-success" role="alert">Contraseña fuerte!</div>';
                    $('#paswordtrength').html(html);
                } else if (mediumRegex.test($(this).val())) {
                    html = '<div class="alert alert-warning" role="alert">Contraseña media!</div>';
                    $('#paswordtrength').html(html);
                } else {
                    html = '<div class="alert alert-danger" role="alert">Contraseña débil!</div>';
                    $('#paswordtrength').html(html);
                }
                return true;
            });

            $("#country").change(function () {
                let countrySelect;
                rut.attr('title', 'Ingresar RUN sin puntos, con guión y con dígito verificador. Ej: 11222333-K');
                rut.attr('maxlength', '10');
                $("#country option:selected").each(function () {
                    countrySelect = $(this).val();
                    //console.log(countrySelect);
                    if (countrySelect == 'CL') {
                        $("#input_dni").hide();
                        $("#input_rut").show();
                        rut.val('');
                    } else {
                        $("#input_dni").show();
                        $("#input_rut").hide();
                        dni.val('');
                    }
                });
            });

            $("#register-user").submit(function (e) {
                //console.log(RUT.val());
                let RutValue = rut.val();
                let countrySelect;
                //alert($("input[type=radio]:checked").val());
                $("#country option:selected").each(function () {
                    countrySelect = $(this).val();
                });
                console.log(countrySelect);
                if (checkRut) {
                    if (countrySelect === 'CL') {
                        if (!(RutValue.match('^[0-9]{7,9}[-|‐]{1}[0-9kK]{1}$'))) {
                            $("#msg-error-rut").show();
                            e.preventDefault();
                        }
                    }
                }
            });

            //Ajax email user exist.
            $(document).ready(function() {
                let urlAjax = "<?php echo $chamilo->get_url_plugin_chamilo().'/ajax/ajax.php'; ?>";
                // Referencia al campo de entrada de correo electrónico
                let emailInput = $("#email");

                // Referencia al elemento donde se mostrará el resultado de la verificación
                let emailStatus = $("#email-status");

                // Función para verificar el correo electrónico usando Ajax
                function checkEmailAvailability(email) {
                    $.ajax({
                        url: urlAjax,
                        method: "GET",
                        data: { email: email },
                        dataType: 'json',
                        success: function(response) {
                            //console.log(response);
                            let resultValue = response.result;
                            //console.log(resultValue);
                            if(!resultValue){
                                emailStatus.removeClass('alert alert-danger');
                                emailStatus.addClass('alert alert-success');
                                emailStatus.show();
                                emailStatus.text('El email se encuentra libre');
                                $('#register-submit').prop('disabled', false);
                            } else {
                                emailStatus.removeClass('alert alert-success');
                                emailStatus.addClass('alert alert-danger');
                                emailStatus.show();
                                emailStatus.text('El email ya se encuentra registrado');
                            }
                        }
                    });
                }

                // Función para verificar el formato de correo electrónico
                function isValidEmail(email) {
                    let pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
                    return pattern.test(email);
                }

                emailInput.on("input", function() {
                    let typedEmail = emailInput.val();
                    emailStatus.text(""); // Limpia el estado

                    if (isValidEmail(typedEmail)) {
                        checkEmailAvailability(typedEmail);
                        emailStatus.hide();
                    } else {
                        emailStatus.removeClass('alert alert-success');
                        emailStatus.addClass('alert alert-danger');
                        emailStatus.show();
                        emailStatus.text("Escribe correctamente un correo valido ejemplo: info@example.com");
                    }
                });
            });

        })(jQuery);
    </script>
    <?php
}
//hide footer
if ($hideHeaderFooter) {
    $chamilo->get_footer_custom();
} else {
    get_footer();
}
?>