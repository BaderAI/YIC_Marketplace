<?php
require_once 'classes/Auth.php';

Auth::requireLogin();

if (Auth::isAdmin()) {
    include 'admin_dashboard.php';
    exit();
}

include 'profile.php';
exit();
