<?php
/**
 * User Controller
 * Handles user management (super admin only)
 */
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * List all users
     */
    public function index() {
        require_role(['super_admin']);

        $page = (int)($this->get('page', '1'));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $users = $this->userModel->all($limit, $offset);
        $total = $this->userModel->count();
        $total_pages = ceil($total / $limit);

        $this->view('users/index', [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'total_pages' => $total_pages,
            'user' => current_user()
        ]);
    }

    /**
     * Show create user form
     */
    public function create() {
        require_role(['super_admin']);

        $this->view('users/create', [
            'user' => current_user()
        ]);
    }

    /**
     * Store new user
     */
    public function store() {
        require_role(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users/create');
        }

        header('Content-Type: application/json');

        try {
            $username = trim($this->post('username', ''));
            $password = $this->post('password', '');
            $confirm_password = $this->post('confirm_password', '');
            $full_name = trim($this->post('full_name', ''));
            $email = trim($this->post('email', ''));
            $role = $this->post('role', 'viewer');

            if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
                $this->json(['success' => false, 'message' => 'All fields are required'], 400);
            }

            if ($password !== $confirm_password) {
                $this->json(['success' => false, 'message' => 'Passwords do not match'], 400);
            }

            if ($this->userModel->usernameExists($username)) {
                $this->json(['success' => false, 'message' => 'Username already exists'], 400);
            }

            $role_id = $this->userModel->getRoleIdByName($role);
            if (!$role_id) {
                $this->json(['success' => false, 'message' => 'Invalid role'], 400);
            }

            $result = $this->userModel->create($username, $password, $full_name, $email, $role_id);

            if ($result['affected'] > 0) {
                $this->json(['success' => true, 'message' => 'User created successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to create user'], 500);
            }

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show edit user form
     */
    public function edit($id) {
        require_role(['super_admin']);

        $user_data = $this->userModel->findById($id);

        if (!$user_data) {
            http_response_code(404);
            echo "User not found";
            exit;
        }

        $this->view('users/edit', [
            'user_data' => $user_data,
            'user' => current_user()
        ]);
    }

    /**
     * Update user
     */
    public function update($id) {
        require_role(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users/' . $id . '/edit');
        }

        header('Content-Type: application/json');

        try {
            $full_name = trim($this->post('full_name', ''));
            $email = trim($this->post('email', ''));
            $status = $this->post('status', 'active');

            if (empty($full_name) || empty($email)) {
                $this->json(['success' => false, 'message' => 'All fields are required'], 400);
            }

            $this->userModel->update($id, [
                'full_name' => $full_name,
                'email' => $email,
                'status' => $status
            ]);

            $this->json(['success' => true, 'message' => 'User updated successfully']);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete user
     */
    public function delete($id) {
        require_role(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users/' . $id . '/edit');
        }

        header('Content-Type: application/json');

        try {
            // Prevent deleting self
            $user = current_user();
            if ($user['id'] == $id) {
                $this->json(['success' => false, 'message' => 'Cannot delete your own account'], 400);
            }

            $this->userModel->delete($id);
            $this->json(['success' => true, 'message' => 'User deleted successfully']);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
