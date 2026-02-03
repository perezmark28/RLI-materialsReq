<?php
/**
 * Auth Controller
 * Handles authentication and user session management
 */
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Show login page (split-screen auth)
     */
    public function loginPage() {
        if (is_logged_in()) {
            $this->redirect('/home');
        }
        $base = defined('BASE_PATH') ? BASE_PATH : '';
        $this->view('auth/split', [
            'base' => $base,
            'initialForm' => 'login',
            'error' => '',
            'username' => '',
            'full_name' => '',
            'email' => '',
        ]);
    }

    /**
     * Handle login submission
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }

        $username = trim($this->post('username', ''));
        $password = $this->post('password', '');

        $error = '';

        if ($username === '' || $password === '') {
            $error = 'Username and password are required.';
        } else {
            $user = $this->userModel->findByUsername($username);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'Invalid credentials.';
            } else {
                login_user([
                    'id' => (int)$user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => $user['role_name'],
                ]);
                $this->redirect('/home');
            }
        }

        $base = defined('BASE_PATH') ? BASE_PATH : '';
        $this->view('auth/split', [
            'base' => $base,
            'initialForm' => 'login',
            'error' => $error,
            'username' => $username,
            'full_name' => '',
            'email' => '',
        ]);
    }

    /**
     * Show signup page (split-screen auth)
     */
    public function signupPage() {
        if (is_logged_in()) {
            $this->redirect('/home');
        }
        $base = defined('BASE_PATH') ? BASE_PATH : '';
        $this->view('auth/split', [
            'base' => $base,
            'initialForm' => 'signup',
            'error' => '',
            'username' => '',
            'full_name' => '',
            'email' => '',
        ]);
    }

    /**
     * Handle signup submission
     */
    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/signup');
        }

        $username = trim($this->post('username', ''));
        $password = $this->post('password', '');
        $confirm_password = $this->post('confirm_password', '');
        $full_name = trim($this->post('full_name', ''));
        $email = trim($this->post('email', ''));

        $error = '';

        if ($username === '' || $password === '' || $full_name === '' || $email === '') {
            $error = 'All fields are required.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif ($this->userModel->usernameExists($username)) {
            $error = 'Username already exists.';
        } else {
            try {
                $role_id = $this->userModel->getRoleIdByName('viewer');
                $result = $this->userModel->create($username, $password, $full_name, $email, $role_id);
                
                if ($result['affected'] > 0) {
                    $this->view('auth/signup_success');
                    return;
                } else {
                    $error = 'Failed to create account.';
                }
            } catch (\Exception $e) {
                $error = 'Error creating account: ' . $e->getMessage();
            }
        }

        $base = defined('BASE_PATH') ? BASE_PATH : '';
        $this->view('auth/split', [
            'base' => $base,
            'initialForm' => 'signup',
            'error' => $error,
            'username' => $username,
            'full_name' => $full_name,
            'email' => $email,
        ]);
    }

    /**
     * Handle logout
     */
    public function logout() {
        logout_user();
        $this->redirect('/');
    }
}
