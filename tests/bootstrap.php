<?php
declare(strict_types=1);

/**
 * Test suite bootstrap for BootstrapTools.
 */
$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);

    throw new Exception('Cannot find the root of the application, unable to run tests');
};
$root = $findRoot(__FILE__);
unset($findRoot);

chdir($root);

require_once $root . '/vendor/autoload.php';

require_once $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';

require_once $root . '/vendor/cakephp/cakephp/src/functions.php';

use BootstrapTools\BootstrapToolsPlugin;
use Cake\Controller\Controller;
use Cake\Core\Plugin;

Plugin::getCollection()->add(new BootstrapToolsPlugin());

if (!class_exists('App\Controller\AppController')) {
    class_alias(Controller::class, 'App\Controller\AppController');
}
