
<?php
session_start();

require_once 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["user_id"]]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Dashboard</title>
</head>
<body>

    <h1>Welcome to Your Dashboard!</h1>

    <h2>Hello, <?php echo htmlspecialchars($user["first_name"]); ?>!</h2>

    <p>You have successfully logged in.</p>

    <hr>

    <h2>Online Clothing Store</h2>

    <p>
        <a href="categories.php">
            Manage Categories (Parent Entity)
        </a>
    </p>

    <p>
        <a href="products.php">
            Manage Products (Child Entity)
        </a>
    </p>

    <hr>

    <h3>Your Account Information</h3>

    <p><strong>First Name:</strong>
        <?php echo htmlspecialchars($user["first_name"]); ?>
    </p>

    <p><strong>Last Name:</strong>
        <?php echo htmlspecialchars($user["last_name"]); ?>
    </p>

    <p><strong>Username:</strong>
        <?php echo htmlspecialchars($user["username"]); ?>
    </p>

    <p><strong>Email:</strong>
        <?php echo htmlspecialchars($user["email"]); ?>
    </p>

    <br>

    <a href="logout.php">Logout</a>

</body>
</html>
