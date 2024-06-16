<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
session_start();
// include (__DIR__.'/../database.php');
$submittedMarks = $_POST['marks'];
$_SESSION['marks'] = $_POST['marks'];

$_SESSION['absent'] = $_POST['absent'];

// Establish database connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// $uniqueId3 = "grades_" . time();
// $_SESSION['uniqueid3'] = $uniqueId3;

// // Create the table if it doesn't exist
// $create_table_query = "CREATE TABLE IF NOT EXISTS $uniqueId3 (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     student_regno VARCHAR(255) NOT NULL,
//     grade VARCHAR(1) NOT NULL
// )";
// if ($conn->query($create_table_query) === FALSE) {
//     echo "Error creating table: " . $conn->error . "<br>";
// }

// foreach ($_POST as $key => $value) {
//     if (strpos($key, "student_grade_mark-") === 0) {
//         $studentRegNo = substr($key, strlen("student_grade_mark-"));
//         $grade = $conn->real_escape_string($value);
        
//         $insert_query = "INSERT INTO $uniqueId3 (student_regno, grade) VALUES ('$studentRegNo', '$grade')";
        

//         if ($conn->query($insert_query) === FALSE) {
//             echo "Error inserting record for student $studentRegNo: " . $conn->error . "<br>";
//         }
       
//         // Store the data in a session variable
//        /* $_SESSION['student_grades'][] = array(
//             'student_regno' => $studentRegNo,
//             'grade' => $grade
//         );*/
//     }
// }

// $conn->close();
$num_failures = filter_input(INPUT_POST, 'no_of_failures', FILTER_VALIDATE_INT);
if ($num_failures === false || $num_failures < 0) {
    echo "Invalid input for number of failures.";
    exit;
}


// Store the number of failures in a session variable
$_SESSION['num_failures'] = $num_failures;

if (isset($_POST['student_marks']) && is_array($_POST['student_marks'])) {
    $submittedStudentMarks = $_POST['student_marks'];
    $_SESSION['student_marks'] = $_POST['student_marks'];
} else {
    $submittedStudentMarks = array();
    $_SESSION['student_marks'] = $submittedStudentMarks;
}



header("Location: ../lab/calculate_scores.php");
exit;
}
else{
    header("Location: ../lab/calculate_scores.php");
    exit;

}

?>