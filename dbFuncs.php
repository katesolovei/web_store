<?php
include "config.php";

/***
 * Connection to DataBase
 *
 * @return mysqli
 */
function connect()
{
    $link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    return $link;
}

/***
 * Creating Data Base
 */
function createDB()
{
    $link = new mysqli(DB_HOST, DB_USER, DB_PASS);

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

    $query = "CREATE DATABASE " . DB_NAME;

    if ($link->query($query)) {
    } else {
        echo "Error creating database: " . $link->errno;
    }

    $link->close();
}

/***
 * Create table for saving product List
 *
 * @param string $tableName
 */
function createTableGoodsList($tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

    $query = "CREATE TABLE $tableName(
    code VARCHAR(30) NOT NULL ,
    price DOUBLE NOT NULL ,
    special_offer VARCHAR(30) NOT NULL,
    PRIMARY KEY (code))";

    if ($link->query($query)) {
    } else {
        echo "Error creating Table: " . $link->errno;
    }

    $link->close();
}

/***
 * Create table for saving product List of the shopping cart
 *
 * @param string $tableName
 */
function createTableShoppingCart($tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

    $query = "CREATE TABLE $tableName(
    code VARCHAR(30) NOT NULL ,
    numb INT(255) NOT NULL ,
    price DOUBLE NOT NULL ,
    PRIMARY KEY (code))";

    if ($link->query($query)) {
    } else {
        echo "Error creating Table: " . $link->errno;
    }

    $link->close();
}

/***
 * Checking if DataBase $name exists
 *
 * @param string $name
 * @return bool
 */
function checkDB($name)
{
    $link = new mysqli(DB_HOST, DB_USER, DB_PASS);

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

    $query = "SHOW DATABASES LIKE '$name'";

    $result = $link->query($query)->fetch_all();

    $result ? $res = false : $res = true;
    return $res;
}

/***
 * Checking if table $name exists
 *
 * @param string $name
 * @return bool
 */
function checkTable($name)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

    $query = "SELECT 1 FROM $name";
    $result = $link->query($query);

    $result ? $res = false : $res = true;

    return $res;
}

/***
 * Fill in the table with product list
 *
 * @param string $code
 * @param float|int $price
 * @param string|null $offer
 * @param string $tableName
 */
function fillInTableGoods($code, $price, $offer, $tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

    $query = "INSERT INTO $tableName(code, price, special_offer) VALUES (?,?,?)";
    $res = $link->prepare($query);
    $res->bind_param('sds', $code, $price, $offer);
    $res->execute();

    if ($res) {
    } else {
        echo mysqli_error($link);
    }
}

/***
 * Check if table $tableName has values
 *
 * @param string $tableName
 * @return bool
 */
function checkFillingTable($tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

    $query = "SELECT COUNT(*) FROM $tableName";
    $result = $link->query($query)->fetch_all();

    ($result[0][0] === "0") ? $res = true : $res = false;

    return $res;
}

/***
 * Get the list of products and all information about each
 *
 * @param string $tableName
 * @return array
 */
function getGoods($tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

    $query = "SELECT * FROM $tableName";
    $result = $link->query($query);

    $res = [];
    while ($row = $result->fetch_assoc()) {
        $res[] = $row;
    }

    return $res;
}

/***
 * Get all info from catalog about product with code = $code
 *
 * @param string $code
 * @param string $tableName
 * @return mixed array
 */
function getGoodsInfo($code, $tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

    $query = "SELECT * FROM $tableName WHERE code = ?";
    $res = $link->prepare($query);
    $res->bind_param('s', $code);
    $res->execute();

    $data[] = $res->get_result()->fetch_assoc();
    $res->free_result();

    return $data;
}

/***
 * Updating price and number of goods in shopping cart
 *
 * @param string $code
 * @param int $numb
 * @param float|int $price
 * @param string $tableName
 */
function updateCart($code, $numb, $price, $tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

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

/***
 * Count price of one product. Depends of number of product and if special offer presences
 *
 * @param string|null $condition special offer. true - presence, null - not
 * @param float|int $price price for 1 piece
 * @param float|int $offer_price price for several pieces if is set special offer
 * @param int $offer_numb number of products for special offer
 * @param int $numb number of products with the similar code in the shopping cart
 * @return float|int
 */
function countPrice($condition, $price, $offer_price, $offer_numb, $numb)
{
    if (!empty($condition)) {
        if ($numb / $offer_numb > 0) {
            $print_price = $offer_price * (intdiv($numb, $offer_numb)) + $price * ($numb % $offer_numb);
        } else $print_price = $price * $numb;
    } else $print_price = $price * $numb;
    return $print_price;
}

/***
 * Add product from catalog to the shopping cart
 *
 * @param string $code
 * @param string $tableName
 */
function addToCart($code, $tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

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
            $numb++;
            if (!empty($data[0]['special_offer'])) {
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

/***
 * Delete from the shopping cart one product witch has code = $code
 *
 * @param string $code
 * @param string $tableName
 */
function deleteOneProduct($code, $tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

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
            deleteAllProducts($code, $tableName);
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

/***
 * Delete from the shopping cart all products witch has code = $code
 *
 * @param string $code
 * @param string $tableName
 */
function deleteAllProducts($code, $tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

    $query = "DELETE FROM $tableName WHERE code = ?";
    $res = $link->prepare($query);
    $res->bind_param('s', $code);
    $res->execute();

    $link->close();
}

/***
 * Count total sum of products in the shopping cart
 *
 * @param string $tableName
 * @return double|int
 */
function getTotalSum($tableName)
{
    $link = connect();

    if ($link->connect_errno) {
        echo "Unable MySQL connection: " . $link->connect_error;
    }

    $query = "SELECT SUM(price) FROM $tableName";
    $res = $link->query($query);

    while ($row = $res->fetch_assoc()) {
        $result[] = $row;
    }

    return $result[0]['SUM(price)'];
}

/***
 * Preparing vars (from super global vars) for using in program
 *
 * @param mixed $data
 * @return string
 */
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}