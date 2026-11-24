<?php

require 'config-google.php';

header("Location: " . filter_var($client->createAuthUrl(), FILTER_SANITIZE_URL));

exit;
