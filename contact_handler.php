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

    $to = "brunolomejor2016@gmail.com";
    $subject = "Nuevo mensaje de contacto de: $name";
    $body = "Nombre: $name\nCorreo: $email\n\nMensaje:\n$message";
    $headers = "From: no-reply@profchaparro.com\r\nReply-To: $email\r\n";
    @mail($to, $subject, $body, $headers);

    // Redirigir de vuelta con mensaje de éxito
    header('Location: index.php?contact=success#contact');
    exit;
}
?>
