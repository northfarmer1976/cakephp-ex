<?php
/**
 * Banking RESTful API - Enhanced Transfer Edition
 * Supports both POST (JSON) and GET (URL Params) for transactions.
 * Host: mariadb | Context: OpenShift 4.16
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");

$host = 'mariadb';
$user = 'root';
$pass = 'P@ssw0rd';
$db   = 'sampledb';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB Connection Failed"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$data = [];

// 1. Data Extraction based on Method
if ($method === 'POST') {
    // Extract from JSON body
    $data = json_decode(file_get_contents("php://input"), true);
} elseif ($method === 'GET') {
    // Extract from URL query string
    $data = $_GET;
}

// 2. Route to Transfer Logic if parameters exist
if (isset($data['from_account'], $data['to_account'], $data['amount'])) {
    handleTransfer($conn, $data);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid payload structure. Required: from_account, to_account, amount",
        "method_used" => $method
    ]);
}

/**
 * Core Transfer Logic with Atomic Transactions
 */
function handleTransfer($conn, $data) {
    $from = $data['from_account'];
    $to   = $data['to_account'];
    $amount = (float)$data['amount'];

    // Validation: Positive amount
    if ($amount <= 0) {
        echo json_encode(["status" => "error", "message" => "Amount must be greater than zero"]);
        return;
    }

    // Validation: Self-transfer
    if ($from === $to) {
        echo json_encode(["status" => "error", "message" => "Source and destination accounts must be different"]);
        return;
    }

    // Start MySQL Transaction
    $conn->begin_transaction();

    try {
        // Step 1: Check & Lock Source Account (FOR UPDATE)
        $stmtFrom = $conn->prepare("SELECT balance FROM client_account WHERE account_number = ? FOR UPDATE");
        $stmtFrom->bind_param("s", $from);
        $stmtFrom->execute();
        $resFrom = $stmtFrom->get_result()->fetch_assoc();

        if (!$resFrom) {
            throw new Exception("Source account ($from) does not exist");
        }

        if ($resFrom['balance'] < $amount) {
            throw new Exception("Insufficient funds. Available: " . $resFrom['balance']);
        }

        // Step 2: Check & Lock Destination Account
        $stmtTo = $conn->prepare("SELECT id FROM client_account WHERE account_number = ? FOR UPDATE");
        $stmtTo->bind_param("s", $to);
        $stmtTo->execute();
        if ($stmtTo->get_result()->num_rows === 0) {
            throw new Exception("Destination account ($to) does not exist");
        }

        // Step 3: Perform Balance Updates
        $upFrom = $conn->prepare("UPDATE client_account SET balance = balance - ? WHERE account_number = ?");
        $upFrom->bind_param("ds", $amount, $from);
        $upFrom->execute();

        $upTo = $conn->prepare("UPDATE client_account SET balance = balance + ? WHERE account_number = ?");
        $upTo->bind_param("ds", $amount, $to);
        $upTo->execute();

        // Step 4: Log Transactions for Audit Trail
        $logSql = "INSERT INTO transactions (account_number, transaction_type, amount) VALUES (?, ?, ?)";
        $logStmt = $conn->prepare($logSql);

        $typeOut = 'Withdrawal';
        $logStmt->bind_param("ssd", $from, $typeOut, $amount);
        $logStmt->execute();

        $typeIn = 'Deposite';
        $logStmt->bind_param("ssd", $to, $typeIn, $amount);
        $logStmt->execute();

        // Finalize: Commit
        $conn->commit();
        echo json_encode([
            "status" => "success",
            "message" => "Transfer completed successfully",
            "request_method" => $_SERVER['REQUEST_METHOD'],
            "details" => [
                "from" => $from,
                "to" => $to,
                "amount" => $amount
            ]
        ]);

    } catch (Exception $e) {
        // Error: Rollback
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

$conn->close();
?>
