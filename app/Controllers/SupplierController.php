<?php
/**
 * Supplier Controller
 * Handles supplier management (admin/super admin only)
 */
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Supplier;

class SupplierController extends Controller {
    private $supplierModel;

    public function __construct() {
        parent::__construct();
        $this->supplierModel = new Supplier();
    }

    /**
     * List all suppliers
     */
    public function index() {
        require_role(['admin', 'super_admin']);

        $page = (int)($this->get('page', '1'));
        $search = $this->get('q', '');
        $limit = 20;
        $offset = ($page - 1) * $limit;

        if (!empty($search)) {
            $search = sanitize_string($search, 200);
            $suppliers = $this->supplierModel->search($search);
            $total = count($suppliers);
        } else {
            $suppliers = $this->supplierModel->all($limit, $offset);
            $total = $this->supplierModel->count();
        }

        $total_pages = ceil($total / $limit);

        $this->view('suppliers/index', [
            'suppliers' => $suppliers,
            'total' => $total,
            'page' => $page,
            'total_pages' => $total_pages,
            'search' => $search,
            'user' => current_user()
        ]);
    }

    /**
     * Show create supplier form
     */
    public function create() {
        require_role(['admin', 'super_admin']);

        $this->view('suppliers/create', [
            'user' => current_user()
        ]);
    }

    /**
     * Store new supplier
     */
    public function store() {
        require_role(['admin', 'super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/suppliers/create');
        }

        header('Content-Type: application/json');

        try {
            $supplier_name = trim($this->post('supplier_name', ''));

            if (empty($supplier_name)) {
                $this->json(['success' => false, 'message' => 'Supplier name is required'], 400);
            }

            $result = $this->supplierModel->create([
                'supplier_name' => $supplier_name,
                'contact_person' => $this->post('contact_person', ''),
                'contact_email' => $this->post('contact_email', ''),
                'contact_phone' => $this->post('contact_phone', ''),
                'address' => $this->post('address', '')
            ]);

            if ($result['affected'] > 0) {
                $this->json(['success' => true, 'message' => 'Supplier created successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to create supplier'], 500);
            }

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show edit supplier form
     */
    public function edit($id) {
        require_role(['admin', 'super_admin']);
        $id = sanitize_int($id);

        $supplier = $this->supplierModel->findById($id);

        if (!$supplier) {
            http_response_code(404);
            echo "Supplier not found";
            exit;
        }

        $this->view('suppliers/edit', [
            'supplier' => $supplier,
            'user' => current_user()
        ]);
    }

    /**
     * Update supplier
     */
    public function update($id) {
        require_role(['admin', 'super_admin']);
        $id = sanitize_int($id);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/suppliers/' . $id . '/edit');
        }

        header('Content-Type: application/json');

        try {
            $supplier_name = trim($this->post('supplier_name', ''));

            if (empty($supplier_name)) {
                $this->json(['success' => false, 'message' => 'Supplier name is required'], 400);
            }

            $this->supplierModel->update($id, [
                'supplier_name' => $supplier_name,
                'contact_person' => $this->post('contact_person', ''),
                'contact_email' => $this->post('contact_email', ''),
                'contact_phone' => $this->post('contact_phone', ''),
                'address' => $this->post('address', '')
            ]);

            $this->json(['success' => true, 'message' => 'Supplier updated successfully']);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete supplier
     */
    public function delete($id) {
        require_role(['admin', 'super_admin']);
        $id = sanitize_int($id);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/suppliers/' . $id . '/edit');
        }

        header('Content-Type: application/json');

        try {
            $this->supplierModel->delete($id);
            $this->json(['success' => true, 'message' => 'Supplier deleted successfully']);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
