<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
Auth::logoutAdmin();
flash('success', 'You have been signed out.');
redirect(base_path('/admin/login.php'));
