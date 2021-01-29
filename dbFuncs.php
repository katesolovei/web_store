<?php
include "config.php";

function createDB()
{
    $link = new mysqli(DB_HOST, DB_USER, DB_PASS);

    if ($link->connect_errno) {
        echo "Не удалось подключиться к MySQL: " . $link->connect_error;
    }

    $query = "CREATE DATABASE " . DB_NAME;

    if ($link->query($query)) {
        echo "Database created successfully with the name '" . DB_NAME . "'";
    } else {
        echo "Error creating database: " . $link->errno;
    }

    $link->close();
}

function createTableGoodsList($tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Не удалось подключиться к MySQL: " . $link->connect_error;
    }

    $query = "CREATE TABLE $tableName(
    code VARCHAR(30) NOT NULL ,
    price DOUBLE NOT NULL ,
    special_offer VARCHAR(30) NOT NULL,
    PRIMARY KEY (code))";

    if ($link->query($query)) {
        echo "Table created successfully with the name 'goodsList'";
    } else {
        echo "Error creating Table: " . $link->errno;
    }

    $link->close();
}

function createTableShoppingCart($tableName)
{
    $link = connect();

    $query = "CREATE TABLE $tableName(
    code VARCHAR(30) NOT NULL ,
    numb INT(255) NOT NULL ,
    price DOUBLE NOT NULL ,
    PRIMARY KEY (code))";

    if ($link->query($query)) {
        echo "Table created successfully with the name 'shoppingCart'";
    } else {
        echo "Error creating Table: " . $link->errno;
    }

    $link->close();
}

/*Checking if DataBase exists*/
function checkDB($name)
{

    $link = new mysqli(DB_HOST, DB_USER, DB_PASS);

    $query = "SHOW DATABASES LIKE '$name'";

    $result = $link->query($query)->fetch_all();

    $result ? $res = false : $res = true;
    return $res;
}

/*Checking if table exists*/
function checkTable($name)
{
    $link = connect();
    $query = "SELECT 1 FROM $name";

    $result = $link->query($query);

    $result ? $res = false : $res = true;
    return $res;
}

function fillInTableGoods($code, $price, $offer, $tableName)
{
    $link = connect();
    $query = "INSERT INTO $tableName(code, price, special_offer) VALUES (?,?,?)";

    $res = $link->prepare($query);
    $res->bind_param('sds', $code, $price, $offer);
    $res->execute();
    if ($res) {
    } else {
        echo mysqli_error($link);
    }
}

function checkFillingTable($tableName)
{
    $link = connect();

    $query = "SELECT COUNT(*) FROM $tableName";

    $result = $link->query($query)->fetch_all();

    ($result[0][0] === "0") ? $res = true : $res = false;

    return $res;
}

function getGoods($tableName)
{
    $link = connect();

    $query = "SELECT * FROM $tableName";
    $result = $link->query($query);
    $res = [];
    while ($row = $result->fetch_assoc()) {
        $res[] = $row;
    }
    return $res;
}

function getGoodsInfo($code, $tableName)
{
    $link = connect();
    $query = "SELECT * FROM $tableName WHERE code = ?";
    $res = $link->prepare($query);
    $res->bind_param('s', $code);
    $res->execute();
    $data[] = $res->get_result()->fetch_assoc();
    $res->free_result();
    return $data;
}

function updateCart($code, $numb, $price, $tableName)
{
    $link = connect();
    $query = "UPDATE $tableName SET numb =  ?, price = ? WHERE code=?";
    $res = $link->prepare($query);
    $res->bind_param('ids', $numb, $price, $code);
    $res->execute();

    if ($res) {
    } else {
        echo mysqli_error($link);
    }
    $link->close();
}

function countPrice($condition, $price, $offer_price, $offer_numb, $numb)
{
    if (!empty($condition)) {
        if ($numb / $offer_numb > 0) {
            $print_price = $offer_price * (intdiv($numb, $offer_numb)) + $price * ($numb % $offer_numb);
        } else $print_price = $price * $numb;
    } else $print_price = $price * $numb;
    return $print_price;
}

function addToCart($code, $tableName)
{
    $link = connect();
    $data = getGoodsInfo($code, 'goodslist');

    if (!empty($data[0]['special_offer'])) {
        $offer = explode(' for ', $data[0]['special_offer']);
        $offer_numb = $offer[1];
        $offer_price = $offer[0];
    }
    foreach ($data as $prod) {
        $info = getGoodsInfo($code, $tableName);
        $price = $prod['price'];
        $numb = $info[0]['numb'];

        if (empty($numb)) {
            $numb = 1;
            $query = "INSERT INTO $tableName(code, numb, price) VALUES (?, ?, ?)";
            $res = $link->prepare($query);
            $res->bind_param('sid', $code, $numb, $price);
            $res->execute();
        } else {
            $numb++; if (!empty($data[0]['special_offer'])) {
                $print_price = countPrice($data[0]['special_offer'], $price, $offer_price, $offer_numb, $numb);
            } else {
                $print_price = countPrice('', $price, '', '', $numb);
            }
            $print_price = round($print_price, 2);
            updateCart($code, $numb, $print_price, $tableName);
        }
    }

    $link->close();
}

function deleteOneProduct($code, $tableName)
{
    $link = connect();
    $info = getGoodsInfo($code, 'goodslist');
    $price = $info[0]['price'];
    $data = getGoodsInfo($code, $tableName);

    if (!empty($info[0]['special_offer'])) {
        $offer = explode(' for ', $info[0]['special_offer']);
        $offer_numb = $offer[1];
        $offer_price = $offer[0];
    }

    foreach ($data as $prod) {
        $numb = $prod['numb'];
        $numb--;
        var_dump($numb);
        if ($numb === 0) {
            deleteAllProducts($code,$tableName);
        } else {
            if (!empty($info[0]['special_offer'])) {
                $print_price = countPrice($info[0]['special_offer'], $price, $offer_price, $offer_numb, $numb);
            } else {
                $print_price = countPrice('', $price, '', '', $numb);
            }
            $print_price = round($print_price, 2);
            updateCart($code, $numb, $print_price, $tableName);
        }
    }

    $link->close();
}

function deleteAllProducts($code, $tableName)
{
    $link = connect();

    $query = "DELETE FROM $tableName WHERE code = ?";
    $res = $link->prepare($query);
    $res->bind_param('s', $code);
    $res->execute();

    $link->close();
}

function getTotalSum($tableName){
    $link = connect();

    $query = "SELECT SUM(price) FROM $tableName";
    $res = $link->query($query);

    while ($row = $res->fetch_assoc()) {
        $result[] = $row;
    }

    return $result[0]['SUM(price)'];
}