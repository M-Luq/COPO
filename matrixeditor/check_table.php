<?php
include (__DIR__.'/../database.php');

// Get the subject code from the POST request
$subjectCode = $_POST["subject_code"];

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare a SQL query to check if the table exists
$query = "SHOW TABLES LIKE '$subjectCode'";

// Execute the query
$result = $conn->query($query);

if ($result->num_rows > 0) {
    // The table exists
    echo "exists";
} else {
    // The table does not exist
    echo "not_exists";
}

// Close the database connection
$conn->close();
?>
