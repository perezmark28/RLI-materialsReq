<?php
/**
 * Home Controller
 * Handles home page and dashboard
 */
namespace App\Controllers;

use App\Core\Controller;
use App\Models\MaterialRequest;
use App\Models\Supervisor;
use App\Models\User;

class HomeController extends Controller {
    /**
     * Show landing page
     */
    public function index() {
        if (is_logged_in()) {
            $this->redirect('/home');
        }
        $this->view('home/index');
    }

    /**
     * Show home/dashboard page
     */
    public function home() {
        require_login();

        $requestModel = new MaterialRequest();
        $userModel = new User();

        $user = current_user();
        $role = current_role();

        // Get statistics
        if ($role === 'super_admin') {
            $stats = $requestModel->getStats();
        } else {
            $stats = [
                'total' => $requestModel->count(['user_id' => $user['id']]),
                'pending' => $requestModel->count(['user_id' => $user['id'], 'status' => 'pending']),
                'approved' => $requestModel->count(['user_id' => $user['id'], 'status' => 'approved']),
                'declined' => $requestModel->count(['user_id' => $user['id'], 'status' => 'declined'])
            ];
        }

        $this->view('home/dashboard', [
            'user' => $user,
            'role' => $role,
            'stats' => $stats
        ]);
    }

    /**
     * Show dashboard page (admin/super_admin: stats + quick actions)
     */
    public function dashboard() {
        require_role(['admin', 'super_admin']);

        $requestModel = new MaterialRequest();
        $supervisorModel = new Supervisor();
        $user = current_user();
        $role = current_role();

        $supervisor_id = null;
        if ($role === 'super_admin') {
            $stats = $requestModel->getStats();
        } else {
            $supervisor = $supervisorModel->findByInitials($user['username']);
            if ($supervisor) {
                $supervisor_id = $supervisor['id'];
                $stats = [
                    'total' => $requestModel->count(['supervisor_id' => $supervisor_id]),
                    'pending' => $requestModel->count(['supervisor_id' => $supervisor_id, 'status' => 'pending']),
                    'approved' => $requestModel->count(['supervisor_id' => $supervisor_id, 'status' => 'approved']),
                    'declined' => $requestModel->count(['supervisor_id' => $supervisor_id, 'status' => 'declined'])
                ];
            } else {
                $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'declined' => 0];
                $supervisor_id = 0; // No supervisor = no data in charts
            }
        }

        $chartMonthly = $requestModel->getRequestsPerMonth(6, $supervisor_id);

        $base = defined('BASE_PATH') ? BASE_PATH : '';
        $this->view('home/dashboard_stats', [
            'user' => $user,
            'role' => $role,
            'stats' => $stats,
            'chartMonthly' => $chartMonthly,
            'base' => $base
        ]);
    }

    /**
     * Show profile page
     */
    public function profile() {
        require_login();

        $user = current_user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            try {
                $current_password = $this->post('current_password', '');
                $new_password = $this->post('new_password', '');
                $confirm_password = $this->post('confirm_password', '');

                if ($current_password === '' || $new_password === '' || $confirm_password === '') {
                    $this->json(['success' => false, 'message' => 'All password fields are required.'], 400);
                }
                if ($new_password !== $confirm_password) {
                    $this->json(['success' => false, 'message' => 'New passwords do not match.'], 400);
                }
                if (strlen($new_password) < 6) {
                    $this->json(['success' => false, 'message' => 'Password must be at least 6 characters.'], 400);
                }

                $userModel = new User();
                $hash = $userModel->getPasswordHashById($user['id']);
                if (!$hash || !password_verify($current_password, $hash)) {
                    $this->json(['success' => false, 'message' => 'Current password is incorrect.'], 400);
                }

                $userModel->updatePassword($user['id'], $new_password);
                $this->json(['success' => true, 'message' => 'Password updated successfully.']);
            } catch (\Exception $e) {
                $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
        }

        $this->view('home/profile', ['user' => $user]);
    }

    /**
     * Show statistics page
     */
    public function statistics() {
        require_role(['admin', 'super_admin']);

        $requestModel = new MaterialRequest();
        $stats = $requestModel->getStats();

        $this->view('home/statistics', [
            'stats' => $stats,
            'user' => current_user()
        ]);
    }
}
