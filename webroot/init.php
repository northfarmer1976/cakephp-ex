<?php
/**
 * Database Configuration for OpenShift 4.16
 * Host: 'mariadb' (Internal Service Name)
 */
$host = 'mariadb';
$user = 'root';
$pass = 'P@ssw0rd';
$db   = 'sampledb';

set_time_limit(0);
ini_set('memory_limit', '1G');

// 1. Establish MySQLi connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("<div class='error'>Connection Failed: " . $conn->connect_error . "</div>");
}

// 2. Initialize Database
$conn->query("CREATE DATABASE IF NOT EXISTS $db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db);

/**
 * 3. Cleanup: Drop existing tables in correct order (due to Foreign Key constraints)
 */
$conn->query("DROP TABLE IF EXISTS transactions");
$conn->query("DROP TABLE IF EXISTS personal_info");
$conn->query("DROP TABLE IF EXISTS client_account");
$conn->query("DROP TABLE IF EXISTS bank_branches");

/**
 * 4. Create Interconnected Tables 
 */

// Table: Branches
$conn->query("CREATE TABLE bank_branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_name VARCHAR(50) NOT NULL,
    location VARCHAR(100) NOT NULL
)");

// Table: Client Accounts (Main)
$conn->query("CREATE TABLE client_account (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_number VARCHAR(20) UNIQUE NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    branch_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES bank_branches(id)
)");

// Table: Personal Info
$conn->query("CREATE TABLE personal_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_number VARCHAR(20) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    FOREIGN KEY (account_number) REFERENCES client_account(account_number) ON DELETE CASCADE
)");

// Table: Transactions
$conn->query("CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_number VARCHAR(20) NOT NULL,
    transaction_type VARCHAR(50) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    tx_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_number) REFERENCES client_account(account_number) ON DELETE CASCADE
)");

/**
 * 5. 
 */
$branches = [
    ['Main Downtown', 'New York, USA'],
    ['Silicon Valley', 'Palo Alto, USA'],
    ['Financial District', 'London, UK'],
    ['Pacific Hub', 'Tokyo, Japan'],
    ['Wall Street East', 'Boston, USA'],
    ['Golden Gate', 'San Francisco, USA'],
    ['Windy City Center', 'Chicago, USA'],
    ['Space City', 'Houston, USA'],
    ['Sunset Branch', 'Los Angeles, USA'],
    ['Capital Hill', 'Washington DC, USA'],
    ['River North', 'Toronto, Canada'],
    ['Maple Square', 'Vancouver, Canada'],
    ['Euro Plaza', 'Frankfurt, Germany'],
    ['Berlin Central', 'Berlin, Germany'],
    ['Munich Square', 'Munich, Germany'],
    ['Paris Center', 'Paris, France'],
    ['Lyon Downtown', 'Lyon, France'],
    ['Marseille Port', 'Marseille, France'],
    ['Amsterdam Canal', 'Amsterdam, Netherlands'],
    ['Rotterdam Port', 'Rotterdam, Netherlands'],
    ['Barcelona Beach', 'Barcelona, Spain'],
    ['Madrid Center', 'Madrid, Spain'],
    ['Lisbon Ocean', 'Lisbon, Portugal'],
    ['Rome Colosseum', 'Rome, Italy'],
    ['Milan Fashion', 'Milan, Italy'],
    ['Osaka Bay', 'Osaka, Japan'],
    ['Kyoto Temple', 'Kyoto, Japan'],
    ['Seoul Gangnam', 'Seoul, South Korea'],
    ['Busan Port', 'Busan, South Korea'],
    ['Incheon Airport', 'Incheon, South Korea'],
    ['Singapore Central', 'Singapore'],
    ['Hong Kong Tower', 'Hong Kong, China'],
    ['Shanghai Bund', 'Shanghai, China'],
    ['Beijing Central', 'Beijing, China'],
    ['Guangzhou Tower', 'Guangzhou, China'],
    ['Shenzhen Bay', 'Shenzhen, China'],
    ['Bangkok Downtown', 'Bangkok, Thailand'],
    ['Kuala Lumpur Tower', 'Kuala Lumpur, Malaysia'],
    ['Jakarta Center', 'Jakarta, Indonesia'],
    ['Manila Bay', 'Manila, Philippines'],
    ['Hanoi Center', 'Hanoi, Vietnam'],
    ['Ho Chi Minh Square', 'Ho Chi Minh, Vietnam'],
    ['Sydney Opera', 'Sydney, Australia'],
    ['Melbourne Central', 'Melbourne, Australia'],
    ['Brisbane River', 'Brisbane, Australia'],
    ['Auckland Harbor', 'Auckland, New Zealand'],
    ['Dubai Downtown', 'Dubai, UAE'],
    ['Abu Dhabi Center', 'Abu Dhabi, UAE'],
    ['Doha City', 'Doha, Qatar'],
    ['Mumbai Central', 'Mumbai, India'],
    ['Delhi Center', 'Delhi, India'],
    ['Bangalore Tech', 'Bangalore, India'],
];
foreach ($branches as $b) {
    $stmt = $conn->prepare("INSERT INTO bank_branches (branch_name, location) VALUES (?, ?)");
    $stmt->bind_param("ss", $b[0], $b[1]);
    $stmt->execute();
}

