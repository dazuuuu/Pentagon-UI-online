<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::logoutClient();
flash('success', 'Signed out.');
redirect('/client/login.php');
