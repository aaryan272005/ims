<?php

require_once __DIR__ . '/../partials/security.php';

require_login('../login.php');
require_post_csrf(false, '../dashboard.php');

$_SESSION = [];
session_destroy();

header('Location: ../login.php');
exit();
