<?php
session_start();
include (__DIR__.'/../database.php');

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    if(isset($_SESSION['mattable'])){
    $table = $_SESSION['mattable'];
    
?>
<!DOCTYPE html>
<html>
<head>
    <title>Form</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
</head>
<style>
body {
    font-family: 'Open Sans', sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

h1 {
    font-family: 'Open Sans', sans-serif;
    text-align: center;
    color: #007BFF;
    margin-top: 20px;
}

form {
    text-align: center;
    margin: 20px auto;
    max-width: 1200px;
    padding: 10px;
    border: 1px solid #ccc;
    background-color: #fff;
}

label {
    display: block;
    margin: 10px 0;
    font-weight: bold;
        text-align:left;
}

input[type="text"],
input[type="number"] {
    font-family: 'Open Sans', sans-serif;
    width: 90%;
    padding: 10px;
    margin: 5px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    text-align: left;
}

input[type="submit"] {
    font-family: 'Open Sans', sans-serif;
    width: 99%;
    padding: 10px;
    margin: 5px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    text-align: center;
}

input[type="submit"] {
    background-color: #009e2a;
    color: #fff;
    cursor: pointer;
}

input[type="submit"]:hover {
    background-color: #009e2b;
}

table {
    font-family: 'Open Sans', sans-serif;
    margin: 20px auto;
    border-collapse: collapse;
    max-width: 600px;
    background-color: #fff;
    border: 1px solid #ccc;
}

table, th, td {
    border: 1px solid #ccc;
    
}

th, td {
    padding: 10px;
    text-align: center;
}

th {
    background-color: #007BFF;
    color: #fff;
    font-weight: bold;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

tr:hover {
    background-color: #d9edf7;
}
</style>
<body>
    <form method="post" action="">
        <?php
        $query = "SELECT * FROM $table";
        $result = mysqli_query($conn, $query);

        if ($result->num_rows > 0) {
            $row = mysqli_fetch_assoc($result);
            echo "<label for='id'>ID: </label>";
            echo "<input type='number' id='id' name='id' required><br>";
            echo "<label for='co'>CO: </label>";
            echo "<input type='text' id='co' name='co' required><br>";

            foreach ($row as $key => $value) {
                if (strpos($key, 'PO') === 0) {
                    echo "<label for='povalue'>$key: </label>";
                    echo "<input type='number' step='0.01' id='povalue' name='$key' required><br>";
                }
            }

            foreach ($row as $key => $value) {
                if (strpos($key, 'PSO') === 0) {
                    echo "<label for='psovalue'>$key: </label>";
                    echo "<input type='number' step='0.01' id='psovalue' name='$key' required><br>";
                }
            }
        }
        ?>
        <input type="submit" name="insert">
    </form>

    <?php
    if (isset($_POST['insert'])) {
        $id = $_POST['id'];
        $co = $_POST['co'];
        $povalue = array();
        $psovalue = array();

        // Process POST data for PO columns
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'PO') === 0) {
                $povalue[$key] = (float)$value;
            }
        }

        // Process POST data for PSO columns
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'PSO') === 0) {
                $psovalue[$key] = (float)$value;
            }
        }

           // Check if the ID already exists
    $checkQuery = "SELECT COUNT(*) as count FROM $table WHERE id = $id";
    $checkResult = mysqli_query($conn, $checkQuery);
    $row = mysqli_fetch_assoc($checkResult);

    if ($row['count'] > 0) {
        // Increment IDs for existing rows with IDs greater than or equal to the desired ID
        $updateQuery = "UPDATE $table SET id = id + 1 WHERE id >= $id ORDER BY id DESC";
        if (mysqli_query($conn, $updateQuery)) {
            
        } else {
            echo "Error incrementing ID values: " . mysqli_error($conn);
        }
    }

    // Insert the new row with the desired ID
    $columns = implode(", ", array_merge(['id', 'co'], array_keys($povalue), array_keys($psovalue)));
    $values = implode(", ", array_merge([$id, "'$co'"], $povalue, $psovalue));
    $insertQuery = "INSERT INTO $table ($columns) VALUES ($values)";

    if (mysqli_query($conn, $insertQuery)) {
        $updateQuery = "SET @row_number = 0";
        mysqli_query($conn, $updateQuery);
    
        $updateQuery = "UPDATE $table SET id = (@row_number:=@row_number+1) ORDER BY id";
        if (mysqli_query($conn, $updateQuery)) {
           
        } else {
            echo "Error updating ID values: " . mysqli_error($conn);
        } 
    } else {
        echo "Error inserting row: " . mysqli_error($conn);
    }
}

$deleteQuery = "DELETE FROM $table WHERE CO = 'AVG'";
$deleteResult = mysqli_query($conn, $deleteQuery);
if (!$deleteResult) {
    echo "Error deleting 'AVG' row: " . mysqli_error($conn);
}
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

} else {
    echo "Error inserting 'AVG' row: " . mysqli_error($conn);
}

$updateQuery = "SET @row_number = 0";
                    mysqli_query($conn, $updateQuery);
        
                    $updateQuery = "UPDATE $table SET id = (@row_number:=@row_number+1)";
                    mysqli_query($conn, $updateQuery);
        
// Close the MySQLi database connection
mysqli_close($conn);
    ?>
  
</body>
</html>
<?php
    }?>