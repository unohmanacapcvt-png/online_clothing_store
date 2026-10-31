
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";

// ADD PRODUCT
if (isset($_POST["add_product"])) {
    $category_id = (int) $_POST["category_id"];
    $product_name = trim($_POST["product_name"]);
    $price = (float) $_POST["price"];
    $stock = (int) $_POST["stock"];

    $sql = "INSERT INTO products
            (category_id, product_name, price, stock, created_by, updated_by)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $category_id,
        $product_name,
        $price,
        $stock,
        $_SESSION["user_id"],
        $_SESSION["user_id"]
    ]);

    $message = "Product added successfully!";
}

// UPDATE PRODUCT
if (isset($_POST["update_product"])) {
    $product_id = (int) $_POST["product_id"];
    $category_id = (int) $_POST["category_id"];
    $product_name = trim($_POST["product_name"]);
    $price = (float) $_POST["price"];
    $stock = (int) $_POST["stock"];

    $sql = "UPDATE products
            SET category_id = ?,
                product_name = ?,
                price = ?,
                stock = ?,
                updated_by = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE product_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $category_id,
        $product_name,
        $price,
        $stock,
        $_SESSION["user_id"],
        $product_id
    ]);

    $message = "Product updated successfully!";
}

// DELETE PRODUCT
if (isset($_POST["delete_product"])) {
    $product_id = (int) $_POST["product_id"];

    $stmt = $pdo->prepare(
        "DELETE FROM products WHERE product_id = ?"
    );
    $stmt->execute([$product_id]);

    $message = "Product deleted successfully!";
}

// GET PRODUCT TO EDIT
$edit_product = null;

if (isset($_GET["edit"])) {
    $stmt = $pdo->prepare(
        "SELECT * FROM products WHERE product_id = ?"
    );
    $stmt->execute([(int) $_GET["edit"]]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// GET CATEGORIES FOR DROPDOWN
$categories_list = $pdo->query(
    "SELECT * FROM categories ORDER BY category_name"
)->fetchAll(PDO::FETCH_ASSOC);

// DISPLAY ALL PRODUCTS
$sql = "SELECT products.*,
        categories.category_name,
        creator.username AS creator_name,
        editor.username AS editor_name
        FROM products
        INNER JOIN categories
        ON products.category_id = categories.category_id
        LEFT JOIN users AS creator
        ON products.created_by = creator.user_id
        LEFT JOIN users AS editor
        ON products.updated_by = editor.user_id
        ORDER BY product_id DESC";

$products = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Management</title>
</head>
<body>

<h1>Product Management</h1>

<p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>

<p><?php echo htmlspecialchars($message); ?></p>

<h2><?php echo $edit_product ? "Update Product" : "Add Product"; ?></h2>

<form method="POST">

    <?php if ($edit_product): ?>
        <input type="hidden" name="product_id"
        value="<?php echo $edit_product["product_id"]; ?>">
    <?php endif; ?>

    <label>Product Name:</label><br>
    <input type="text" name="product_name" required
    value="<?php echo htmlspecialchars($edit_product["product_name"] ?? ""); ?>">
    <br><br>

    <label>Category:</label><br>
    <select name="category_id" required>
        <option value="">Select Category</option>

        <?php foreach ($categories_list as $cat): ?>
            <option value="<?php echo $cat["category_id"]; ?>"
            <?php
            if ($edit_product && $edit_product["category_id"] == $cat["category_id"]) {
                echo "selected";
            }
            ?>>
                <?php echo htmlspecialchars($cat["category_name"]); ?>
            </option>
        <?php endforeach; ?>

    </select>
    <br><br>

    <label>Price:</label><br>
    <input type="number" name="price" step="0.01" min="0" required
    value="<?php echo htmlspecialchars($edit_product["price"] ?? ""); ?>">
    <br><br>

    <label>Stock:</label><br>
    <input type="number" name="stock" min="0" required
    value="<?php echo htmlspecialchars($edit_product["stock"] ?? "0"); ?>">
    <br><br>

    <?php if ($edit_product): ?>
        <button type="submit" name="update_product">Update Product</button>
        <a href="products.php">Cancel</a>
    <?php else: ?>
        <button type="submit" name="add_product">Add Product</button>
    <?php endif; ?>

</form>

<hr>

<h2>All Products</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Product Name</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Added By</th>
        <th>Last Updated By</th>
        <th>Last Updated</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($products as $product): ?>
    <tr>
        <td><?php echo $product["product_id"]; ?></td>
        <td><?php echo htmlspecialchars($product["product_name"]); ?></td>
        <td><?php echo htmlspecialchars($product["category_name"]); ?></td>
        <td><?php echo number_format($product["price"], 2); ?></td>
        <td><?php echo $product["stock"]; ?></td>
        <td><?php echo htmlspecialchars($product["creator_name"] ?? "Unknown"); ?></td>
        <td><?php echo htmlspecialchars($product["editor_name"] ?? "Not updated"); ?></td>
        <td><?php echo htmlspecialchars($product["updated_at"] ?? "Not updated"); ?></td>
        <td>
            <a href="products.php?edit=<?php echo $product["product_id"]; ?>">Edit</a>

            <form method="POST" style="display:inline;"
            onsubmit="return confirm('Are you sure you want to delete this product?');">
                <input type="hidden" name="product_id"
                value="<?php echo $product["product_id"]; ?>">
                <button type="submit" name="delete_product">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>

</table>

<br>
<a href="categories.php">Manage Categories</a> |
<a href="dashboard.php">Dashboard</a> |
<a href="logout.php">Logout</a>

</body>
</html>
