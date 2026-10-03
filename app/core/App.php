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
        // Nilai dari URL WAJIB di-escape sebelum dicetak (mencegah reflected XSS)
        http_response_code(404);
        die('Controller <strong>' . htmlspecialchars($url[0]) . '</strong> tidak ditemukan.');
      }
    }

    require_once __DIR__ . '/../controllers/' . $this->controller . '.php';
    $this->controller = new $this->controller;

    if (isset($url[1]) && $this->isRoutableMethod($this->controller, $url[1])) {
      $this->method = $url[1];
      unset($url[1]);
    }

    $this->params = !empty($url) ? array_values($url) : [];

    call_user_func_array([$this->controller, $this->method], $this->params);
  }

  // Hanya method public milik controller yang boleh dipanggil lewat URL.
  // is_callable() dari luar class otomatis menolak method protected/private
  // (mis. view() dan model() di base Controller), dan kita tolak juga __construct dkk.
  private function isRoutableMethod($controller, $method)
  {
    return strpos($method, '__') !== 0 && is_callable([$controller, $method]);
  }

  private function resolveControllerName($name)
  {
    // Nama controller hanya boleh huruf/angka/underscore, supaya tidak bisa
    // dipakai menyusup ke path lain (mis. "..\config\config" di Windows).
    if (empty($name) || !preg_match('/^[A-Za-z0-9_]+$/', $name)) {
      return null;
    }

    $folder = __DIR__ . '/../controllers';

    if (file_exists($folder . '/' . $name . '.php')) {
      return $name;
    }

    foreach (scandir($folder) as $file) {
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
