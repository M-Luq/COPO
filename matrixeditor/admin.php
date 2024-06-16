<!DOCTYPE html>
<html>
<head>
    <title>Admin Email Registration</title>
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f2f2f2;
        }
        
        h1 {
            font-family: 'Open Sans', sans-serif;
    text-align: center;
    margin-top: 20px;
    color: #000000;
    background-color:#009e2a;
    padding: 20px;
}
        form {
            background-color: #fff;
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="email"] {
            font-family: 'Open Sans', sans-serif;
            width: 95%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        input[type="submit"] {
            font-family: 'Open Sans', sans-serif;
            background-color: #2d753f;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        button[type="button"] {
            font-family: 'Open Sans', sans-serif;
            background-color: #ccc;
            color: #000;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }

        button[type="button"]:hover {
            background-color: #999;
        }
    </style>
</head>
<body>
    <h1>Super Admin</h1>
    

    <form method="post" action="">
        <label for="email">Enter your email:</label>
        <input type="email" name="email" id="email" required>
        <input type="submit" value="Register">
        <button type="button" onclick="window.location.href='matrix.php'">Back</button>
    </form>
</body>
</html>

<?php
include (__DIR__.'/../database.php');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize email input
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $sql = "CREATE TABLE IF NOT EXISTS admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255)
        )";

        // Create the "admin" table if it doesn't exist
        $conn->exec($sql);

        // Check if the email already exists in the database
        $checkEmailQuery = "SELECT * FROM admin WHERE email = :email";
        $checkStmt = $conn->prepare($checkEmailQuery);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            echo "Email already exists.";
        } else {
            // Insert the email into the "admin" table using a prepared statement
            $sql = "INSERT INTO admin (email) VALUES (:email)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);

            if ($stmt->execute()) {
                echo "Email registered successfully!";
            } else {
                echo "Error registering email.";
            }
        }
    } else {
        echo "Invalid email format.";
    }
}
?>
