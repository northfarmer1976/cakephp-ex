<?php
// 数据库配置（和主页面保持一致）
$host = 'mariadb';
$user = 'root';
$pass = 'P@ssw0rd';
$db   = 'sampledb';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) die("Database Connection Failed");

$conn->select_db($db);

// 获取账户号
$account = $_GET['acc'] ?? '';
if (empty($account)) die("Invalid Account");

// 查询账户信息
$info = $conn->query("
    SELECT a.*, p.full_name, b.branch_name, b.location
    FROM client_account a
    JOIN personal_info p ON a.account_number = p.account_number
    JOIN bank_branches b ON a.branch_id = b.id
    WHERE a.account_number = '$account'
")->fetch_assoc();

// 查询所有交易
$transactions = $conn->query("
    SELECT * FROM transactions
    WHERE account_number = '$account'
    ORDER BY tx_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Details | <?php echo $account; ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; padding: 30px; margin: 0; }
        .card { max-width: 900px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        h2 { color: #d32f2f; margin-top: 0; }
        .account-card { background: #f1f3f5; padding: 18px; border-radius: 10px; margin-bottom: 25px; }
        .account-card p { margin: 6px 0; font-size: 14px; }
        .label { font-weight: 600; width: 110px; display: inline-block; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #343a40; color: white; padding: 12px; text-align: left; font-size: 13px; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #dee2e6; font-size: 14px; }
        tr:hover { background: #f8f9fa; }
        .type-deposit { color: #2e7d32; font-weight: bold; }
        .type-withdrawal { color: #d32f2f; font-weight: bold; }
        .type-transfer { color: #ed6c02; font-weight: bold; }
        .amount { font-weight: 600; }
        .back {
            display: inline-block; margin-bottom: 20px; padding: 8px 16px;
            background: #007bff; color: white; border-radius: 6px;
            text-decoration: none;
        }
        .back:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="card">
    <h2>Transaction History</h2>

    <div class="account-card">
        <p><span class="label">Account:</span> <?php echo $info['account_number']; ?></p>
        <p><span class="label">Customer:</span> <?php echo $info['full_name']; ?></p>
        <p><span class="label">Branch:</span> <?php echo $info['branch_name'] . ', ' . $info['location']; ?></p>
        <p><span class="label">Balance:</span> $<?php echo number_format($info['balance'], 2); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tx ID</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while($tx = $transactions->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $tx['id']; ?></td>
                <td class="type-<?php echo strtolower($tx['transaction_type']); ?>">
                    <?php echo $tx['transaction_type']; ?>
                </td>
                <td class="amount">$<?php echo number_format($tx['amount'], 2); ?></td>
                <td><?php echo $tx['tx_date']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php $conn->close(); ?>
