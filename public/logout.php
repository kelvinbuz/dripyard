<?php
require_once __DIR__ . '/../backend/auth.php';

logoutUser();

header('Location: login.php');
exit;
