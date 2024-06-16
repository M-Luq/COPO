<?php
session_start();

require __DIR__.'/../vendor/autoload.php';
include (__DIR__.'/../database.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

// Check if a file was uploaded
if (isset($_FILES["excel_file_t"]) && $_FILES["excel_file_t"]["error"] == 0) {
    // Define the target directory where uploaded files will be stored
    $uploadDir = __DIR__.'/../uploads/';

    // Get the uploaded file's name and path
    $uploadFilePath = $uploadDir . basename($_FILES["excel_file_t"]["name"]);

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["excel_file_t"]["tmp_name"], $uploadFilePath)) {
        try {
            // Load the Excel file
            $spreadsheet = IOFactory::load($uploadFilePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Establish a database connection (adjust these parameters according to your database setup)
            $mysqli = new mysqli($servername, $username, $password, $dbname);
            
            // Check for a successful connection
            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }

            // Get table name from the file name
            $tableName = pathinfo($_FILES["excel_file_t"]["name"], PATHINFO_FILENAME);
            $tableName = preg_replace("/[^a-zA-Z0-9_]/", "_", $tableName);
            // Sanitize table name
            $tableName = "students_" . strtolower($tableName); // Add a prefix for safety

            $dropTableSQL = "DROP TABLE IF EXISTS $tableName";
            if ($mysqli->query($dropTableSQL) === TRUE) {
                // Table dropped successfully
            } else {
                echo "Error dropping table: " . $mysqli->error;
            }

            $createTableSQL = "CREATE TABLE IF NOT EXISTS $tableName (
                id INT AUTO_INCREMENT PRIMARY KEY,
                REGNO VARCHAR(255) NOT NULL UNIQUE,
                STDNAME VARCHAR(255) NOT NULL
            )";
            if ($mysqli->query($createTableSQL) === TRUE) {
                // Table created successfully
            } else {
                echo "Error creating table: " . $mysqli->error;
            }

            foreach ($worksheet->getRowIterator() as $key => $row) {
                if ($key > 1) { // Skip the header row (row 1)
                    // Assuming you want to get data from columns A (0-indexed) and B (1-indexed)
                    $regno = $worksheet->getCellByColumnAndRow(1, $key)->getValue(); // Column A
                    $name = $worksheet->getCellByColumnAndRow(2, $key)->getValue(); // Column B
            
                    // Check if either $name or $regno is not empty before inserting
                    if (!empty($name) && !empty($regno)) {
                        // Insert data into the database
                        $insertSQL = "INSERT INTO $tableName (REGNO, STDNAME) VALUES (?, ?)";
                        $stmt = $mysqli->prepare($insertSQL);
                        $stmt->bind_param("ss", $regno, $name);
                        if ($stmt->execute()) {
                            // Data inserted successfully
                        } else {
                            echo "Error inserting data: " . $mysqli->error;
                        }
                    }
                }
            }
            
            $_SESSION['stdnamelist'] = $tableName;

            unlink($uploadFilePath);
            $mysqli->close();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Error uploading the file.";
    }
} else {
    echo "Please choose a valid Excel file.";
}




if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['num_assessments'])) {
        $_SESSION['num_assessments'] = $_POST['num_assessments'];
    }
    
    if (isset($_POST['num_assignments'])) {
        $_SESSION['num_assignments'] = $_POST['num_assignments'];
    }
    if (isset($_POST['subject_code'])) {
        $_SESSION['subject_code'] = $_POST['subject_code'];
    }


    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $subject_code=$_SESSION['subject_code'];
   
    
    // SQL query to fetch data from the table
    $sql = "SELECT distinct count(CO) AS co_count FROM $subject_code WHERE CO LIKE 'CO%'";
    
    $result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Get the count of distinct CO values
    $coCount = $row['co_count'];
} else {
    
}
$_SESSION['co_no']=$coCount;


$conn->close();

header("Location: ../theory/pattern.php");

exit;

}
else{

    header("Location: ../theory/pattern.php");
exit;

}
?>
