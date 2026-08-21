<?php
// File: /app/Controllers/AuthController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class AuthController extends Controller
{   
    public function login(): void
    {
        if ($this->auth->check()) {
            $this->redirect('dashboard');
            exit;
        }

        // Retrieve old inputs after failed attempt
        $email = $_SESSION['old_email'] ?? '';
        $password = $_SESSION['old_password'] ?? '';
        
        // Clear session old inputs so they don't persist on fresh page navigation
        unset($_SESSION['old_email'], $_SESSION['old_password']);

        $data = [
            'email' => $email,
            'password' => $password
        ];

        echo $this->view->render('auth/login', $data);
    }

    public function authenticate(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Store both email and password in session for failed attempt repopulation
        $_SESSION['old_email'] = $email;
        $_SESSION['old_password'] = $password;
        
        if (empty($email) || empty($password)) {
            $this->view->flash('error', 'Please enter your email and password.');
            session_write_close();
            $this->redirect('login');
            exit;
        }
        
        if ($this->auth->attempt($email, $password)) {
            $user = $this->auth->getUser();
            if ($user) {
                $_SESSION['school_id'] = $user->school_id ?? 1;
                $_SESSION['school_name'] = $this->getSchoolName($user->school_id ?? 1);
            }
            
            // Clean up session inputs on success
            unset($_SESSION['old_email'], $_SESSION['old_password']);
            
            $this->view->flash('success', 'Welcome back!');
            session_write_close();
            $this->redirect('dashboard');
            exit;
        }
        
        $this->view->flash('error', 'Invalid email/password. Please try again!');
        session_write_close();
        $this->redirect('login');
        exit;
    }

    private function getSchoolName($schoolId): string
    {
        $settings = new \NexaT\Services\SettingsService();
        return $settings->get('school.name', 'My School');
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->auth->logout();
        unset($_SESSION['school_id'], $_SESSION['school_name']);
        $this->view->flash('success', 'You have been logged out successfully!');
        session_write_close();
        $this->redirect('login');
        exit;
    }
}