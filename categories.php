
<?php
session_start();
require_once 'db.php';

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";

// ADD CATEGORY
if (isset($_POST["add_category"])) {
    $category_name = trim($_POST["category_name"]);
    $description = trim($_POST["description"]);

    if ($category_name !== "") {
        $sql = "INSERT INTO categories
                (category_name, description, created_by, updated_by)
                VALUES (?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $category_name,
            $description,
            $_SESSION["user_id"],
            $_SESSION["user_id"]
        ]);

        $message = "Category added successfully!";
    }
}

// UPDATE CATEGORY
if (isset($_POST["update_category"])) {
    $category_id = (int) $_POST["category_id"];
    $category_name = trim($_POST["category_name"]);
    $description = trim($_POST["description"]);

    $sql = "UPDATE categories
            SET category_name = ?,
                description = ?,
                updated_by = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE category_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $category_name,
        $description,
        $_SESSION["user_id"],
        $category_id
    ]);

    $message = "Category updated successfully!";
}

// DELETE CATEGORY
if (isset($_POST["delete_category"])) {
    $category_id = (int) $_POST["category_id"];

    try {
        $stmt = $pdo->prepare(
            "DELETE FROM categories WHERE category_id = ?"
        );
        $stmt->execute([$category_id]);

        $message = "Category deleted successfully!";
    } catch (PDOException $e) {
        $message = "Cannot delete this category because it may have products.";
    }
}

// GET CATEGORY TO EDIT
$edit_category = null;

if (isset($_GET["edit"])) {
    $stmt = $pdo->prepare(
        "SELECT * FROM categories WHERE category_id = ?"
    );
    $stmt->execute([(int) $_GET["edit"]]);
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}

// DISPLAY ALL CATEGORIES
$sql = "SELECT categories.*,
        creator.username AS creator_name,
        editor.username AS editor_name
        FROM categories
        LEFT JOIN users AS creator
        ON categories.created_by = creator.user_id
        LEFT JOIN users AS editor
        ON categories.updated_by = editor.user_id
        ORDER BY category_id DESC";

$categories = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Category Management</title>
</head>
<body>

<h1>Category Management</h1>

<p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>

<p><?php echo htmlspecialchars($message); ?></p>

<h2><?php echo $edit_category ? "Update Category" : "Add Category"; ?></h2>

<form method="POST">

    <?php if ($edit_category): ?>
        <input type="hidden" name="category_id"
        value="<?php echo $edit_category["category_id"]; ?>">
    <?php endif; ?>

    <label>Category Name:</label><br>
    <input type="text" name="category_name" required
    value="<?php echo htmlspecialchars($edit_category["category_name"] ?? ""); ?>">
    <br><br>

    <label>Description:</label><br>
    <textarea name="description"><?php
        echo htmlspecialchars($edit_category["description"] ?? "");
    ?></textarea>
    <br><br>

    <?php if ($edit_category): ?>
        <button type="submit" name="update_category">Update Category</button>
        <a href="categories.php">Cancel</a>
    <?php else: ?>
        <button type="submit" name="add_category">Add Category</button>
    <?php endif; ?>

</form>

<hr>

<h2>All Categories</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Category Name</th>
        <th>Description</th>
        <th>Added By</th>
        <th>Last Updated By</th>
        <th>Last Updated</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($categories as $category): ?>
    <tr>
        <td><?php echo $category["category_id"]; ?></td>
        <td><?php echo htmlspecialchars($category["category_name"]); ?></td>
        <td><?php echo htmlspecialchars($category["description"] ?? ""); ?></td>
        <td><?php echo htmlspecialchars($category["creator_name"] ?? "Unknown"); ?></td>
        <td><?php echo htmlspecialchars($category["editor_name"] ?? "Not updated"); ?></td>
        <td><?php echo htmlspecialchars($category["updated_at"] ?? "Not updated"); ?></td>
        <td>
            <a href="categories.php?edit=<?php echo $category["category_id"]; ?>">Edit</a>

            <form method="POST" style="display:inline;"
            onsubmit="return confirm('Are you sure you want to delete this category?');">
                <input type="hidden" name="category_id"
                value="<?php echo $category["category_id"]; ?>">
                <button type="submit" name="delete_category">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>

</table>

<br>
<a href="dashboard.php">Back to Dashboard</a> |
<a href="logout.php">Logout</a>

</body>
</html>
