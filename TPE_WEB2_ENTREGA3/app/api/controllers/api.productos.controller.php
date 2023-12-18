<?php
require_once './app/api/views/api.view.php';
require_once './app/models/productos.model.php';
require_once './app/models/categoria.model.php';
require_once './app/api/helpers/auth.api.helper.php';

class ProductosAPIController {

    private $view;
    private $productosModel;
    private $categoriaModel;
    private $auth;
    private $data;
    
    function __construct () {
        $this->auth = new AuthHelper;
        $this->view = new ApiView();
        $this->productosModel = new ProductosModel();
        $this->categoriaModel = new CategoriaModel();
        $this->data = file_get_contents('php://input');
    }

    public function getdata () {
        return json_decode($this->data);
    }
    function getProductos($params = []) {
        if (empty($params)) {
            $sort = 'id_producto';
            $order = 'ASC';
            if (isset($_GET['order'])) {
                $order = $_GET['order'];
                if ($order !='ASC' && $order != 'DESC') {
                    $order = 'ASC';
                }
            }

            if (isset($_GET['sort'])) {
                $sort = $_GET['sort'];
                $columnas = array('nombre', 'descripcion', 'precio');
                if (!in_array($sort, $columnas)) {
                $sort = 'id_producto';
                }
            }

            $productos = $this->productosModel->getProductos($order, $sort);
            if ($productos) {
                $this->view->response($productos, 200);
            } else {
                $this->view->response("Page not found", 404);
            }
        }
    }

    function getProductosPaginado(){
        if (empty($params)) {
            if (!empty($_GET['size']) && (!empty($_GET['page']))) {
                $size = $_GET['size'];
                $page = $_GET['page'];
                $sort = 'id_producto';
                $order = 'ASC';
                if ($page!=0 ) { 
                    $page = ($page * $size) - 1; // le resto -1 ya que no toma el primer producto de la db.
                }
                if (isset($_GET['order'])) {
                    $order = $_GET['order'];
                if ($order !='ASC' && $order != 'DESC') {
                        $order = 'ASC';
                    }
                }
                if (isset($_GET['sort'])) {
                    $sort = $_GET['sort'];
                    $filtros = array('id_producto','nombre', 'descripcion', 'precio', 'id_genero');
                if (!in_array($sort, $filtros)) {
                    $sort = 'id_producto';
                    }
                }
                $productos = $this->productosModel->getProductosPaginado($page, $size, $sort, $order);
                $this->view->response($productos, 200);
                return;
            } else{
                $productos = $this->productosModel->get();
                $this->view->response($productos, 200);
                return;
            }
        }
    }

    function getProductosID ($params = []) {
        $idProducto = $params[':ID'];
        $producto = $this->productosModel->getProductosPorId($idProducto);
        if($producto) {
            $this->view->response($producto, 200);
        } else {
            $this->view->response("Bad request", 400);
        }
    }
    
    function addProducto ($params = []) {
    $user = $this->auth->currentUser();
        if(!$user){
            $this->view->response('Unautorized', 401);
            return;
        }else{
        $addProducto = $this->getdata();
        $nombre = $addProducto->nombre;
        $descripcion = $addProducto->descripcion;
        $precio = $addProducto->precio;
        $id_genero = $addProducto->id_genero;

        if (empty($nombre) || empty($descripcion) || empty($precio) || empty($id_genero)) {
            $this->view->response('Bad request', 400);
            die();
        }

        $productos = $this->productosModel->checkProductos();
        foreach ($productos as $producto) {
            if ($nombre == $producto->nombre) {
                $this->view->response("Bad request", 400);
                die();
            }
        }
        
        $categoria = $this->categoriaModel->getCategoriaUnica($id_genero);
        if (!$categoria) {
            $this->view->response("Bad request", 400); 
            die();  
        }

        $id = $this->productosModel->addProducto($nombre, $descripcion, $precio, $id_genero);
        if ($id>0) {
            $this->view->response("se agrego el producto", 201);
        } else {
            $this->view->response("Bad request", 400);
        }
        }
    }

    function updateProducto($params = []) {
        if (empty($params[':ID'])) {
            $this->view->response('Page not found', 404);
            die();
        }
        $user = $this->auth->currentUser();
        if(!$user){
            $this->view->response('Unautorized', 401);
            return;
        }else{
        $updateProducto = $this->getdata();
        $nombre = $updateProducto->nombre;
        $descripcion = $updateProducto->descripcion;
        $precio = $updateProducto->precio;
        $id_genero = $updateProducto->id_genero;
        if (empty($nombre) || empty($descripcion) || empty($precio) || empty($id_genero)) {
            $this->view->response('Bad request', 400);
            die();
        }
        $id_producto = $params[':ID'];
        $producto = $this->productosModel->getProductoUnico($id_producto);
        if (!$producto) {
            $this->view->response("Bad request", 400);
            die();
        }

        $productos = $this->productosModel->getProductosMenosUno($id_producto);
        foreach ($productos as $producto) {
            if ($nombre == $producto->nombre) {
                $this->view->response("Bad request", 400);
                return;
            }
        }

        $categoria = $this->categoriaModel->getCategoriaUnica($id_genero);
        if (!$categoria) {
            $this->view->response("Bad request", 400);
            die();  
        }

        $updated = $this->productosModel->updateProducto($nombre, $descripcion, $precio, $id_genero, $id_producto);
        if ($updated) {
            $this->view->response('se actualizaron los datos correctamente', 201);
        } else {
            $this->view->response('Bad request', 400);
        }
        }
    }
}

    
