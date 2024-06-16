<?php
// Check if the request is a POST request
session_start();
include (__DIR__.'/../database.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the JSON data sent from the client
    $postData = json_decode(file_get_contents("php://input"), true);
    
    // Extract data from the JSON
    $tableName = $postData["tableName"];
    $data = $postData["data"];
    $coCount = $postData["coCount"];
    $poCount = $postData["poCount"];
    $psoCount = $postData["psoCount"];
    
    $conn = new mysqli($servername, $username, $password, $dbname);


    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the table already exists
    $tableExistsQuery = "SHOW TABLES LIKE '$tableName'";
    $tableExistsResult = $conn->query($tableExistsQuery);

    if ($tableExistsResult->num_rows == 0) {
        // Table does not exist, create it
        $createTableQuery = "CREATE TABLE $tableName (";
        $createTableQuery .= "id INT AUTO_INCREMENT PRIMARY KEY, ";
        $createTableQuery .= "CO VARCHAR(255), ";

        // Add columns for PO and PSO dynamically
        for ($i = 1; $i <= $poCount; $i++) {
            $createTableQuery .= "PO$i DECIMAL(10,2), ";
        }

        for ($i = 1; $i <= $psoCount; $i++) {
            $createTableQuery .= "PSO$i DECIMAL(10,2), ";
        }

        $createTableQuery = rtrim($createTableQuery, ", "); // Remove trailing comma
        $createTableQuery .= ")";
        
        if ($conn->query($createTableQuery) === TRUE) {
            echo "Table created successfully.";
            foreach ($data as $rowData) {
        $coValue = $rowData[0];
        $poValues = array_slice($rowData, 1, $poCount);
        $psoValues = array_slice($rowData, $poCount + 1, $psoCount);

        $insertQuery = "INSERT INTO $tableName (CO, ";
        for ($i = 1; $i <= $poCount; $i++) {
            $insertQuery .= "PO$i, ";
        }

        for ($i = 1; $i <= $psoCount; $i++) {
            $insertQuery .= "PSO$i, ";
        }

        $insertQuery = rtrim($insertQuery, ", "); // Remove trailing comma
        $insertQuery .= ") VALUES ('$coValue', '" . implode("', '", array_merge($poValues, $psoValues)) . "')";

        if ($conn->query($insertQuery) !== TRUE) {
            echo "Error inserting data: " . $conn->error;
        }
    }

    // Calculate and insert averages
    $avgQuery = "INSERT INTO $tableName (CO, ";
    for ($i = 1; $i <= $poCount; $i++) {
        $avgQuery .= "PO$i, ";
    }

    for ($i = 1; $i <= $psoCount; $i++) {
        $avgQuery .= "PSO$i, ";
    }

    $avgQuery = rtrim($avgQuery, ", "); // Remove trailing comma
    $avgQuery .= ") VALUES ('AVG', ";

    for ($i = 1; $i <= $poCount + $psoCount; $i++) {
        $columnValues = array_column($data, $i);
        $avg = array_sum($columnValues) / count($columnValues);
        $avgQuery .= "'$avg', ";
    }

    $avgQuery = rtrim($avgQuery, ", "); // Remove trailing comma
    $avgQuery .= ")";

    if ($conn->query($avgQuery) !== TRUE) {
        echo "Error inserting average data: " . $conn->error;
    }

    $conn->close();           
        } else {
            echo "Error creating table: " . $conn->error;
        }
    } else {
        echo "Table already exists.";
    }

    
} else {
    echo "Invalid request method.";
}
?>
