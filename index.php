<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License

require 'webroot' . DIRECTORY_SEPARATOR . 'index.php';
*/

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>年度銷售數據報表</title>
    <style>
        /* CSS 樣式設定 */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            padding: 40px;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse; /* 合併邊框 */
            margin-top: 10px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3498db;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* 根據銷售狀態顯示不同顏色 */
        .status {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .completed { background: #d4edda; color: #155724; }
        .pending { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>

<div class="container">
    <h2>📊 銷售數據實時報表</h2>
    
    <table>
        <thead>
            <tr>
                <th>編號</th>
                <th>日期</th>
                <th>產品名稱</th>
                <th>銷售額 (USD)</th>
                <th>狀態</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // PHP 隨機數據生成
            $products = ["智能手機", "筆記型電腦", "無線耳機", "平板電腦", "智慧手錶"];
            $statuses = ["已完成", "待處理"];

            for ($i = 1; $i <= 10; $i++) {
                $random_product = $products[array_rand($products)];
                $random_price = rand(100, 2000);
                $random_date = date("Y-m-d", strtotime("-" . rand(0, 30) . " days"));
                $status_text = $statuses[array_rand($statuses)];
                $status_class = ($status_text == "已完成") ? "completed" : "pending";

                echo "<tr>";
                echo "<td>#$i</td>";
                echo "<td>$random_date</td>";
                echo "<td>$random_product</td>";
                echo "<td>$" . number_format($random_price) . "</td>";
                echo "<td><span class='status $status_class'>$status_text</span></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
