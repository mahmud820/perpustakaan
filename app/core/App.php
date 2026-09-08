<?php

class App {
  protected $controller = 'Beranda';
  protected $method = 'index';
  protected $params = [];

  public function __construct()
  {
    $url = $this->parseUrl();

    if (!empty($url) && strtolower($url[0]) === 'login') {
      $this->controller = 'Auth';
      $this->method = 'login';
      unset($url[0]);
    } else if (!empty($url)) {
      $controllerName = $this->resolveControllerName($url[0]);

      if ($controllerName) {
        $this->controller = $controllerName;
        unset($url[0]);
      } else {
        die ("Controller <strong>{$url[0]}</strong> tidak ditemukan.");
      }
    }

    require_once '../app/controllers/' . $this->controller . '.php';
    $this->controller = new $this->controller;

    if (isset($url[1])) {
      if (method_exists($this->controller, $url[1])) {
        $this->method = $url[1];
        unset($url[1]);
      }
    }

    $this->params = !empty($url) ? array_values($url) : [];

    call_user_func_array([$this->controller, $this->method], $this->params);
  }

  private function resolveControllerName($name)
  {
    if (empty($name)) {
      return null;
    }

    if (file_exists('../app/controllers/' . $name . '.php')) {
      return $name;
    }

    $files = scandir('../app/controllers');

    foreach ($files as $file) {
      if (strtolower($file) === strtolower($name) . '.php') {
        return pathinfo($file, PATHINFO_FILENAME);
      }
    }

    return null;
  }

  public function parseUrl() 
  {
      if (isset($_GET['url'])) {
        $url = rtrim($_GET['url'], '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return explode('/', $url);
      }
      return [];
  }
}