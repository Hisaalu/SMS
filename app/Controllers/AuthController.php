<?php
// File: /app/Controllers/AuthController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Core\SettingsService;
use NexaT\Core\Toast;

class AuthController extends Controller
{
    public function login(): void
    {
        if ($this->auth->check()) {
            $this->redirect('dashboard');
            return;
        }

        $email = $_SESSION['old_email'] ?? '';
        unset($_SESSION['old_email']);
        $error   = null;
        $success = null;
        $warning = null;
        $info    = null;

        foreach (Toast::pull() as $t) {
            $type = $t['type']    ?? 'info';
            $msg  = $t['message'] ?? '';
            if ($msg === '') {
                continue;
            }
            if ($type === 'error'   && $error   === null) $error   = $msg;
            elseif ($type === 'success' && $success === null) $success = $msg;
            elseif ($type === 'warning' && $warning === null) $warning = $msg;
            elseif ($type === 'info'    && $info    === null) $info    = $msg;
        }

        if ($reason = \NexaT\Core\Auth::pullLogoutReason()) {
            if ($reason === 'timeout') {
                $error = $error ?: 'Your session expired due to inactivity. Please sign in again.';
            }
        }

        echo $this->view->render('auth/login', [
            'email'   => $email,
            'error'   => $error,
            'success' => $success,
            'warning' => $warning,
            'info'    => $info,
        ]);
    }

    public function authenticate(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($this->auth->check()) {
            $this->redirect('dashboard');
            return;
        }

        $identifier = trim($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';

        $_SESSION['old_email'] = $identifier;

        if ($identifier === '' || $password === '') {
            Toast::error('Please enter your email or username and your password.');
            $this->redirect('login');
            return;
        }

        if (!$this->auth->attempt($identifier, $password)) {
            $reason = method_exists($this->auth, 'lastError') ? $this->auth->lastError() : null;
            Toast::error($reason ?: 'Incorrect email/username or password. Please try again.');
            $this->redirect('login');
            return;
        }

        $user = $this->auth->getUser();

        if (!$user) {
            Toast::error('Unable to complete sign-in. Please try again.');
            $this->redirect('login');
            return;
        }

        $status = strtolower((string)($user->status ?? 'active'));
        if ($status !== 'active') {
            $this->auth->logout();
            Toast::error('Your account is not active. Please contact your administrator.');
            $this->redirect('login');
            return;
        }

        $_SESSION['school_id']   = $user->school_id ?? 1;
        $_SESSION['school_name'] = $this->getSchoolName();

        unset($_SESSION['old_email']);
        $_SESSION['_just_logged_in'] = true;
        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        Toast::success('You have been logged out successfully!');

        $this->auth->logout();

        unset($_SESSION['school_id'], $_SESSION['school_name']);

        $this->redirect('login');
    }

    private function getSchoolName(): string
    {
        $settings = new SettingsService();
        return $settings->get('school.name', 'My School');
    }
}