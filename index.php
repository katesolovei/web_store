<?php
include "dbFuncs.php";

if (checkDB(DB_NAME)) createDB();

if (checkTable($tableGoods)) {
    createTableGoodsList($tableGoods);
}
if (checkFillingTable($tableGoods)) {
    $goods_number = count($goods);
    print ($goods_number);
    for ($i = 0; $i < $goods_number; $i++) {
        fillInTableGoods($goods[$i]['code'], $goods[$i]['price'], $goods[$i]['offer'], $tableGoods);
    }
}

if (checkTable($tableCart)) createTableShoppingCart($tableCart);

if(isset($_GET['code'])){
    $code = test_input($_GET['code']);

    addToCart($code, $tableCart);
}

?>

    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            table {
                font-size: 18px;
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            td, th {
                border: 1px solid #424242;
                text-align: left;
                padding: 8px;
            }

            tr:nth-child(even) {
                background-color: #dddddd;
            }

            .thead {
                background-color: black;
                color: white;
            }

            .link {
                color: black;
                text-decoration: none;
                font-weight: bold;
            }

            .link:hover {
                text-decoration: underline;
                color: #0D25A7;
            }
        </style>
    </head>
    <body>
<form action="cart.php" method="post">
    <h2 style="float: left;">List of products</h2>
    <input type="image" src="img/shopping-cart1.png" style="float: right; width: 70px; height: 70px;">

    <table>
        <tr>
            <th class="thead">Product code</th>
            <th class="thead">Price</th>
            <th class="thead">Action</th>
        </tr>
        <?php
        $goods = getGoods($tableGoods);
        foreach ($goods as $product) {
            echo '<tr><td>' . $product['code'] . '</td>' . '<td>£' . $product['price'];
            if (!empty($product['special_offer'])) {
                echo ' £' . $product['special_offer'] . ' pieces</td>';
            }
            echo '<td><a href="?code='.$product['code'].'" class="link">Add to the shopping cart</a></td></tr>';
        }
        ?>
    </table>
</form>
    </body>
    </html>
<?php
/*Processing of input data*/
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}