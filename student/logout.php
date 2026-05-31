<?php
session_start();
unset($_SESSION['student_logged_in']);
unset($_SESSION['student_id']);
unset($_SESSION['student_name']);
unset($_SESSION['student_username']);
header('Location: ../index.php');
exit;
?>
