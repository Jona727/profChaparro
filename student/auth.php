<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_logged_in'])) {
    $in_student_dir = basename(dirname($_SERVER['PHP_SELF'])) === 'student';
    $redirect_url = $in_student_dir ? 'login.php' : 'student/login.php';
    header('Location: ' . $redirect_url);
    exit;
}
?>
