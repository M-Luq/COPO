<?php
session_start();
include (__DIR__.'/../database.php');

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submissionTableName = "submission_" . time();
    $assignmentTableName = "assignment_" . time(); 
    // Create a new table name using timestamp
    $_SESSION['assignment'] = $assignmentTableName;
    $_SESSION['submission'] = $submissionTableName;
    $co_no = $_SESSION['co_no'];
    $num_assessments = $_SESSION['num_assessments'];
    $num_assignments = $_SESSION['num_assignments'];
    // Create a new table for this submission
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS $submissionTableName (
            id INT AUTO_INCREMENT PRIMARY KEY,
            assessment INT NOT NULL,
            question VARCHAR(255) NOT NULL,
            subdivision_mark INT NOT NULL,
            subdivision_category VARCHAR(255) NOT NULL
        )";

    try {
        $pdo->exec($createTableSQL);
    } catch (PDOException $e) {
        die("Table creation failed: " . $e->getMessage());
    }
    
    $createTableSQL2 = "CREATE TABLE IF NOT EXISTS $assignmentTableName (
    ASSIGNMENT_NO INT NOT NULL,";

// Generate columns for CO1, CO2, ... based on $co_no
    for ($co = 1; $co <= $co_no; $co++) {
    $createTableSQL2 .= "CO$co INT NOT NULL";

    // Add a comma after each column except the last one
    if ($co < $co_no) {
        $createTableSQL2 .= ",";
    }
}

$createTableSQL2 .= ")";



    try {
        $pdo->exec($createTableSQL2);
    } catch (PDOException $e) {
        die("Table creation failed: " . $e->getMessage());
    }

    for ($i = 1; $i <= $num_assessments; $i++) {
        $sessionqn = "num_questions_$i";
        $num_questions = $_SESSION[$sessionqn];
        for ($q = 1; $q <= $num_questions; $q++) {
            $numSubdivisions = $_POST["num_subdivisions_${i}_${q}"];
            $questionIdentifier = "${q}";

            if ($numSubdivisions > 1) {
                for ($s = 1; $s <= $numSubdivisions; $s++) {
                    $subIdentifier = chr(96 + $s); // Convert $s to 'a', 'b', 'c', ...
                    $fullQuestionIdentifier = $questionIdentifier . $subIdentifier;
                    if(isset($_POST['num_assessments'])) {
                        $_SESSION['num_assessments'] = $_POST['num_assessments']; // Store num_assessments in the session
                    }
                    $markWeightage = $_POST["subdivision_mark_${i}_${q}_${s}"];
                    $category = $_POST["subdivision_category_${i}_${q}_${s}"];
    
                    // Validate and sanitize input data
                    $markWeightage = filter_var($markWeightage, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $category = filter_var($category, FILTER_SANITIZE_STRING);
    
                    // Prepare and execute the SQL statement
                    $insertSQL = "INSERT INTO $submissionTableName (assessment, question, subdivision_mark, subdivision_category) VALUES (?, ?, ?, ?)";
                    $stmt = $pdo->prepare($insertSQL);
                    $stmt->execute([$i, $fullQuestionIdentifier, $markWeightage, $category]);
                }
            } else {
                // Handle the case when there are no subdivisions
                $markWeightage = $_POST["subdivision_mark_${i}_${q}"];
                $category = $_POST["subdivision_category_${i}_${q}"];

    
                // Validate and sanitize input data
                $markWeightage = filter_var($markWeightage, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $category = filter_var($category, FILTER_SANITIZE_STRING);
    
                // Prepare and execute the SQL statement
                $insertSQL = "INSERT INTO $submissionTableName (assessment, question, subdivision_mark, subdivision_category) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($insertSQL);
                $stmt->execute([$i, $questionIdentifier, $markWeightage, $category]);
            }
        }
    }
    
        


    for ($i = 1; $i <= $num_assignments; $i++) {
        // Loop through CO inputs
        $coMarks = array();
        for ($k = 1; $k <= $co_no; $k++) {
            $coMarks[$k] = $_POST["co${k}_${i}_mark"];
        }
    
        // Build the INSERT SQL query dynamically
        $insertSQL = "INSERT INTO $assignmentTableName (ASSIGNMENT_NO, ";
        for ($k = 1; $k <= $co_no; $k++) {
            $insertSQL .= "CO$k";
    
            // Add a comma after each column except the last one
            if ($k < $co_no) {
                $insertSQL .= ",";
            }
        }
        $insertSQL .= ") VALUES (?, ";
        for ($k = 1; $k <= $co_no; $k++) {
            $insertSQL .= "?";
    
            // Add a comma after each parameter except the last one
            if ($k < $co_no) {
                $insertSQL .= ",";
            }
        }
        $insertSQL .= ")";
        
        // Prepare and execute the INSERT statement
        $stmt = $pdo->prepare($insertSQL);
        $params = array_merge([$i], $coMarks);
        $stmt->execute($params);
    }
    
}
header("Location: student.php");
exit();

?>
