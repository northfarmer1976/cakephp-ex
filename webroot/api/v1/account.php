<?php
/**
 * Banking RESTful API for OpenShift 4.16
 * Host: mariadb
 * Content-Type: application/json
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

$host = 'mariadb';
$user = 'root';
$pass = 'P@ssw0rd';
$db   = 'sampledb';

// 1. Establish connection
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Get Request Method
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        handleGet($conn);
        break;
    case 'POST':
        handlePost($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed"]);
        break;
}

/**
 * Handle GET Request: Account Inquiry
 * Usage: api.php?account=ACC-100001
 */
function handleGet($conn) {
    if (!isset($_GET['account'])) {
        echo json_encode(["status" => "error", "message" => "Account number is required"]);
        return;
    }

    $accNo = $_GET['account'];

    // Fetch Account & Personal Info
    $sql = "SELECT a.account_number, p.full_name, p.email, a.balance, b.branch_name 
            FROM client_account a
            JOIN personal_info p ON a.account_number = p.account_number
            JOIN bank_branches b ON a.branch_id = b.id
            WHERE a.account_number = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $accNo);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();

    if (!$userData) {
        echo json_encode(["status" => "error", "message" => "Account not found"]);
        return;
    }

    // Fetch Transaction History
    $txSql = "SELECT transaction_type, amount, tx_date FROM transactions WHERE account_number = ? ORDER BY tx_date DESC LIMIT 5";
    $txStmt = $conn->prepare($txSql);
    $txStmt->bind_param("s", $accNo);
    $txStmt->execute();
    $transactions = $txStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "status" => "success",
        "account_info" => $userData,
        "recent_transactions" => $transactions
    ]);
}

/**
 * Handle POST Request: Create Transaction
 * Payload: {"account_number": "ACC-100001", "type": "Deposit", "amount": 500}
 */
function handlePost($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['account_number'], $data['type'], $data['amount'])) {
        echo json_encode(["status" => "error", "message" => "Invalid payload"]);
        return;
    }

    $accNo  = $data['account_number'];
    $type   = $data['type']; // Deposit, Withdrawal, Transfer
    $amount = (float)$data['amount'];

    // Start Transaction
    $conn->begin_transaction();

    try {
        // 1. Insert Transaction Record
        $stmt = $conn->prepare("INSERT INTO transactions (account_number, transaction_type, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $accNo, $type, $amount);
        $stmt->execute();

        // 2. Update Balance
        $op = ($type === 'Deposit') ? "+" : "-";
        $updateSql = "UPDATE client_account SET balance = balance $op ? WHERE account_number = ?";
        $upStmt = $conn->prepare($updateSql);
        $upStmt->bind_param("ds", $amount, $accNo);
        $upStmt->execute();

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Transaction completed"]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
    }
}

$conn->close();
?>
