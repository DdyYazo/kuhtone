<?php
session_start(); // Iniciar la sesión

// asistencia_paciente.php
include("./config/conexion.php");
include("./includes/asistencia_pacienteModel.php");

$model = new AsistenciaPacienteModel($conection);

$id_paciente = $_GET['id_paciente']; // Asegúrate de validar y sanear este dato en la práctica
$id_profesional = $_SESSION['id_profesional']; // Suponiendo que tienes un ID de profesional almacenado en la sesión
$id_sesion = isset($_GET['id_sesion']) ? $_GET['id_sesion'] : null;

// Obtener la información del paciente
list($paciente_nombres, $paciente_apellidos) = $model->getPacienteInfo($id_paciente);

// Actualizar la asistencia
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asistio = isset($_POST['asistio']) ? intval($_POST['asistio']) : null;
    $reporte = isset($_POST['reporte']) ? $_POST['reporte'] : null;
    $id_sesion = isset($_POST['id_sesion']) ? intval($_POST['id_sesion']) : null;

    if ($asistio !== null && $reporte !== null && $id_sesion !== null) {
        // Verificar si ya existe una sesión con el paciente y el profesional
        $sesion_existente = $model->obtenerSesion($id_paciente, $id_profesional);

        if ($sesion_existente) {
            // Actualizar la asistencia y el reporte de la sesión existente
            if ($model->updateAsistencia($asistio, $reporte, $id_paciente, $sesion_existente['id_sesion'])) {
                echo "Asistencia y reporte guardados correctamente";
            } else {
                $error_message = $model->getErrorMessage();
            }
        } else {
            // Crear una nueva sesión y luego actualizar la asistencia y el reporte
            $fecha_sesion = date('Y-m-d'); // Establecer la fecha de la sesión como la fecha actual
            $id_sesion = $model->insertarSesion($id_paciente, $id_profesional, $fecha_sesion);

            if ($id_sesion !== false) {
                if ($model->updateAsistencia($asistio, $reporte, $id_paciente, $id_sesion)) {
                    echo "Asistencia y reporte guardados correctamente";
                } else {
                    $error_message = $model->getErrorMessage();
                }
            } else {
                $error_message = $model->getErrorMessage();
            }
        }
    } else {
        $error_message = "Por favor, completa todos los campos del formulario.";
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Metadatos y enlaces a archivos externos -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kuhtone</title>
    <script src="https://kit.fontawesome.com/79e6024c63.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Inter:wght@300;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/asistencia_paciente.css">
    <link rel="stylesheet" href="./css/tablet.css" media="screen and (min-width: 600px)" />
    <link rel="stylesheet" href="./css/desktop.css" media="screen and (min-width: 800px)" />
</head>

<body>
    <!-- Encabezado de la página -->
    <header>
        <section class="section_header">
            <!-- Logo -->
            <figure class="figure_header">
                <img src="./img/logo_header.svg" alt="lgo de kuhtone" />
                <figcaption></figcaption>
            </figure>

            <!-- Menú de navegación -->
            <div class="menu menu-header">
                <figure id="btn_menu">
                    <img src="./img/menu.svg" alt="menu" />
                    <figcaption></figcaption>
                </figure>
                <div id="back_menu"></div>
                <nav id="nav" class="menu-section">
                    <img src="img/logo_header.svG" alt="">
                    <ul>
                        <!-- Enlaces dinámicos según el ID del perfil del profesional -->
                        <?php
                        echo '
                            <li><a href="./index_psicologos.php?id_perfil=' . $id_profesional . '">Inicio</a></li>
                            <li><a href="./queries/consultar_dispo.php?id_perfil=' . $id_profesional . '">Mi disponibilidad</a></li>
                            <li><a href="./perfil_psicologo.php?id_perfil=' . $id_profesional . '">Mi perfil</a></li>
                            <li><a href="./index.php" id="selected">Cerrar Sesión</a></li>';
                        ?>
                    </ul>
                </nav>
            </div>
        </section>
    </header>

    <!-- Contenido principal -->
    <main>
        <?php
        // Verificar si hay un mensaje de error
        if (isset($error_message)) {
            echo '<div class="error-message">' . $error_message . '</div>';
        } else {
            // Mostrar la información del paciente
            echo '
    <div class="Contendor-info-paciente">
        <h3>Asistencia de cita.</h3>
        <div class="psicologo-details--container">
            <h3>Paciente</h3>
            <section>
                <div class="info-group">
                    <img class="small-image" src="./img/NameUsuario.svg" alt="Icono de Nombre">
                    <div>
                        <h4>Nombres:</h4>
                        <p class="paciente-nombre">' . $paciente_nombres . '</p>
                    </div>
                </div>
                <div class="info-group">
                    <img class="small-image" src="./img/NameUsuario.svg" alt="Icono de Apellido">
                    <div>
                        <h4>Apellidos:</h4>
                        <p class="paciente-apellidos">' . $paciente_apellidos . '</p>
                    </div>
                </div>
            </section>
        </div>
    </div>';

            // Añadir el formulario de asistencia del paciente
            echo '
    
        <div class="formulario-asistencia">
        <form method="post">
    <div class="form-group">
        <h3>Asistió a la cita</h3>
        <label class="label-asistio">
            <input type="radio" name="asistio" value="1">
            Sí
        </label>
        <label class="label-asistio" >
            <input type="radio" name="asistio" value="0">
            No
        </label>
    </div>
    <div class="form-group">
        <label class="label-reporte" for="reporte">Reporte del Encuentro</label>
        <textarea id="reporte" name="reporte" rows="4" cols="50" placeholder="Escribe el reporte de la cita"></textarea>
    </div>
    <input type="hidden" name="id_sesion" value="<?php echo $id_sesion; ?>">
    <div class="button-container">
        <input type="submit" value="Guardar Asistencia" class="form-bottom">
    </div>
</form>
        </div>
    ';
        }
        ?>

        <div><a href="./citas_psicologo.php?id_perfil='.$id_paciente.'" class="back--bottom">Volver</a></div>
    </main>

    <!-- Pie de página -->
    <footer class="pie-pagina">
        <div class="footer_copy">
            <small>&copy; 2023 <b>kuhtone</b> - Todos los Derechos Reservados.</small>
        </div>
    </footer>

    <!-- Archivo de script -->
    <script src="js/script.js"></script>
</body>

</html>