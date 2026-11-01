<?php
require __DIR__ . '/config.php';
unset($_SESSION['voter']);
session_regenerate_id(true);
header('Location: index.php');
exit;
