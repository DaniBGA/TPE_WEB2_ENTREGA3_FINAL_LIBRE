<?php
    require_once 'app/api/controllers/api.productos.controller.php';
    require_once 'app/api/helpers/auth.api.helper.php';
    require_once 'app/models/user.model.php';

    class UserApiController extends ProductosAPIController {
        private $model;
        private $authHelper;
        private $view;

        function __construct() {
            parent::__construct();
            $this->authHelper = new AuthHelper();
            $this->model = new UserModel();
            $this->view = new ApiView();
        }

        function getToken($params = null) {
            $basic = $this->authHelper->getAuthHeaders(); 

            if(empty($basic)) {
                $this->view->response('No envió encabezados de autenticación.', 401);
                return;
            }

            $basic = explode(" ", $basic); 

            if($basic[0]!="Basic") {
                $this->view->response('Los encabezados de autenticación son incorrectos.', 401);
                return;
            }

            $userpass = base64_decode($basic[1]); 
            $userpass = explode(":", $userpass); 

            $users = $this->model->getUsers();
            $userdata = [];
            $usuarioEncontrado = false;
            foreach($users as $user){
                if($user->usuario == $userpass[0] && password_verify($userpass[1], $user->contraseña)){
                    $usuarioEncontrado = true;
                    $userdata = [
                        "usuario" => $user->usuario,
                        "rol" => $user->rol
                    ];
                    break;
                }
            } 
            if($usuarioEncontrado){
                $token = $this->authHelper->createToken($userdata);
                $this->view->response($token, 201);
            } else {
                $this->view->response('El usuario o contraseña son incorrectos.', 401);
            }
        }
        
    }
