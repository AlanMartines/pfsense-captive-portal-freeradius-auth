<?php
// Globais para compatibilidade com o interpretador PHP do pfSense
global $brand, $today, $userName, $password, $db, $macAddress, $ipAddress;

// No pfSense, o arquivo enviado como config.php deve ser incluído com o prefixo
include "captiveportal-config.php";

// Obter IP e MAC Address (FreeBSD/pfSense)
$ipAddress = $_SERVER['REMOTE_ADDR'];
$arp = `arp $ipAddress`;
$lines = explode(" ", $arp);
$macAddress = (!empty($lines[3])) ? $lines[3] : "00:00:00:00:00:00";

function cleanInput($input)
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Lógica de Autenticação e Registro
 */
if (isset($_POST["name"]) && isset($_POST["whatsapp"]) && isset($_POST["terms"])) {

    $name = cleanInput($_POST["name"]);
    $phone = cleanInput($_POST["whatsapp"]);
    $regDate = date("Y-m-d H:i:s");

    $db = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    if ($db->connect_error) {
        WelcomePage("Erro de conexão com o banco de dados.");
        exit;
    }

    // 1. Salvar Lead (reg_users)
    $stmt = $db->prepare("INSERT INTO reg_users (familyName, emailAddress, macAddress, ipAddress, regDate) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $phone, $macAddress, $ipAddress, $regDate);
    $stmt->execute();
    $stmt->close();

    // 2. Credenciais RADIUS
    $userName = preg_replace('/[^0-9]/', '', $phone);
    $password = "wifi123";

    // 3. Atualizar/Inserir FreeRADIUS (radcheck)
    $stmt = $db->prepare("SELECT username FROM radcheck WHERE username = ?");
    $stmt->bind_param("s", $userName);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $up = $db->prepare("UPDATE radcheck SET value = ? WHERE username = ?");
        $up->bind_param("ss", $password, $userName);
        $up->execute();
        $up->close();
    } else {
        $ins = $db->prepare("INSERT INTO radcheck (username, attribute, op, value) VALUES (?, 'Cleartext-Password', ':=', ?)");
        $ins->bind_param("ss", $userName, $password);
        $ins->execute();
        $ins->close();
    }
    $stmt->close();

    // 4. Grupo RADIUS
    $db->query("INSERT IGNORE INTO radusergroup (username, groupname) VALUES ('$userName', 'Free')");
    $db->close();

    LoginHandshake();
} else {
    WelcomePage();
}

/**
 * Tela de Transição / Handshake pfSense
 */
function LoginHandshake()
{
    global $userName, $password;
?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <link rel="icon" href="captiveportal-favicon.ico" />
        <link rel="stylesheet" href="captiveportal-bootstrap.min.css">
        <style>
            body,
            html {
                height: 100%;
                margin: 0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .bg-image {
                /* Ajustado para o prefixo da imagem de fundo */
                background-image: url("captiveportal-background.jpg");
                height: 100%;
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
                position: fixed;
                width: 100%;
                z-index: -1;
            }

            .loader {
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f8f9fa;
                flex-direction: column;
            }
        </style>
    </head>

    <body>
        <div class="loader">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
            <h4 class="mt-4">Autenticando...</h4>
        </div>
        <form name="loginForm" method="post" action="$PORTAL_ACTION$">
            <input name="auth_user" type="hidden" value="<?php echo $userName; ?>">
            <input name="auth_pass" type="hidden" value="<?php echo $password; ?>">
            <input name="zone" type="hidden" value="$PORTAL_ZONE$">
            <input name="redirurl" type="hidden" value="$PORTAL_REDIRURL$">
            <input id="submitbtn" name="accept" type="submit" style="display:none;">
        </form>
        <script>
            window.onload = function() {
                document.getElementById("submitbtn").click();
            };
        </script>
    </body>

    </html>
<?php
    exit;
}

/**
 * Página Principal (Welcome)
 */
function WelcomePage($message = '')
{
    global $brand;
?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" href="captiveportal-favicon.ico" />
        <link rel="stylesheet" href="captiveportal-bootstrap.min.css">
        <style>
            body,
            html {
                height: 100%;
                margin: 0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .bg-image {
                /* Ajustado para o prefixo da imagem de fundo */
                background-image: url("captiveportal-background.jpg");
                height: 100%;
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
                position: fixed;
                width: 100%;
                z-index: -1;
            }

            .auth-container {
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .card-auth {
                border: none;
                border-radius: 1rem;
                background: rgba(255, 255, 255, 0.9);
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                overflow: hidden;
                width: 100%;
                max-width: 400px;
            }

            .card-header-custom {
                background: #007bff;
                color: white;
                text-align: center;
                padding: 1.5rem;
            }

            .invalid-feedback {
                display: block;
                font-size: 80%;
                color: #dc3545;
            }
        </style>
    </head>

    <body>
        <div class="bg-image"></div>

        <div class="container auth-container">
            <div class="card card-auth">
                <div class="card-header-custom">
                    <h4 class="mb-0">Acesso Wi-Fi</h4>
                    <small>Conecte-se agora</small>
                </div>
                <div class="card-body p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-danger py-2 small"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <form id="registrationForm" method="post">
                        <div class="form-group">
                            <label for="name">Nome Completo</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="Ex: João Silva" required>
                        </div>
                        <div class="form-group">
                            <label for="whatsapp">WhatsApp</label>
                            <input type="tel" class="form-control" name="whatsapp" id="whatsapp" placeholder="(67) 99999-9999" required>
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="terms" name="terms" required>
                            <label class="custom-control-label" for="terms">
                                Aceito os <a href="#" data-toggle="modal" data-target="#termsModal">termos de uso</a>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg shadow-sm">Liberar Acesso</button>
                    </form>
                </div>
            </div>
        </div>

        <?php include_once("captiveportal-termsofuse.php"); ?>

        <script src="captiveportal-jquery-3.5.1.min.js"></script>
        <script src="captiveportal-jquery.validate.min.js"></script>
        <script src="captiveportal-jquery.mask.min.js"></script>
        <script src="captiveportal-bootstrap.bundle.min.js"></script>

        <script>
            $(document).ready(function() {
                var behavior = function(val) {
                        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
                    },
                    options = {
                        onKeyPress: function(val, e, field, options) {
                            field.mask(behavior.apply({}, arguments), options);
                        }
                    };
                $('#whatsapp').mask(behavior, options);

                $("#registrationForm").validate({
                    rules: {
                        name: {
                            required: true,
                            minlength: 3
                        },
                        whatsapp: {
                            required: true,
                            minlength: 14
                        },
                        terms: {
                            required: true
                        }
                    },
                    messages: {
                        name: {
                            required: "O nome é obrigatório.",
                            minlength: "Mínimo de 3 letras."
                        },
                        whatsapp: {
                            required: "WhatsApp obrigatório.",
                            minlength: "Número incompleto."
                        },
                        terms: {
                            required: "Aceite os termos para prosseguir."
                        }
                    },
                    errorElement: 'span',
                    errorPlacement: function(error, element) {
                        error.addClass('invalid-feedback');
                        if (element.hasClass("custom-control-input")) {
                            error.insertAfter(element.closest('.custom-control'));
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    highlight: function(element) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('is-invalid');
                    }
                });
            });
        </script>
    </body>

    </html>
<?php
}
?>