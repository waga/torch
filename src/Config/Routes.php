<?php

$routes->get('/admin', 'Torch\Controllers\BaseController::index', ['as' => 'admin-index']);
$routes->get('/admin/login', 'Torch\Controllers\BaseController::login', ['as' => 'admin-login']);
$routes->post('/admin/login', 'Torch\Controllers\BaseController::login');
$routes->get('/admin/register', 'Torch\Controllers\BaseController::register', ['as' => 'admin-register']);
$routes->post('/admin/register', 'Torch\Controllers\BaseController::register');
$routes->get('/admin/logout', 'Torch\Controllers\BaseController::logout', ['as' => 'admin-logout']);

