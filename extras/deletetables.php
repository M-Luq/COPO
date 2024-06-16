<?php
include ('database.php');

$prefixToDelete = ["assessment_", "assignments_", "assignment_", "coas_", "comb_", "coat_", "grades_", "submission_","students_","psoat_"];

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 


// Get a list of all tables in the database
$tablesQuery = "SHOW TABLES";
$result = $conn->query($tablesQuery);

if ($result->num_rows > 0) {
    // Iterate through the tables and delete those with matching prefixes
    while ($row = $result->fetch_assoc()) {
        $tableName = $row["Tables_in_" . $database];
        // Extract the prefix from the table name using regular expressions
        preg_match('/^([a-zA-Z_]+)/', $tableName, $matches);
$tablePrefix = $matches[1];
echo "Table Name: $tableName, Extracted Prefix: $tablePrefix<br>";

        if (in_array($tablePrefix, $prefixToDelete)) {
            $deleteQuery = "DROP TABLE `$tableName`";
            $conn->query($deleteQuery);
        }
            
    }
}

// Close the database connection
$conn->close();
?>
