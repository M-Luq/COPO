<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
if (isset($_SESSION['num_assessments'])) {
        $num_assessments = $_SESSION['num_assessments'];
    }
    if (isset($_SESSION['num_assignments'])) {
        $num_assignments = $_SESSION['num_assignments'];
    }
    for ($i = 1; $i <= $num_assessments; $i++) {
        $input_name = "num_questions_$i";
        if (isset($_POST[$input_name])) {
            $_SESSION[$input_name] = $_POST[$input_name];
        }
    }
    header("Location: ../theory/question3.php");
    exit;
}
else{
    if (isset($_SESSION['num_assessments'])) {
        $num_assessments = $_SESSION['num_assessments'];
    }
    if (isset($_SESSION['num_assignments'])) {
        $num_assignments = $_SESSION['num_assignments'];
    }
    for ($i = 1; $i <= $num_assessments; $i++) {
        $input_name = "num_questions_$i";
        if (isset($_SESSION[$input_name])) {
            $_SESSION[$input_name] = $_SESSION[$input_name];
        }
    }
    header("Location: ../theory/question3.php");
    exit;
    
}

?>