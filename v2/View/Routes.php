<?php

class Routes
{
    private $routes = [];
    private $method;
    private $uri;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function get($path, $model, $action)
    {
        $this->addRoute('GET', $path, $model, $action);
    }

    public function post($path, $model, $action)
    {
        $this->addRoute('POST', $path, $model, $action);
    }

    public function put($path, $model, $action)
    {
        $this->addRoute('PUT', $path, $model, $action);
    }

    public function delete($path, $model, $action)
    {
        $this->addRoute('DELETE', $path, $model, $action);
    }

    private function addRoute($method, $path, $model, $action)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'model' => $model,
            'action' => $action
        ];
    }

    public function dispatch()
    {
        foreach ($this->routes as $key => $route) {
            if ($route['method'] === $this->method && $this->matchUri($route['path'])) {
                return $this->executeRoute($route);
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Routes: rule not found', 'path' => $this->uri]);
        exit;
    }

    private function matchUri($pattern)
    {
        $pattern = preg_replace('/\{[a-zA-Z]+\}/', '(.+)', $pattern);
        return preg_match("#^{$pattern}$#", $this->uri);
    }

    private function executeRoute($route)
    {
        //$modelPath = __DIR__ .'\\..\\Model\\'.$route['model'].'.php';
        //$modelPath = str_replace('View\\', '', $modelPath);
        $modelPath = dirname(__DIR__) . '/Model/' . $route['model'] . '.php';
        $modelClass = $route['model'];
        $action = $route['action'];

        if (!file_exists($modelPath)) {
            http_response_code(500);
            echo json_encode(['error' => 'Routes: Model file not found at '.$modelPath]);
            exit;
        }

        require_once($modelPath);

        if (!class_exists($modelClass)) {
            http_response_code(500);
            echo json_encode(['error' => 'Routes: Model class '.$modelClass.' is not found']);
            exit;
        }

        $obj = new $modelClass();
        return $obj->$action();
    }
}
?>