<?php

date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_ALL, 'pt_BR.UTF-8');

require_once __DIR__ . '/../View/Routes.php';
require_once __DIR__ . '/../API/Auth.php';

class Index {

    // variaveis global
    private $routes;
    private $method; 
    private $path; 
    private $auth_api; 
    private $auth;

    public function __construct() {
        $this->routes = new \Routes();
        $this->auth_api = new \Auth();  
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->auth = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        switch ($this->method) {
            case 'GET':
                $this->handleGetRequest($this->path);
                break;
            case 'POST':
                $this->handlePostRequest($this->path);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
        }
    }

    // GET Routes
    function handleGetRequest($path) {
        // ROTAS DEFAULT
        if (strpos($path, '/info')!==false){
            echo phpinfo();
        }
        elseif (strpos($path, '/health')!==false){
            http_response_code(200);   
            echo json_encode([
                'status' => 'OK', 
                'php_version' => $_SERVER['SERVER_SOFTWARE'],
                'project_version'=> '1.0.0', 
                "time_consult" => date('Y-m-d H:i:s'),
                'developer_info' => [
                    'name' => 'Clemerson L Oliveira',
                    'email' => 'clemerson.lucas.oliveira@gmail.com'
                ],
                'time_zone' => date_default_timezone_get(),
                'timestamp' => time()
            ]);
        }
        
        // ROULES BLOQUEADAS POR TOKEN

        elseif (strpos($path, '/v2')!==false){
            if ($this->auth && strpos($this->auth, 'Bearer ') === 0) {
                $token = substr($this->auth, 7);
                $response = $this->auth_api->validateToken($token);
                // TOKEN VALIDO, CONTINUAR COM A ROTA
                if ($response) {
                
                    // MACHINES
                    if (strpos($path, '/v2/machines') !== false){
                        $this->routes->get('/v2/machines', 'ModelMachine', 'getAll');
                        $response = $this->routes->dispatch();    
                        echo json_encode(['status' => 'OK', 'Total Machines' => count($response),  'data' => $response]);
                    }

                     // CARD FLAGS
                    if (strpos($path, '/v2/card-flags') !== false){
                        $this->routes->get('/v2/card-flags', 'ModelCardFlags', 'getAll');
                        $response = $this->routes->dispatch();    
                        echo json_encode(['status' => 'OK', 'Total Card Flags' => count($response),  'data' => $response]);
                    }



                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Index: Invalid or expired token']);
                }
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Index: Missing or invalid authorization']);
            }
        }
        
        
        
        
        else{
            http_response_code(404);
            echo json_encode(['error' => 'Index: Route not found']);
        }
    }

    // POST Routes
    function handlePostRequest($path) {
        // LOGIN
        if (strpos($path, '/auth/login') !== false) {
            if ($this->auth && strpos($this->auth, 'Basic ') === 0) {
                $credentials = base64_decode(substr($this->auth, 6));
                list($username, $password) = explode(':', $credentials, 2);
                $response = $this->auth_api->login($username, $password);
                if ($response){
                    http_response_code(200);
                    echo json_encode(['status' => 'OK', 'message' => 'Index: Login successful', 'result' => $response]);
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Index: Login not authorized']);
                }
            } 
            else {
                http_response_code(401);
                echo json_encode(['error' => 'Index: Missing or invalid authorization']);
            }
        } 

        // LOGOUT
        elseif (strpos($path, '/auth/logout') !== false) {
            if ($this->auth && strpos($this->auth, 'Bearer ') === 0) {
                $token = substr($this->auth, 7);
                $response = $this->auth_api->logout($token);
                if ($response) {
                    http_response_code(200);
                    echo json_encode(['status' => 'OK', 'message' => 'Index: Logout successful']);
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Index: Logout not authorized']);
                }
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Index: Missing or invalid authorization']);
            }
        }

        
        
        //VALIDATE TOKEN
        elseif (strpos($path, '/auth/validate-token') !== false) {
            if ($this->auth && strpos($this->auth, 'Bearer ') === 0) {
                $token = substr($this->auth, 7);
                $response = $this->auth_api->validateToken($token);
                if ($response) {
                    http_response_code(200);
                    echo json_encode(['status' => 'OK', 'message' => 'Index: Token is valid', 'result' => $response]);
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Index: Invalid or expired token']);
                }
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Index: Missing or invalid authorization']);
            }
        }            
        else {
            http_response_code(404);
            echo json_encode(['error' => 'Index: Route not found']);
        }
        
    }
}

new Index();