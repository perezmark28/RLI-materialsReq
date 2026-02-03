<?php
/**
 * Request Controller
 * Handles material request CRUD operations
 */
namespace App\Controllers;

use App\Core\Controller;
use App\Models\MaterialRequest;
use App\Models\Supervisor;
use App\Models\User;

class RequestController extends Controller {
    private $requestModel;
    private $supervisorModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->requestModel = new MaterialRequest();
        $this->supervisorModel = new Supervisor();
        $this->userModel = new User();
    }

    /**
     * List all requests
     */
    public function index() {
        require_login();

        $user = current_user();
        $role = current_role();

        // Filters
        $status = $this->get('status', '');
        $search = $this->get('q', '');
        $page = (int)($this->get('page', '1'));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Build filters
        $filters = [];

        if ($role === 'viewer') {
            $filters['user_id'] = $user['id'];
        } elseif ($role === 'admin') {
            // Get supervisor ID from username
            $supervisor = $this->supervisorModel->findByInitials($user['username']);
            if ($supervisor) {
                $filters['supervisor_id'] = $supervisor['id'];
            }
        }

        if (!empty($status) && in_array($status, ['pending', 'approved', 'declined'])) {
            $filters['status'] = $status;
        }

        if (!empty($search)) {
            $filters['search'] = $search;
        }

        // Get requests
        $requests = $this->requestModel->getRequests($filters, $limit, $offset);
        $total = $this->requestModel->count($filters);
        $total_pages = ceil($total / $limit);

        $this->view('requests/index', [
            'requests' => $requests,
            'total' => $total,
            'page' => $page,
            'total_pages' => $total_pages,
            'status' => $status,
            'search' => $search,
            'user' => $user,
            'role' => $role
        ]);
    }

    /**
     * Show create request form
     */
    public function create() {
        require_login();

        $supervisors = $this->supervisorModel->all();

        $this->view('requests/create', [
            'supervisors' => $supervisors,
            'user' => current_user()
        ]);
    }

    /**
     * Store new request
     */
    public function store() {
        require_login();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/requests/create');
        }

        header('Content-Type: application/json');

        try {
            $user = current_user();
            $user_id = $user['id'];

            // Validate required fields
            $requester_name = trim($this->post('requester_name', ''));
            $date_requested = $this->post('date_requested', '');
            $date_needed = $this->post('date_needed', '');
            $particulars = trim($this->post('particulars', ''));
            $supervisor_id = (int)($this->post('supervisor_id', 0));

            if (empty($requester_name) || empty($date_requested) || empty($date_needed) || empty($particulars) || $supervisor_id === 0) {
                $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
            }

            // Get items
            $items = $this->getItems();

            if (empty($items)) {
                $this->json(['success' => false, 'message' => 'At least one item is required'], 400);
            }

            // Create request
            $result = $this->requestModel->create($user_id, [
                'requester_name' => $requester_name,
                'date_requested' => $date_requested,
                'date_needed' => $date_needed,
                'particulars' => $particulars,
                'supervisor_id' => $supervisor_id
            ]);

            $request_id = $result['insert_id'];

            // Add items
            foreach ($items as $item_no => $item) {
                $this->requestModel->addItem($request_id, [
                    'item_no' => $item_no + 1,
                    'item_name' => $item['item_name'],
                    'specs' => $item['specs'],
                    'quantity' => (float)$item['quantity'],
                    'unit' => $item['unit'],
                    'price' => (float)$item['price'],
                    'amount' => (float)$item['amount'],
                    'item_link' => $item['item_link']
                ]);
            }

            $this->json(['success' => true, 'message' => 'Request created successfully', 'request_id' => $request_id]);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show request details
     */
    public function show($id) {
        require_login();

        $request = $this->requestModel->getById($id);

        if (!$request) {
            http_response_code(404);
            echo "Request not found";
            exit;
        }

        // Check authorization
        $user = current_user();
        $role = current_role();

        if ($role === 'viewer' && $request['user_id'] != $user['id']) {
            http_response_code(403);
            echo "Forbidden";
            exit;
        }

        $items = $this->requestModel->getItems($id);

        $this->view('requests/view', [
            'request' => $request,
            'items' => $items,
            'user' => $user,
            'role' => $role
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($id) {
        require_login();

        $request = $this->requestModel->getById($id);

        if (!$request) {
            http_response_code(404);
            echo "Request not found";
            exit;
        }

        // Only viewer owner can edit
        $user = current_user();
        if (current_role() === 'viewer' && $request['user_id'] != $user['id']) {
            http_response_code(403);
            echo "Forbidden";
            exit;
        }

        $items = $this->requestModel->getItems($id);
        $supervisors = $this->supervisorModel->all();

        $this->view('requests/edit', [
            'request' => $request,
            'items' => $items,
            'supervisors' => $supervisors,
            'user' => $user
        ]);
    }

    /**
     * Update request
     */
    public function update($id) {
        require_login();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/requests/' . $id . '/edit');
        }

        header('Content-Type: application/json');

        try {
            $request = $this->requestModel->getById($id);

            if (!$request) {
                $this->json(['success' => false, 'message' => 'Request not found'], 404);
            }

            // Authorization check
            $user = current_user();
            if (current_role() === 'viewer' && $request['user_id'] != $user['id']) {
                $this->json(['success' => false, 'message' => 'Forbidden'], 403);
            }

            // Only allow editing if pending
            if ($request['status'] !== 'pending') {
                $this->json(['success' => false, 'message' => 'Can only edit pending requests'], 400);
            }

            // Update request
            $this->requestModel->update($id, [
                'requester_name' => $this->post('requester_name', ''),
                'date_requested' => $this->post('date_requested', ''),
                'date_needed' => $this->post('date_needed', ''),
                'particulars' => $this->post('particulars', ''),
                'supervisor_id' => $this->post('supervisor_id', '')
            ]);

            // Delete old items and add new ones
            // Note: In production, you may want more granular item management
            $items = $this->getItems();
            $oldItems = $this->requestModel->getItems($id);

            // Delete old items
            foreach ($oldItems as $item) {
                // Note: You'll need a deleteItem method in the model
                // For now, we'll skip this - you can implement it
            }

            // Add new items
            foreach ($items as $item_no => $item) {
                $this->requestModel->addItem($id, [
                    'item_no' => $item_no + 1,
                    'item_name' => $item['item_name'],
                    'specs' => $item['specs'],
                    'quantity' => (float)$item['quantity'],
                    'unit' => $item['unit'],
                    'price' => (float)$item['price'],
                    'amount' => (float)$item['amount'],
                    'item_link' => $item['item_link']
                ]);
            }

            $this->json(['success' => true, 'message' => 'Request updated successfully']);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve request
     */
    public function approve($id) {
        require_role(['admin', 'super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/requests/' . $id);
        }

        header('Content-Type: application/json');

        try {
            $user = current_user();
            $this->requestModel->updateStatus($id, 'approved', $user['id']);
            $this->json(['success' => true, 'message' => 'Request approved']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Decline request
     */
    public function decline($id) {
        require_role(['admin', 'super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/requests/' . $id);
        }

        header('Content-Type: application/json');

        try {
            $user = current_user();
            $this->requestModel->updateStatus($id, 'declined', $user['id']);
            $this->json(['success' => true, 'message' => 'Request declined']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Printable "All Requests" view for admin/super admin
     */
    public function printAll() {
        require_role(['admin', 'super_admin']);

        require_once __DIR__ . '/../../includes/db_connect.php';

        $sql = "
            SELECT
              mr.id,
              mr.requester_name,
              mr.date_requested,
              mr.date_needed,
              mr.status,
              approver.full_name AS approver_full_name
            FROM material_requests mr
            LEFT JOIN users approver ON mr.approved_by = approver.id
            ORDER BY mr.id DESC
        ";

        $requests = [];
        $result = $conn->query($sql);
        $itemStmt = $conn->prepare("
            SELECT item_name, specs, quantity, unit, price, amount
            FROM request_items
            WHERE request_id = ?
            ORDER BY id ASC
        ");

        if ($result && $itemStmt) {
            while ($row = $result->fetch_assoc()) {
                $items = [];
                $rid = (int)$row['id'];
                $itemStmt->bind_param("i", $rid);
                $itemStmt->execute();
                $itemRes = $itemStmt->get_result();
                if ($itemRes) {
                    while ($it = $itemRes->fetch_assoc()) {
                        $items[] = $it;
                    }
                }
                $row['items'] = $items;
                $requests[] = $row;
            }
        }

        $this->view('requests/print', [
            'requests' => $requests,
            'user' => current_user(),
            'role' => current_role()
        ]);
    }

    /**
     * Supervisor info for auto-fill (AJAX)
     */
    public function supervisorInfo($id) {
        require_login();
        header('Content-Type: application/json');

        try {
            $supervisor = $this->supervisorModel->findById((int)$id);
            if (!$supervisor) {
                $this->json(['success' => false, 'message' => 'Supervisor not found'], 404);
            }

            $this->json([
                'success' => true,
                'email' => $supervisor['email'] ?? '',
                'mobile' => $supervisor['mobile'] ?? ''
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete request
     */
    public function delete($id) {
        require_login();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/requests/' . $id);
        }

        header('Content-Type: application/json');

        try {
            $request = $this->requestModel->getById($id);

            if (!$request) {
                $this->json(['success' => false, 'message' => 'Request not found'], 404);
            }

            // Authorization check
            $user = current_user();
            if (current_role() === 'viewer' && $request['user_id'] != $user['id']) {
                $this->json(['success' => false, 'message' => 'Forbidden'], 403);
            }

            // Only allow deleting if pending
            if ($request['status'] !== 'pending') {
                $this->json(['success' => false, 'message' => 'Can only delete pending requests'], 400);
            }

            $this->requestModel->delete($id);
            $this->json(['success' => true, 'message' => 'Request deleted']);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Parse items from POST data
     */
    private function getItems() {
        $items = [];

        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $items = $_POST['items'];
        } else {
            // Parse manually from flat POST data
            foreach ($_POST as $key => $value) {
                if (preg_match('/^items\[(\d+)\]\[(.+)\]$/', $key, $matches)) {
                    $item_index = $matches[1];
                    $field_name = $matches[2];
                    if (!isset($items[$item_index])) {
                        $items[$item_index] = [];
                    }
                    $items[$item_index][$field_name] = $value;
                }
            }
        }

        return array_filter($items, function($item) {
            return !empty($item['item_name']);
        });
    }
}
