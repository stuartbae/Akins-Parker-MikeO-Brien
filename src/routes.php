<?php

// Register route converters.
// Each converter needs to check if the $id it received is actually a value,
// as a workaround for https://github.com/silexphp/Silex/pull/768.
// $app['controllers']->convert('artist', function ($id) use ($app) {
//     if ($id) {
//         return $app['repository.artist']->find($id);
//     }
// });
// $app['controllers']->convert('comment', function ($id) use ($app) {
//     if ($id) {
//         return $app['repository.comment']->find($id);
//     }
// });
$app['controllers']->convert('user', function ($id) use ($app) {
    if ($id) {
        return $app['repository.user']->find($id);
    }
});
$app['controllers']->convert('group', function ($id) use ($app) {
    if ($id) {
        return $app['repository.group']->find($id);
    }
});

// Register routes.
$app->get('/', 'Swim\Controller\IndexController::indexAction')
    ->bind('homepage');
$app->get('/instruction', 'Swim\Controller\IndexController::indexAction')
    ->bind('instruction');
$app->get('/about', 'Swim\Controller\IndexController::indexAction')
    ->bind('about');

$app->get('/signup', 'Swim\Controller\SignupController::indexAction')
    ->bind('signup');
$app->get('/signup/reset', 'Swim\Controller\SignupController::resetAction')
    ->bind('signup_reset');
$app->match('/signup/user', 'Swim\Controller\SignupController::userSignupAction')
    ->bind('signup_user');

$app->match('/signup/guest/{group}', 'Swim\Controller\SignupController::guestSignupAction')
    ->value('group', false)
    ->bind('signup_guest');
$app->match('/signup/user/{mode}/{group}','Swim\Controller\SignupController::userSignupAction')
    ->value('group', false)
    ->bind('signup_user');

$app->match('/signup/student/{mode}/{group}', 'Swim\Controller\SignupController::studentSignupAction')
    ->value('group', false)
    ->bind('signup_student');
$app->match('/signup/student/guest/{group}', 'Swim\Controller\SignupController::studentSignupAction')
    ->value('mode', 'guest')->value('group', false)
    ->bind('signup_guest_student');
$app->match('/signup/student/host/{group}', 'Swim\Controller\SignupController::studentSignupAction')
    ->value('mode', 'host')->value('group', false)
    ->bind('signup_host_student');

$app->match('/signup/group/{mode}/{group}', 'Swim\Controller\SignupController::pickGroupAction')
    ->value('group', false)
    ->bind('signup_pick_group');
$app->match('/signup/group/guest/{group}', 'Swim\Controller\SignupController::pickGroupAction')
    ->value('mode', 'guest')->value('group', false)
    ->bind('signup_guest_pick_group');
$app->match('/signup/group/host/{group}', 'Swim\Controller\SignupController::pickGroupAction')
    ->value('mode', 'host')->value('host', false)
    ->bind('signup_host_pick_group');

$app->match('/signup/groupdetail/{mode}/{group}', 'Swim\Controller\SignupController::groupDetailAction')
    ->value('group', false)
    ->bind('signup_group_detail');
// $app->match('/signup/group/detail/host/{group}', 'Swim\Controller\SignupController::groupDetailAction')
//     ->value('mode', 'host')->value('group', false)
//     ->bind('signup_host_group_detail');

$app->match('/signup/payment/{mode}/{group}', 'Swim\Controller\SignupController::paymentAction')
    ->value('group', false)
    ->bind('signup_payment');
$app->match('/signup/payment/host/{group}', 'Swim\Controller\SignupController::paymentAction')
    ->value('mode', 'host')->value('group', false)
    ->bind('signup_host_payment');
$app->match('/signup/payment/guest/{group}', 'Swim\Controller\SignupController::paymentAction')
    ->value('mode', 'guest')->value('group', false)
    ->bind('signup_guest_payment');

$app->match('/signup/confirm/{mode}/{group}', 'Swim\Controller\SignupController::confirmAction')
    ->value('group', false)
    ->bind('signup_payment_confirm');
$app->match('/signup/confirm/host/{group}', 'Swim\Controller\SignupController::confirmAction')
    ->value('mode', 'host')->value('group', false)
    ->bind('signup_host_payment_confirm');
$app->match('/signup/confirm/guest/{group}', 'Swim\Controller\SignupController::confirmtAction')
    ->value('mode', 'guest')->value('group', false)
    ->bind('signup_guest_payment_confirm');
// $app->match('/signup/student/host/{group}', 'Swim\Controller\SignupController::studentSignupAction')
//     ->value('group', false)->bind('signup_host_student');

