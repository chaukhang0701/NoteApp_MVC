<?php
class Router {
    private array $routes = [];
    

    public function get(string $uri, $callback): void {
        $this->routes['GET'][$uri] = $callback;
    }

    public function post(string $uri, $callback): void {
        $this->routes['POST'][$uri] = $callback;
    }

    public function resolve(): void {
        $method = $_SERVER['REQUEST_METHOD'];
    
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
    
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
        // ✅ Cắt base path tự động
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($base !== '' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
    
        $uri = '/' . trim($uri, '/');
    
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
    
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                call_user_func_array($callback, $matches);
                return;
            }
        }
    
        http_response_code(404);
        echo "<h1 style='color:red;text-align:center;margin-top:50px;'>
                404 — Route không tồn tại: <code>{$uri}</code>
              </h1>";
    }
}