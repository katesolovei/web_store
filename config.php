<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'web_store');

$tableGoods = 'goodsList'; // Name of the table for saving product List
$tableCart = 'shopping_cart'; // Name of the table for saving products from the shopping cart
// List of products and information about each
$goods = [
    ['code' => 'ZA', 'price' => 2.00, 'offer' => '7 for 4'],
    ['code' => 'YB', 'price' => 12.00, 'offer' => ''],
    ['code' => 'FC', 'price' => 1.25, 'offer' => '6 for 6'],
    ['code' => 'GD', 'price' => 0.15, 'offer' => '']
];