// $app->match('/signup/host/group', 'Swim\Controller\SignupController::pickGroupAction')
//     ->bind('signup_student_pick_group');
// $app->match('/signup/host/group/detail', 'Swim\Controller\SignupController::groupDetailAction')
//     ->bind('signup_group_detail');
// $app->match('/signup/host/payment', 'Swim\Controller\SignupController::paymentAction')
//     ->bind('signup_payment');
// $app->match('/signup/host/confirm', 'Swim\Controller\SignupController::confirmAction')
//     ->bind('signup_payment_confirm');


// $app->match('/signup/guest/user/{group}', 'Swim\Controller\SignupController::guestUserSignupAction')
//     ->bind('signup_guest_user');
// $app->match('/signup/guest/student/{group}', 'Swim\Controller\SignupController::guestStudentSignupAction')
//     ->bind('signup_guest_student');
// $app->match('/signup/guest/group/{group}', 'Swim\Controller\SignupController::guestPickGroupAction')
//     ->bind('signup_guest_student_pick_group');
// $app->match('/signup/guest/payment/{group}', 'Swim\Controller\SignupController::guestPaymentAction')
//     ->bind('signup_guest_payment');
// $app->match('/signup/guest/confirm/{group}', 'Swim\Controller\SignupController::confirmAction')
//     ->bind('signup_guest_payment_confirm');



// $app->match('/signup/host/step/{step}', 'Swim\Controller\SignupController::hostSignupAction')
//     ->bind('signup_host');
// $app->match('/signup/guest/step/{step}', 'Swim\Controller\SignupController::guestSignupAction')
//     ->bind('signup_guest');
$app->match('/signup/placement', 'Swim\Controller\SignupController::placementAction')
    ->bind('signup_placement');

$app->get('/me', 'Swim\Controller\UserController::meAction')
    ->bind('me');
$app->match('/login', 'Swim\Controller\UserController::loginAction')
    ->bind('login');
$app->get('/logout', 'Swim\Controller\UserController::logoutAction')
    ->bind('logout');

// $app->get('/artists', 'Swim\Controller\ArtistController::indexAction')
//     ->bind('artists');
// $app->match('/artist/{artist}', 'Swim\Controller\ArtistController::viewAction')
//     ->bind('artist');
// $app->match('/artist/{artist}/like', 'Swim\Controller\ArtistController::likeAction')
//     ->bind('artist_like');
// $app->get('/api/artists', 'Swim\Controller\ApiArtistController::indexAction');
// $app->get('/api/artist/{artist}', 'Swim\Controller\ApiArtistController::viewAction');
// $app->post('/api/artist', 'Swim\Controller\ApiArtistController::addAction');
// $app->put('/api/artist/{artist}', 'Swim\Controller\ApiArtistController::editAction');
// $app->delete('/api/artist/{artist}', 'Swim\Controller\ApiArtistController::deleteAction');

$app->get('/admin', 'Swim\Controller\AdminController::indexAction')
    ->bind('admin');

// $app->get('/admin/artists', 'Swim\Controller\AdminArtistController::indexAction')
//     ->bind('admin_artists');
// $app->match('/admin/artists/add', 'Swim\Controller\AdminArtistController::addAction')
//     ->bind('admin_artist_add');
// $app->match('/admin/artists/{artist}/edit', 'Swim\Controller\AdminArtistController::editAction')
//     ->bind('admin_artist_edit');
// $app->match('/admin/artists/{artist}/delete', 'Swim\Controller\AdminArtistController::deleteAction')
//     ->bind('admin_artist_delete');

$app->get('/admin/users', 'Swim\Controller\AdminUserController::indexAction')
    ->bind('admin_users');
$app->match('/admin/users/add', 'Swim\Controller\AdminUserController::addAction')
    ->bind('admin_user_add');
$app->match('/admin/users/{user}/edit', 'Swim\Controller\AdminUserController::editAction')
    ->bind('admin_user_edit');
$app->match('/admin/users/{user}/delete', 'Swim\Controller\AdminUserController::deleteAction')
    ->bind('admin_user_delete');

$app->get('/api/users', 'Swim\Controller\ApiUserController::indexAction')
    ->bind('api_users');
$app->get('/api/user/{user}', 'Swim\Controller\ApiUserController::viewAction')
    ->bind('api_user');
$app->post('/api/user', 'Swim\Controller\ApiUserController::addAction');
$app->put('/api/user/{user}', 'Swim\Controller\ApiUserController::editAction');
$app->delete('/api/user/{user}', 'Swim\Controller\ApiUserController::deleteAction');


