<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Feed principal
$routes->get('/', 'Home::index');

// Autenticación
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::procesarLogin');
$routes->post('/registro', 'Auth::registro');
$routes->get('/logout', 'Auth::logout');

// Acciones sobre publicaciones (protegidas: requieren sesión activa)
$routes->group('publicaciones', ['filter' => 'auth'], function ($routes) {
    $routes->post('crear', 'Publicaciones::crear');
    $routes->post('(:num)/like', 'Publicaciones::like/$1');
    $routes->post('(:num)/guardar', 'Publicaciones::guardar/$1');
    $routes->post('(:num)/comentar', 'Publicaciones::comentar/$1');
});