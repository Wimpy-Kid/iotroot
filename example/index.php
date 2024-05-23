<?php

require_once '../src/iotroot.php';
require_once '../src/encrypter.php';

use Cherrylu\iotroot\iotroot;
use Cherrylu\iotroot\encrypter;


$iotroot = new iotroot('input-your-client-id-here', 'input-your-key-here');

$templates = $iotroot->getTemplates();

var_dump([
    'client_id' => encrypter::getClientId(),
    'key' => encrypter::getKey(),
    'time_stamp' => encrypter::getTimeStamp(),
    'sign' => encrypter::getSignString(),
    'templates' => $templates,
]);