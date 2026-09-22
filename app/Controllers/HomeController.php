<?php
// File: /app/Controllers/HomeController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        if (!file_exists(STORAGE_PATH . '/installed')) {
            header('Location: ' . BASE_URL . '/install');
            exit;
        }

        header('Location: ' . BASE_URL . ($this->auth->check() ? '/dashboard' : '/login'));
        exit;
    }
}