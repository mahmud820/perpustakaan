<?php

class Beranda extends Controller {
  public function index()
  {
    $data['judul'] = 'Beranda';
    $this->view('templates/header');
    $this->view('beranda/index');
    $this->view('templates/footer');
  }
}