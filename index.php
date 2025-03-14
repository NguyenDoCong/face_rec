<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoload classes
spl_autoload_register(function ($class) {
    $file = str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Kiểm tra xem request có phải là AJAX không
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Get controller and action from request
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'Student';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Nếu không có action và không phải AJAX request, render view trực tiếp
if ($action === 'index' && !isAjaxRequest()) {
    include 'View/main.php';
    exit;
}

// Nếu là AJAX request, đảm bảo trả về JSON
if (isAjaxRequest()) {
    header('Content-Type: application/json');
}

try {
    // Create controller instance
    $controllerClass = "Controller\\{$controller}Controller";
    if (!class_exists($controllerClass)) {
        throw new Exception("Controller không tồn tại: " . $controllerClass);
    }

    $controller = new $controllerClass();
    if (!method_exists($controller, $action)) {
        throw new Exception("Action không tồn tại: " . $action);
    }

    // Gọi action
    $controller->$action();

} catch (Exception $e) {
    if (isAjaxRequest()) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'debug_info' => [
                'controller' => $controllerClass,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]
        ]);
    } else {
        echo "Lỗi: " . $e->getMessage();
    }
}