<?php
/**
 * Database Configuration for OpenShift 4.16
 * Host: 'mariadb' (Internal Service Name)
 */
$host = 'mariadb';
$user = 'root';
$pass = 'P@ssw0rd';
$db   = 'sampledb';

// 1. Establish MySQLi connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("<div class='error'>Connection Failed: " . $conn->connect_error . "</div>");
}

$conn->query("CREATE DATABASE IF NOT EXISTS $db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db);

// 6. Query Joined Data
$sql = "SELECT a.account_number, p.full_name, a.balance, b.branch_name, 
               (SELECT COUNT(*) FROM transactions t WHERE t.account_number = a.account_number) as tx_count
        FROM client_account a
        JOIN personal_info p ON a.account_number = p.account_number
        JOIN bank_branches b ON a.branch_id = b.id
        ORDER BY a.id DESC 
		LIMIT 20";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bank Core System</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.05); }
        h2 { color: #d32f2f; margin-bottom: 5px; }
        p.subtitle { color: #666; margin-bottom: 25px; font-size: 0.9em; border-left: 3px solid #ccc; padding-left: 10px; }
        .badge-reset { background: #fff3cd; color: #856404; padding: 5px 12px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #343a40; color: white; padding: 12px; text-align: left; text-transform: uppercase; font-size: 13px; }
        td { padding: 12px; border-bottom: 1px solid #dee2e6; font-size: 14px; }
        tr:hover { background-color: #f1f3f5; }
        .acc-code { font-family: 'Courier New', monospace; font-weight: bold; color: #007bff; }
        .money { color: #2e7d32; font-weight: bold; }
        .tx-pill { background: #e9ecef; color: #495057; padding: 2px 8px; border-radius: 20px; font-size: 12px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Bank Core System</h2>
    <p class="subtitle">Environment: <strong>CRC 4.16</strong> | Host: <strong>mariadb</strong></p>
    <div style="margin:20px 0; display:flex; gap:10px;">
    <input 
        type="text" 
        id="searchAccount" 
        placeholder="Enter Account ID (e.g. ACC-100000)" 
        style="flex:1; padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px;"
    >
    <button 
        onclick="window.open('transactions.php?acc='+document.getElementById('searchAccount').value, '_blank')"
        style="padding:8px 16px; background:#007bff; color:white; border:none; border-radius:6px; cursor:pointer;">
        Search Transactions
    </button>
</div>
    <table>
        <thead>
            <tr>
                <th>Account No.</th>
                <th>Customer Name</th>
                <th>Branch Location</th>
                <th>Balance (USD)</th>
                <th>Tx History</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="acc-code"><?php echo $row['account_number']; ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo $row['branch_name']; ?></td>
                        <td class="money">$<?php echo number_format($row['balance'], 2); ?></td>
                        <td><a class="tx-pill" href="transactions.php?acc=<?php echo $row['account_number']; ?>" target="_blank"><?php echo $row['tx_count']; ?> Total</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php $conn->close(); ?>
