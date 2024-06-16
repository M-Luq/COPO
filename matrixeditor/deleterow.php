<?php
include (__DIR__.'/../database.php');

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['edit'])) {
    $table = $_POST['table'];
    $id = $_POST['id'];
    $updateQuery = "UPDATE $table SET ";
    $paramTypes = '';
    $paramValues = [];

    foreach ($_POST as $key => $value) {
        if ($key !== 'table' && $key !== 'id' && $key !== 'edit') {
            $updateQuery .= "$key = ?, ";
            $paramTypes .= 's';
            $paramValues[] = $value;
        }
    }

    // Remove the trailing comma and space
    $updateQuery = rtrim($updateQuery, ', ');

    // Add the WHERE clause to update the correct row
    $updateQuery .= " WHERE id = ?";
    $paramTypes .= 's';
    $paramValues[] = $id;

    $stmt = mysqli_prepare($conn, $updateQuery);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $paramTypes, ...$paramValues);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Data updated successfully
            echo "Row updated successfully.";
        } else {
            echo "Error updating row: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }
}

elseif (  isset($_POST['delete'])) {
    $table = $_POST['table'];
    $id = $_POST['id'];

    // Delete the row with the specified ID
    $deleteQuery = "DELETE FROM $table WHERE id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {

            $updateQuery = "SET @row_number = 0";
            mysqli_query($conn, $updateQuery);

            $updateQuery = "UPDATE $table SET id = (@row_number:=@row_number+1)";
            mysqli_query($conn, $updateQuery);

            echo "Row deleted successfully. ID values updated.";
        } else {
            echo "Error deleting row: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }
}

// Delete the existing "AVG" row if it exists
$deleteQuery = "DELETE FROM $table WHERE CO = 'AVG'";
$deleteResult = mysqli_query($conn, $deleteQuery);
if (!$deleteResult) {
    echo "Error deleting 'AVG' row: " . mysqli_error($conn);
}

// Determine the number of 'PO' and 'PSO' columns dynamically
$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table' AND (COLUMN_NAME LIKE 'PO%' OR COLUMN_NAME LIKE 'PSO%')";
$result = mysqli_query($conn, $sql);

$columnNames = array();
while ($row = mysqli_fetch_assoc($result)) {
    $columnNames[] = $row['COLUMN_NAME'];
}

// Build the INSERT query dynamically
$avgQuery = "INSERT INTO $table (CO, ";
$avgQuery .= implode(", ", $columnNames);
$avgQuery .= ") VALUES ('AVG', ";

// Calculate and add the average values
foreach ($columnNames as $columnName) {
    // Modify the following SQL query to retrieve the data from your database using MySQLi with dynamically obtained column names
    $sql = "SELECT $columnName FROM $table";
    $result = mysqli_query($conn, $sql);
    
    $columnValues = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $columnValues[] = $row[$columnName];
    }

    $avg = array_sum($columnValues) / count($columnValues);
    $avgQuery .= "'$avg', ";
}

$avgQuery = rtrim($avgQuery, ", "); // Remove trailing comma
$avgQuery .= ")";
$avgInsertResult = mysqli_query($conn, $avgQuery);
if ($avgInsertResult) {
    echo "Records inserted successfully.";
} else {
    echo "Error inserting 'AVG' row: " . mysqli_error($conn);
}

$updateQuery = "SET @row_number = 0";
                    mysqli_query($conn, $updateQuery);
        
                    $updateQuery = "UPDATE $table SET id = (@row_number:=@row_number+1)";
                    mysqli_query($conn, $updateQuery);
        
echo "Row deleted successfully. ID values updated.";
// Close the MySQLi database connection
mysqli_close($conn);

header("Location: edit.php?table=$table");
exit;
?>