<?php
// File: /app/Controllers/HomeController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        if (!file_exists(STORAGE_PATH . '/installed')) {
            header('Location: /NexaT/install');
            exit;
        }
        
        if ($this->auth->check()) {
            header('Location: /NexaT/dashboard');
            exit;
        }
        
        header('Location: /NexaT/login');
        exit;
    }
}