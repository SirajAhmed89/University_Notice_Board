<?php
require_once '../config.php';
require_once '../includes/functions.php';

session_start();
session_destroy();
redirectTo('/auth/login.php');
?>
