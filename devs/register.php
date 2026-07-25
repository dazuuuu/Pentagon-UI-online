<?php
/**
 * Legacy entrypoint — admin registration now lives under the web root:
 *   /Pentagon Quest UI/devs/register.php
 */
require_once dirname(__DIR__) . '/apps/backend/bootstrap.php';
redirect(url('devs/register.php'));
