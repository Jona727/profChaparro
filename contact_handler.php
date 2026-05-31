<?php
/**
 * Contact Handler
 * 
 * SUGERENCIA TÉCNICA:
 * Para evitar configurar un servidor de correo (SMTP), puedes cambiar la URL de acción 
 * en el formulario de index.php a una de Formspree (https://formspree.io/f/tu_id).
 * 
 * Este script es una alternativa para procesar y luego redirigir.
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? 'Anónimo';
    $email = $_POST['email'] ?? 'No provisto';
    $message = $_POST['message'] ?? '';

    // Aquí iría la lógica de mail() si el servidor está configurado.
    // Ejemplo simple:
    /*
    $to = "tu-correo@ejemplo.com";
    $subject = "Nuevo mensaje de contacto de: $name";
    $headers = "From: $email";
    mail($to, $subject, $message, $headers);
    */

    // Redirigir de vuelta con mensaje de éxito
    header('Location: index.php?contact=success#contact');
    exit;
}
?>
