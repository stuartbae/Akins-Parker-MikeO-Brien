<?php

// include the prod configuration
require __DIR__.'/prod.php';

// enable the debug mode
$app['debug'] = true;
$app['twig.options.cache'] = false;
$app['stripe.api.key'] ='sk_test_pwimf8utQOD91UBpVUMQ6gxd';
