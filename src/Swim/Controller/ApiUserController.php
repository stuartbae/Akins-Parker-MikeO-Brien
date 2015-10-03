<?php

namespace Swim\Controller;

use Swim\Entity\User;
use Swim\Form\Type\UserType;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ApiUserController
{
    public function indexAction(Request $request, Application $app)
    {
        $limit = $request->query->get('limit', 20);
        $offset = $request->query->get('offset', 0);
        $users = $app['repository.user']->findAll($limit, $offset);
        $data = array();
        foreach ($users as $user) {
            $data[] = array(
                // 'id' => $user->getId(),
                // 'name' => $user->getFullName(),
                // 'spouse_name' => $user->getSpouseName(),
                // 'mobile' => $user->getMobile(),
                // 'home' => $user->getHome(),
                // 'street' => $user->getAddress()->getStreet(),
                // 'city' => $user->getAddress()->getCity(),
                // 'state' => $user->getAddress()->getState(),
                // 'zip' => $user->getAddress()->getZip(),
                'id' => $user->getId(),
                'name' => $user->getFullName(),
                'email' => $user->getEmail(),
                'children' => '#',
                'phone' => $user->getMobile(),
                'registered' => $user->getCreatedAt(),
            );
        }
        return $app->json($data);
    }
    public function viewAction(Request $request, Application $app)
    {
        $user = $request->attributes->get('user');
        if (!$user) {
            return $app->json('Not Found', 404);
        }
        $data = array(
            'id' => $user->getId(),
            'name' => $user->getFullName(),
            'spouse_name' => $user->getSpouseName(),
            'mobile' => $user->getMobile(),
            'home' => $user->getHome(),
            'street' => $user->getAddress()->getStreet(),
            'city' => $user->getAddress()->getCity(),
            'state' => $user->getAddress()->getState(),
            'zip' => $user->getAddress()->getZip(),
            // 'short_biography' => $user->getShortBiography(),
            // 'biography' => $user->getBiography(),
            // 'soundcloud_url' => $user->getSoundCloudUrl(),
            // 'likes' => $user->getLikes(),
        );
        return $app->json($data);
    }
    public function addAction(Request $request, Application $app)
    {
        if (!$request->request->has('name')) {
            return $app->json('Missing required parameter: name', 400);
        }
        if (!$request->request->has('short_biography')) {
            return $app->json('Missing required parameter: short_biography', 400);
        }
        $user = new User();
        $user->setName($request->request->get('name'));
        $user->setShortBiography($request->request->get('short_biography'));
        $user->setBiography($request->request->get('biography'));
        $user->setSoundCloudUrl($request->request->get('soundcloud_url'));
        $app['repository.user']->save($user);
        $headers = array('Location' => '/api/user/' . $user->getId());
        return $app->json('Created', 201, $headers);
    }
    public function editAction(Request $request, Application $app)
    {
        $user = $request->attributes->get('user');
        if (!$user) {
            return $app->json('Not Found', 404);
        }
        if (!$request->request->has('name')) {
            return $app->json('Missing required parameter: name', 400);
        }
        if (!$request->request->has('short_biography')) {
            return $app->json('Missing required parameter: short_biography', 400);
        }
        $user->setName($request->request->get('name'));
        $user->setShortBiography($request->request->get('short_biography'));
        $user->setBiography($request->request->get('biography'));
        $user->setSoundCloudUrl($request->request->get('soundcloud_url'));
        $app['repository.user']->save($user);
        return $app->json('OK', 200);
    }
    public function deleteAction(Request $request, Application $app)
    {
        $user = $request->attributes->get('user');
        if (!$user) {
            return $app->json('Not Found', 404);
        }
        $app['repository.user']->delete($user);
        return $app->json('No Content', 204);
    }
}
