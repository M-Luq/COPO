<?php
session_start();
require __DIR__.'/../vendor/autoload.php';
include (__DIR__.'/../database.php');
use PhpOffice\PhpSpreadsheet\IOFactory;
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    // Check if the file was uploaded directly via sheet.html
    
    if (isset($_POST['subject_code'])) {
        $_SESSION['subject_code'] = $_POST['subject_code'];
    }
    // Check if a file was uploaded
    if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] === UPLOAD_ERR_OK) 
    {
        // Specify the path to save the uploaded file
        $uploadDir = __DIR__ . '/../uploads/';
        $uploadFile = $uploadDir . basename($_FILES['excelFile']['name']);

        // Move the uploaded file to the specified path
    if (move_uploaded_file($_FILES['excelFile']['tmp_name'], $uploadFile)) {
        try {
            // Load the Excel file
            $_SESSION["file_name"] =  $uploadFile ;
            $spreadsheet = IOFactory::load($uploadFile);
            $worksheet = $spreadsheet->getActiveSheet();

            // Establish a database connection (adjust these parameters according to your database setup)
            $mysqli = new mysqli($servername, $username, $password, $dbname);
            
            // Check for a successful connection
            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }

            // Get table name from the file name
            $tableName = pathinfo($_FILES["excelFile"]["name"], PATHINFO_FILENAME);
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
                if ($key > 4) { // Skip  till row (row 4)
                    $regno = $worksheet->getCellByColumnAndRow(2, $key)->getValue(); // Column B
                    $name = $worksheet->getCellByColumnAndRow(3, $key)->getValue(); // Column C
            
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
            $mysqli->close();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    else {
        echo "Error uploading the file.";
    }
} 
else {
    // Handle the case when no file was uploaded or an error occurred
    $errorCode = $_FILES['excelFile']['error'];

    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            // File size exceeds the maximum allowed size
            echo "Error: The uploaded file size is too large.";
            break;
        case UPLOAD_ERR_PARTIAL:
            // The file was only partially uploaded
            echo "Error: The uploaded file was only partially uploaded.";
            break;
        case UPLOAD_ERR_NO_FILE:
            // No file was uploaded
            echo "Error: No file was uploaded.";
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            // Missing temporary folder
            echo "Error: Missing temporary folder.";
            break;
        case UPLOAD_ERR_CANT_WRITE:
            // Failed to write file to disk
            echo "Error: Failed to write file to disk.";
            break;
        case UPLOAD_ERR_EXTENSION:
            // A PHP extension stopped the file upload
            echo "Error: File upload stopped by a PHP extension.";
            break;
        default:
            // Unknown error
            echo "Error: An unknown error occurred during file upload.";
            break;
    }
}
header("Location: ../theory/MAIN.php");
exit;
}

else {
    header("Location: ../theory/MAIN.php");
    exit;
}


?>