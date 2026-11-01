<?php
require __DIR__ . '/../config.php';
unset($_SESSION['admin']);
session_regenerate_id(true);
header('Location: login.php');
exit;
