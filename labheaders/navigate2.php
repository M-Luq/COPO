<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
if (isset($_SESSION['num_mid_sem'])) {
        $num_mid_sem = $_SESSION['num_mid_sem'];
    }
    if (isset($_SESSION['num_experiments'])) {
        $num_experiments = $_SESSION['num_experiments'];
    }
    for ($i = 1; $i <= $num_mid_sem; $i++) {
        $input_name = "num_questions_$i";
        if (isset($_POST[$input_name])) {
            $_SESSION[$input_name] = $_POST[$input_name];
        }
    }
    header("Location: ../lab/question3.php");
    exit;
}
else{
    if (isset($_SESSION['num_mid_sem'])) {
        $num_mid_sem = $_SESSION['num_mid_sem'];
    }
    if (isset($_SESSION['num_experiments'])) {
        $num_experiments = $_SESSION['num_experiments'];
    }
    for ($i = 1; $i <= $num_mid_sem; $i++) {
        $input_name = "num_questions_$i";
        if (isset($_SESSION[$input_name])) {
            $_SESSION[$input_name] = $_SESSION[$input_name];
        }
    }
    header("Location: ../lab/question3.php");
    exit;
    
}

?>