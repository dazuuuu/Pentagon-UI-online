<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::logoutAdmin();
flash('success', 'You have been signed out.');
redirect('/admin/login.php');
