<?php
/* SSL Management */
$useSSL = true;

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once('PrestoChangeoClasses/init.php');
require_once('controllers/front/validationdpn.php');

$_POST['module'] = 'authorizedotnet'; // for presta 1.5 

$controller = new AuthorizedotnetValidationdpnModuleFrontController('authorizedotnet');

$controller->run();