$firstNames = [
    'John','Jane','Michael','Emily','David','Sarah','Chris','Anna','Robert','Linda',
    'Daniel','Sophia','James','Olivia','William','Ava','Benjamin','Mia','Elijah','Charlotte',
    'Richard','Lisa','Thomas','Nancy','Daniel','Karen','Anthony','Kimberly','Mark','Mary'
];
$lastNames = [
    'Smith','Johnson','Williams','Brown','Jones','Garcia','Miller','Davis','Taylor','Wilson',
    'Martinez','Anderson','Thomas','Hernandez','Moore','Martin','Jackson','Thompson','White','Lopez',
    'Gonzalez','Harris','Clark','Lewis','Robinson','Walker','Perez','Hall','Young','Allen'
];

$emailDomains = [
    'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com',
    'icloud.com', 'protonmail.com', 'aol.com', 'zoho.com',
    'mail.com', 'live.com', 'me.com', 'msn.com'
];

$totalAccounts = 20000;

for ($i = 0; $i < $totalAccounts; $i++) {
    $accNo = "ACC-" . (100000 + $i);
    $name = $firstNames[array_rand($firstNames)] . " " . $lastNames[array_rand($lastNames)];
    $balance = rand(1000, 50000);
    $branchId = rand(1, 4);

    // Insert Account
    $conn->query("INSERT INTO client_account (account_number, balance, branch_id) VALUES ('$accNo', $balance, $branchId)");
    
    // Insert Personal Info
	$randomDomain = $emailDomains[array_rand($emailDomains)];
    $email = strtolower(str_replace(' ', '.', $name)) . "@" . $randomDomain;
    $phone = "+1-555-010" . $i;
    $conn->query("INSERT INTO personal_info (account_number, full_name, email, phone) VALUES ('$accNo', '$name', '$email', '$phone')");
    
    // Insert 2 random Transactions
	$txNum = rand(2,5);
    for ($j = 0; $j < $txNum; $j++) {
        $type = ['Deposit', 'Withdrawal', 'Transfer'][rand(0, 2)];
        $amt = rand(50, 1000);
        $conn->query("INSERT INTO transactions (account_number, transaction_type, amount) VALUES ('$accNo', '$type', $amt)");
    }
}

// 6. Query Joined Data
$sql = "SELECT a.account_number, p.full_name, a.balance, b.branch_name, 
               (SELECT COUNT(*) FROM transactions t WHERE t.account_number = a.account_number) as tx_count
        FROM client_account a
        JOIN personal_info p ON a.account_number = p.account_number
        JOIN bank_branches b ON a.branch_id = b.id
        ORDER BY a.id DESC 
		LIMIT 100";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OpenShift Banking Dashboard - Fresh Start</title>
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
    <h2>Bank Management System</h2>
    <p class="subtitle">Environment: <strong>CRC 4.16</strong> | Host: <strong>mariadb</strong></p>
    
    <div style="margin-bottom: 20px;">
        <span class="badge-reset">NOTICE: Tables were dropped and recreated upon this request.</span>
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
                        <td><span class="tx-pill"><?php echo $row['tx_count']; ?> Total</span></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php $conn->close(); ?>
