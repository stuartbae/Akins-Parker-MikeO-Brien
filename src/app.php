<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

if($app['debug']) {
ExceptionHandler::register();
ErrorHandler::register();
}

// Register service providers.
$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider());
$app->register(new Silex\Provider\SwiftmailerServiceProvider());

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'admin' => array(
            'pattern' => '^/',
            'form' => array(
                'login_path' => '/login',
                'check_path' => '/admin/login_check',
                'username_parameter' => 'form[username]',
                'password_parameter' => 'form[password]',
            ),
            'logout'  => true,
            'anonymous' => true,
            'users' => $app->share(function () use ($app) {
                return new Swim\Repository\UserRepository($app['db'], $app['security.encoder.digest'], $app['repository.address']);
            }),
        ),
    ),
    'security.role_hierarchy' => array(
       'ROLE_ADMIN' => array('ROLE_USER'),
    ),
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true,
    ),
    'twig.form.templates' => array('form_div_layout.html.twig', 'common/form_div_layout.html.twig'),
    'twig.path' => array(__DIR__ . '/../app/views')
));

// Register repositories.
// $app['repository.lession'] = $app->share(function ($app) {
//     return new Swim\Repository\ArtistRepository($app['db']);
// });

$app['repository.address'] = $app->share(function ($app) {
    return new Swim\Repository\AddressRepository($app['db']);
});

$app['repository.user'] = $app->share(function ($app) {
    return new Swim\Repository\UserRepository($app['db'], $app['security.encoder.digest'], $app['repository.address']);
});

$app['repository.pool'] = $app->share(function ($app) {
    return new Swim\Repository\PoolRepository($app['db'], $app['repository.address']);
});

$app['repository.lesson'] = $app->share(function ($app) {
    return new Swim\Repository\LessonRepository($app['db'], $app['repository.user'], $app['repository.pool']);
});

$app['repository.group'] = $app->share(function ($app) {
    return new Swim\Repository\GroupRepository($app['db'], $app['repository.lesson'], $app['repository.user']  );
});

$app['repository.helper'] = $app->share(function ($app) {
    return new Swim\Repository\HelperRepository($app['db']);
});

$app['repository.coupon'] = $app->share(function ($app) {
    return new Swim\Repository\CouponRepository($app['db']);
});

// Protect admin urls.
$app->before(function (Request $request) use ($app) {
    $protected = array(
        '/admin/' => 'ROLE_ADMIN',
        '/me' => 'ROLE_USER',
    );
    $path = $request->getPathInfo();
    foreach ($protected as $protectedPath => $role) {
        if (strpos($path, $protectedPath) !== FALSE && !$app['security']->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }
});

// Register the error handler.
$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
});

return $app;
