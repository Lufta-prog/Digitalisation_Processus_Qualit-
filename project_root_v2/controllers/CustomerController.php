<?php
require_once __DIR__ . '/../models/CustomerModel.php';

class CustomerController {
    private $model;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new CustomerModel($db);
    }

    /**
     * List all customers with pagination
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 10;
            
            if (!in_array($perPage, [10, 25, 50, 100])) {
                $perPage = 10;
            }
            
            $result = $this->model->getPaginatedCustomers($page, $perPage);
            
            $data = [
                'customers' => $result['data'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'perPage' => $result['perPage'],
                    'totalPages' => $result['totalPages']
                ]
            ];
            
            extract($data);
            include_once 'views/customers/list.php';
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header('Location: index.php?controller=dashboard');
            exit;
        }
    }

    /**
     * Show create customer form
     */
    public function create() {
        try {
            $industries = $this->model->getAllIndustries();
            $data = ['industries' => $industries];
            extract($data);
            include_once 'views/customers/create.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header('Location: index.php?controller=customers');
            exit;
        }
    }

    /**
     * Store new customer
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=customers&action=create");
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $industryId = isset($_POST['industry_id']) ? (int)$_POST['industry_id'] : null;

        // Validation
        if (empty($name)) {
            $_SESSION['error'] = "Le nom du client est obligatoire";
            header("Location: index.php?controller=customers&action=create");
            exit;
        }

        try {
            $this->db->beginTransaction();

            // Check for duplicate name
            if ($this->model->customerNameExists($name)) {
                $_SESSION['error'] = "Un client avec ce nom existe déjà";
                header("Location: index.php?controller=customers&action=create");
                exit;
            }

            // Create customer
            $this->model->createCustomer($name, $industryId);
            
            $this->db->commit();
            $_SESSION['success'] = "Client créé avec succès";
            header("Location: index.php?controller=customers");
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la création : " . $e->getMessage();
            header("Location: index.php?controller=customers&action=create");
            exit;
        }
    }

    /**
     * Show edit customer form
     */
    public function edit(int $id) {
        try {
            $customer = $this->model->getCustomerById($id);
            if (!$customer) {
                $_SESSION['error'] = "Client non trouvé";
                header("Location: index.php?controller=customers");
                exit;
            }
            
            $industries = $this->model->getAllIndustries();
            
            $data = [
                'customer' => $customer,
                'industries' => $industries
            ];
            extract($data);
            include_once 'views/customers/edit.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header("Location: index.php?controller=customers");
            exit;
        }
    }

    /**
     * Update customer
     */
    public function update(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=customers&action=edit&id=$id");
            exit;
        }

        $customer = $this->model->getCustomerById($id);
        if (!$customer) {
            $_SESSION['error'] = "Client non trouvé";
            header("Location: index.php?controller=customers");
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $industryId = isset($_POST['industry_id']) ? (int)$_POST['industry_id'] : null;

        // Validation
        if (empty($name)) {
            $_SESSION['error'] = "Le nom du client est obligatoire";
            header("Location: index.php?controller=customers&action=edit&id=$id");
            exit;
        }

        try {
            $this->db->beginTransaction();

            // Check for duplicate name (excluding current customer)
            if ($this->model->customerNameExists($name, $id)) {
                $_SESSION['error'] = "Un client avec ce nom existe déjà";
                header("Location: index.php?controller=customers&action=edit&id=$id");
                exit;
            }

            // Update customer
            $this->model->updateCustomer($id, $name, $industryId);
            
            $this->db->commit();
            $_SESSION['success'] = "Client mis à jour avec succès";
            header("Location: index.php?controller=customers");
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
            header("Location: index.php?controller=customers&action=edit&id=$id");
            exit;
        }
    }

    /**
     * Delete customer
     */
    public function delete(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Méthode non autorisée";
            header("Location: index.php?controller=customers");
            exit;
        }

        $customer = $this->model->getCustomerById($id);
        if (!$customer) {
            $_SESSION['error'] = "Client non trouvé";
            header("Location: index.php?controller=customers");
            exit;
        }

        try {
            $this->db->beginTransaction();
            $result = $this->model->deleteCustomer($id);
            
            if ($result) {
                $this->db->commit();
                $_SESSION['success'] = "Client supprimé avec succès";
            } else {
                $this->db->rollBack();
                $_SESSION['error'] = "Échec de la suppression du client";
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
        }

        header("Location: index.php?controller=customers");
        exit;
    }

    /**
     * View customer details
     */
    public function view(int $id) {
        try {
            $customer = $this->model->getCustomerById($id);
            if (!$customer) {
                $_SESSION['error'] = "Client non trouvé";
                header("Location: index.php?controller=customers");
                exit;
            }
            
            $data = ['customer' => $customer];
            extract($data);
            include_once 'views/customers/view.php';
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            header("Location: index.php?controller=customers");
            exit;
        }
    }
}
?>