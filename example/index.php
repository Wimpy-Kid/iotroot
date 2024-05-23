<?php

require_once '../src/ioroot.php';
require_once '../src/encrypter.php';

use Cherrylu\iotroot\ioroot;
use Cherrylu\iotroot\encrypter;


$ecode = new ioroot('input-your-client-id-here', 'input-your-key-here');

$templates = $ecode->getTemplates();

dd([
    'client_id' => encrypter::getClientId(),
    'key' => encrypter::getKey(),
    'time_stamp' => encrypter::getTimeStamp(),
    'sign' => encrypter::getSignString(),
    'templates' => $templates,
]);