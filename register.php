<?php
require_once 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $address = trim($_POST["address"]);
    $age = (int) $_POST["age"];
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users
        (first_name, last_name, address, age, email, username, password)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $first_name,
            $last_name,
            $address,
            $age,
            $email,
            $username,
            $password
        ]);

        $message = "Registration successful!";

    } catch (PDOException $e) {
        $message = "Registration failed. Email or username may already exist.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>Create an Account</h2>

<p><?php echo htmlspecialchars($message); ?></p>

<form method="POST" action="">

    <label>First Name:</label><br>
    <input type="text" name="first_name" required><br><br>

    <label>Last Name:</label><br>
    <input type="text" name="last_name" required><br><br>

    <label>Address:</label><br>
    <input type="text" name="address"><br><br>

    <label>Age:</label><br>
    <input type="number" name="age" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Register</button>

</form>

</body>
</html>