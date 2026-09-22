<?php
// File: /app/Controllers/AuthController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\SettingsService;

class AuthController extends Controller
{
    public function login(): void
    {
        if ($this->auth->check()) {
            $this->redirect('dashboard');
            return;
        }

        $email    = $_SESSION['old_email'] ?? '';
        $password = $_SESSION['old_password'] ?? '';
        unset($_SESSION['old_email'], $_SESSION['old_password']);

        echo $this->view->render('auth/login', [
            'email'    => $email,
            'password' => $password,
        ]);
    }

    public function authenticate(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $_SESSION['old_email']    = $email;
        $_SESSION['old_password'] = $password;

        if ($email === '' || $password === '') {
            $this->flashError('Please enter your email and password.');
            session_write_close();
            $this->redirect('login');
        }

        if (!$this->auth->attempt($email, $password)) {
            $this->flashError('Invalid email/password. Please try again!');
            session_write_close();
            $this->redirect('login');
        }

        $user = $this->auth->getUser();

        if ($user) {
            $_SESSION['school_id']   = $user->school_id ?? 1;
            $_SESSION['school_name'] = $this->getSchoolName();
        }

        unset($_SESSION['old_email'], $_SESSION['old_password']);

        $this->flashSuccess('Welcome back!');
        session_write_close();
        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->auth->logout();
        unset($_SESSION['school_id'], $_SESSION['school_name']);

        $this->flashSuccess('You have been logged out successfully!');
        session_write_close();
        $this->redirect('login');
    }

    private function getSchoolName(): string
    {
        $settings = new SettingsService();
        return $settings->get('school.name', 'My School');
    }
}