<?php

use App\Http\Controllers\InboundStuffController;
use App\Http\Controllers\StuffController;
use App\Http\Controllers\StuffStockController;
use App\Http\Controllers\LendingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RestorationController;
use App\Http\Controllers\AuthController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'cors'], function ($router) {

});

$router->get('/inbound/dashboardCalculate', 'InboundStuffController@dashboardCalculate');

$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/profile', 'AuthController@me');

$router->get('/stuff', 'StuffController@index');
$router->post('/stuff/create', 'StuffController@store');
$router->get('/stuff/show/{id}', 'StuffController@show');
$router->patch('/stuff/patch/{id}', 'StuffController@update');
$router->delete('/stuff/delete/{id}', 'StuffController@destroy');
$router->get('/stuff/trash', 'StuffController@deleted');
$router->delete('/stuff/permanent', 'StuffController@permanentDeleteAll');
$router->delete('/stuff/permanentDel/{id}', 'StuffController@permanentDelete');
$router->put('/stuff/restore', 'StuffController@restoreAll');
$router->put('/stuff/restore/{id}', 'StuffController@restore');

$router->get('/inbound', 'InboundStuffController@index');
$router->post('/inbound/create', 'InboundStuffController@store');
$router->patch('/inbound/update/{id}', 'InboundStuffController@update'); 
$router->get('/inbound/show/{id}', 'InboundStuffController@show');
$router->delete('/inbound/destroy/{id}', 'InboundStuffController@destroy');
$router->get('/inbound/trash', 'InboundStuffController@deleted');
$router->delete('/inbound/permanent', 'InboundStuffController@permanentDelAll');    
$router->delete('/inbound/permanentDel/{id}', 'InboundStuffController@permanentDel');
$router->patch('/inbound/restore/{id}', 'InboundStuffController@restore');
$router->patch('/inbound/restore', 'InboundStuffController@restoreAll');

$router->get('/stock', 'StuffStockController@index');
$router->post('/stock/create', 'StuffStockController@store');   
$router->patch('/stock/patch/{id}', 'StuffStockController@update');
$router->get('/stock/show/{id}', 'StuffStockController@show');
$router->delete('/stock/delete/{id}', 'StuffStockController@destroy');
$router->delete('/stock/permanent', 'StuffStockController@permanentDeleteAll');
$router->delete('/stock/permanentDel/{id}', 'StuffStockController@permanentDelete');
$router->put('/stock/restore', 'StuffStockController@restoreAll');
$router->put('/stock/restore/{id}', 'StuffStockController@restore');


$router->get('/lending', 'lendingController@index');
$router->post('/lending/create', 'lendingController@store');
$router->patch('/lending/patch/{id}', 'lendingController@update');
$router->get('/lending/show/{id}', 'lendingController@show');
$router->delete('/lending/delete/{id}', 'lendingController@destroy');
$router->post('/lendings/store', 'LendingController@store');
    
$router->get('/user', 'UserController@index');
$router->post('/user', 'UserController@store');
$router->get('/user/trash', 'UserController@deleted');
$router->delete('/user/permanent', 'UserController@permanentDeleteAll');
$router->delete('/user/permanentDel/{id}', 'UserController@permanentDelete');
$router->put('/user/restore', 'UserController@restoreAll');
$router->put('/user/restore/{id}', 'UserController@restore');
$router->get('/user/{id}', 'UserController@show');
$router->patch('/user/{id}', 'UserController@update');
$router->delete('/user/{id}', 'UserController@destroy');

$router->get('/restoration', 'restorationController@index');
$router->post('/restoration/create', 'restorationController@store');
$router->patch('/restoration/patch/{id}', 'restorationController@update');
$router->get('/restoration/show/{id}', 'restorationController@show');
$router->delete('/restoration/delete/{id}', 'restorationController@destroy');

// $router->post('/login', 'AuthController@authenticate');
