<?php

date_default_timezone_set('Africa/Nairobi');

require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/Auth.php';
require_once __DIR__ . '/lib/Mailer.php';
require_once __DIR__ . '/lib/Migrator.php';
require_once __DIR__ . '/lib/Settings.php';

$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['timezone'] ?? 'Africa/Nairobi');

Auth::startSession();
