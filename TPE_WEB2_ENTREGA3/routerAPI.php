<?php
    require_once './app/api/libs/router.php';
    require_once './app/api/controllers/api.productos.controller.php';
    require_once './app/api/controllers/user.api.controller.php';

$router = new Router();

$router->addRoute('productos','GET','ProductosAPIController','getProductos');
$router->addRoute('productosPaginado','GET','ProductosAPIController','getProductosPaginado');
$router->addRoute('productos/:ID','GET','ProductosAPIController','getProductosID');
$router->addRoute('addProducto','POST','ProductosAPIController','addProducto');
$router->addRoute('updateProducto/:ID','PUT','ProductosAPIController','updateProducto');
$router->addRoute('user/token', 'GET', 'UserApiController', 'getToken');

$router->route($_REQUEST['resource'], $_SERVER['REQUEST_METHOD']);