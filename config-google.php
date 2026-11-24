<?php

require_once 'vendor/autoload.php';

$client = new Google_Client();

$client->setClientId('189580554145-2vd41gkm0jsqgfhukf6ibm895o0hps2f.apps.googleusercontent.com');
$client->setClientSecret('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');

$client->setRedirectUri('http://localhost/project_caffe_tf2/google-callback.php');

$client->addScope('email');
$client->addScope('profile');

$client->setPrompt('select_account');