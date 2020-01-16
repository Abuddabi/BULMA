<?php

namespace App\Controllers\Admin;


class HomeController extends Controller
{
    public function index()
    {
      echo $this->view->render('admin/dashboard');
    }
}