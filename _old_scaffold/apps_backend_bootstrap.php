<?php
require_once dirname(__DIR__) . '/path.php';

putenv('PQ_CONFIG_PATH=' . __DIR__ . '/config.php');
date_default_timezone_set('Africa/Nairobi');

require_once dirname(__DIR__, 2) . '/backend/lib/helpers.php';
require_once dirname(__DIR__, 2) . '/backend/lib/Database.php';
require_once dirname(__DIR__, 2) . '/backend/lib/Auth.php';
require_once dirname(__DIR__, 2) . '/backend/lib/Mailer.php';
require_once dirname(__DIR__, 2) . '/backend/lib/Migrator.php';
require_once dirname(__DIR__, 2) . '/backend/lib/Settings.php';

$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['timezone'] ?? 'Africa/Nairobi');

Auth::startSession();
