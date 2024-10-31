<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Util\Autoload;

Autoload::addNamespace( '', __DIR__ . '/../../' );
require_once __DIR__ . '/../../../../../../wp-load.php';

class Unit extends \Codeception\Module
{

}
