
<?php
/**
 * Front to the Ziki app. This file doesn't do anything, but bootstraps
 * Ziki to load start.
 *
 * @package Ziki
 */
define('ZIKI', true);
define('ZIKI_BASE_PATH', __DIR__);

$ziki = require_once ZIKI_BASE_PATH . '/src/bootstrap.php';

$ziki->start();

