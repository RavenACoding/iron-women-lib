<!-- LOGOUT but we making it two lines of code --->
<?php session_start(); session_unset(); session_destroy(); header("Location: index.php"); exit(); ?>