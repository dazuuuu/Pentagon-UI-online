<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
Auth::logoutClient();
flash('success', 'Signed out.');
redirect('/client/login.php');
