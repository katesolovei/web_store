<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'web_store');

$tableGoods = 'goodsList';
$tableCart = 'shopping_cart';
$goods = [
    ['code' => 'ZA', 'price' => 2.00, 'offer' => '7 for 4'],
    ['code' => 'YB', 'price' => 12.00, 'offer' => ''],
    ['code' => 'FC', 'price' => 1.25, 'offer' => '6 for 6'],
    ['code' => 'GD', 'price' => 0.15, 'offer' => '']
];

function connect(){
    $link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    return $link;
}