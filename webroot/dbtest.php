<?php
/**
 * Database Configuration
 * Note: When deploying on OpenShift, change '127.0.0.1' to your MariaDB Service Name 
 * if the PHP app and Database are in different Pods.
 */
$host = '127.0.0.1';
$user = 'root';
$pass = 'P@ssw0rd';
$db   = 'sampledb';

// 1. Establish MySQLi connection
$conn = new mysqli($host, $user, $pass);

// Check connection stability
if ($conn->connect_error) {
    die("<div class='error'>Connection Failed: " . $conn->connect_error . "</div>");
}

// 2. Initialize Database and Table
$conn->query("CREATE DATABASE IF NOT EXISTS $db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db);

$tableSql = "CREATE TABLE IF NOT EXISTS client_account (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_number VARCHAR(20) NOT NULL,
    customer_name VARCHAR(50) NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($tableSql);

// 3. Seed Data (Insert 20 random records if table is empty)
$check = $conn->query("SELECT COUNT(*) as total FROM client_account");
$rowCount = $check->fetch_assoc()['total'];

if ($rowCount == 0) {
    $firstNames = ['John', 'Jane', 'Michael', 'Emily', 'David', 'Sarah', 'Chris', 'Anna', 'Robert', 'Linda'];
    $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Taylor', 'Wilson'];
    
    $stmt = $conn->prepare("INSERT INTO client_account (account_number, customer_name, balance) VALUES (?, ?, ?)");
    
    for ($i = 0; $i < 20; $i++) {
        $accNo = "ACC-" . rand(100000, 999999);
        $name = $firstNames[array_rand($firstNames)] . " " . $lastNames[array_rand($lastNames)];
        $balance = rand(500, 100000) / 100 * rand(5, 50);
        
        $stmt->bind_param("ssd", $accNo, $name, $balance);
        $stmt->execute();
    }
    $stmt->close();
}

// 4. Fetch records for display
$result = $conn->query("SELECT * FROM client_account ORDER BY id DESC LIMIT 20");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenShift MariaDB Client Dashboard</title>
    <style>
        /* CSS Styling */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #eef2f7; margin: 0; padding: 40px; }
        .container { max-width: 1000px; margin: auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h2 { color: #1a3a5a; margin-top: 0; border-left: 5px solid #007bff; padding-left: 15px; }
        .alert { background-color: #d1e7dd; color: #0f5132; padding: 15px; border-radius: 6px; margin-bottom: 25px; border: 1px solid #badbcc; font-size: 0.95rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; overflow: hidden; border-radius: 8px; }
        thead { background-color: #007bff; color: #ffffff; }
        th, td { padding: 14px 20px; text-align: left; border-bottom: 1px solid #ebeef5; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background-color: #f8f9fa; transition: 0.3s; }
        .badge-acc { background: #f1f3f5; padding: 4px 8px; border-radius: 4px; font-family: monospace; color: #495057; }
        .balance-text { font-weight: 600; color: #28a745; }
        .error { background: #f8d7da; color: #842029; padding: 20px; border-radius: 8px; border: 1px solid #f5c2c7; }
    </style>
</head>
<body>

<div class="container">
    <h2>Client Account Management</h2>
    
    <div class="alert">
        <strong>Status:</strong> Connected to MariaDB. Database <em>sampledb</em> is active.
    </div>

    <table>
        <thead>
            <tr>
                <th># ID</th>
                <th>Account Number</th>
                <th>Customer Name</th>
                <th>Balance (USD)</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><span class="badge-acc"><?php echo $row['account_number']; ?></span></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td class="balance-text">$<?php echo number_format($row['balance'], 2); ?></td>
                        <td style="color: #6c757d; font-size: 0.85rem;"><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">No records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php 
// 5. Close connection
$conn->close(); 
?>
