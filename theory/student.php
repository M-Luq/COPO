<?php 
session_start();
include (__DIR__.'/../database.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="" href="../css/style3.css">
<title>Assessment Form</title>
<style>
  .student {
    display: none; /* Hide all students by default */
  }

  body::-webkit-scrollbar {
      width: 0; /* Hide scrollbar for Chrome, Safari, and Opera */
    }
    
    body::-webkit-scrollbar-thumb {
      background-color: transparent; /* Color of the scrollbar thumb */
    }
    
</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
<script src="../javascript/student.js"></script>
<script src="../javascript/nextinput.js"></script>
<script src="../javascript/studentTab.js"></script>
</head>
<body>
  <header>
    <h1>Assessment Form</h1>
  </header>
  
  <div class="container">
  <form  method="post" action = "../headers/navigate3.php">

    <?php
    
    $submissionTableName = $_SESSION["submission"];
    $assignmentTableName = $_SESSION["assignment"];
    $stdnamelist = $_SESSION['stdnamelist'];
    $co_no = $_SESSION['co_no'];

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $_SESSION['assessments'] = array();
    $_SESSION['students'] = array();
    // Fetch assessments from the database

    $sql_students = "SELECT REGNO, STDNAME FROM $stdnamelist";
    $result_students = $conn->query($sql_students);
    echo '<div class="tab assessment-tab" data-tab="assessment">';
            echo '<h2>Assessment</h2>';
    echo '</div>';
           
    if ($result_students->num_rows > 0) {
        while($student_row = $result_students->fetch_assoc()) {
          $student_regno = $student_row["REGNO"];
          $student_name = $student_row["STDNAME"];
          
            
            echo '<div class="student" data-tab="assessment">';
            echo '<h5>Regno: ' . strtoupper($student_row["REGNO"]) .'&nbsp Student: ' . strtoupper($student_row["STDNAME"]) .  '</h5>';

            $sql_assessments = "SELECT DISTINCT assessment FROM $submissionTableName";
            $result_assessments = $conn->query($sql_assessments);
    if ($result_assessments->num_rows > 0) {
        while($assessment_row = $result_assessments->fetch_assoc()) {

            $assessment = $assessment_row["assessment"];
            $_SESSION['assessments'][] = (object) array('name' => $assessment);
            
            echo '<h2>Assessment ' . $assessment . '</h2>';

// Output the "Absent" toggle checkbox for the current student
echo '<input style="padding:10px ;margin:10px;"type="checkbox" id="absent_' . $assessment . '_' . $student_row["REGNO"] . '" name="absent[' . $assessment . '][' . $student_row["REGNO"] . ']" onchange="toggleAbsent(this)"> Absent';
echo '<br>';
// Fetch questions for the current assessment
$sql_questions = "SELECT id, question, subdivision_mark, subdivision_category FROM $submissionTableName WHERE assessment = $assessment";
$result_questions = $conn->query($sql_questions);

if ($result_questions->num_rows > 0) {
    while($row = $result_questions->fetch_assoc()) {
        $maxmark = $row["subdivision_mark"];
        $subdivisioncategory = $row["subdivision_category"];
        echo '<div class="question">';
        echo '<label for="marks_' . $assessment . '_' . $student_row["REGNO"] . '_' . $row["id"] . '">' . $row["question"] . ':</label>';
        echo '<div class="input-container">';
        echo '<input type="number" id="marks_' . $assessment . '_' . $student_row["REGNO"] . '_' . $row["id"] . '" name="marks[' . $assessment . '][' . $student_row["REGNO"] . '][' . $row["id"] . ']" min="-1"  max="' . $maxmark . '" required placeholder=" ' . $maxmark . '" oninput="roundToInteger(this)">';
        echo '<span class="subdivision-category">' . $subdivisioncategory . '</span>';
        echo '</div>';
        echo '</div>';
    }
    echo '<input type="hidden" name="absent[' . $assessment . '][' . $student_row["REGNO"] . ']" value="0">';

} else {
    echo "No questions found.";
}      
                } 
            } 
            else {
                echo "No students found.";
            }echo '</div>';
            
            
            
            
        }
    } else {
        echo "No assessments found.";
    }
    
    
    $num_assignments = $_SESSION['num_assignments'];
    if($num_assignments !=0){
    
    echo '<div class="tab assignment-tab"data-tab="assignment">';
            echo '<h2>Assignment</h2>';
    echo '</div>';
    // Rest of your PHP code for fetching assignment data
    $sql_students = "SELECT REGNO, STDNAME FROM $stdnamelist";
    $result_students = $conn->query($sql_students);

    if ($result_students->num_rows > 0) {
        while ($student_row = $result_students->fetch_assoc()) {
            $student_regno = $student_row["REGNO"];
            $student_name = $student_row["STDNAME"];
            echo '<div class="student" data-tab="assignment">';
            echo '<h5>Regno: ' . strtoupper($student_row["REGNO"]) . '&nbsp Student: ' . strtoupper($student_name) . '</h5>';
     
    if($num_assignments>0){
    for($i=1;$i<=$num_assignments;$i++)
    {
   
    echo "<h2>Assignment $i</h2>";
  
    $sql = "SELECT column_name
        FROM information_schema.columns
        WHERE table_schema = 'copo' 
          AND table_name = '$assignmentTableName' 
          AND column_name LIKE 'co%'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $columns = array(); // To store the matching column names
    
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row["column_name"];
    }

    // Construct a SELECT query with the matching column names
    $selectColumns = implode(", ", $columns);
  }
    
    // Modify your SQL query to select data from the columns
    $sql_assignments = "SELECT $selectColumns FROM $assignmentTableName WHERE ASSIGNMENT_NO = $i";
$result_assignments = $conn->query($sql_assignments);


if ($result_assignments->num_rows > 0) {
  while($row = $result_assignments->fetch_assoc()) {
    // Display CO marks under each category for the student
    for ($co = 1; $co <= $co_no; $co++) {
      $co_mark = $row["CO".$co];
      
      // Check if the maximum mark is greater than zero before displaying the input field
      if ($co_mark > 0) {
        echo '<div class="question">';
        echo '<label for="student_marks['.$student_regno.']['.$co.']['.$i.']">CO'.$co.':</label>';
        echo '<div class="input-container">';
        echo '<input type="number" id="student_marks['.$student_regno.']['.$co.']['.$i.']""  name="student_marks['.$student_regno.']['.$co.']['.$i.']"  min="0" max ="'.$co_mark.'"  required placeholder=" ' . $co_mark . '">';
        echo '</div>';
        echo '</div>';
      }
    }
  }
}else {
      echo "No assignment question found for this student.";
      }
          }

            
    } else {
        echo "No students found.";
    }
    echo '</div>';
            
  }} else {
    echo "No assessments found.";
}
    }

    echo '<div class="tab university-tab" data-tab="university">';
    echo '<h2>University Result</h2>';
    echo '</div>';
    // $sql_students = "SELECT REGNO, STDNAME FROM $stdnamelist";
    // $result_students = $conn->query($sql_students);
    
    echo '<div class="student" data-tab="university">';
    // if ($result_students->num_rows > 0) {
    //     while ($student_row = $result_students->fetch_assoc()) 
    //     {
    //         $student_regno = $student_row["REGNO"];
    //         $student_name = $student_row["STDNAME"];
    //         $student_count = $result_students->num_rows;
            
            // echo '<h5>Regno: ' . strtoupper($student_row["REGNO"]) . '&nbsp &nbsp Student: ' . strtoupper($student_name) . '</h5>';
              echo '<div class="question">';
                  echo '<label for="no_of_failures">No of Failures:</label>';
                  echo '<div class="input-container">';
                  echo '<input type = "number" id = "no_of_failures" name = "no_of_failures" required >';
                  echo '</div>';
              echo '</div>';
     
         echo '</div>';
    $conn->close();

    
echo '</div>';
?>
<br>
<div class="button">
      <button type="submit">S U B M I T</button>
</div>
    </form> 
  </div> 
</body>
<script>
  function roundToInteger(input) {
    var value = parseFloat(input.value); // Parse the input value as a float
    var roundedValue = Math.floor(value); // Round down to the nearest integer
    input.value = roundedValue; // Set the input field value to the rounded integer
}
  </script>
 <script>
function toggleAbsent(checkbox) {
    var assessmentId = checkbox.id.split("_")[1]; // Extract assessment ID
    var studentId = checkbox.id.split("_")[2]; // Extract student ID

    // Loop through all input fields for the current assessment and student
    var inputFields = document.querySelectorAll('[id^="marks_' + assessmentId + '_' + studentId + '"]');
    inputFields.forEach(function(inputField) {
        if (checkbox.checked) {
            inputField.value = "-1";
        } else {
            inputField.value = "";
        }
    });
    var hiddenInput = checkbox.parentNode.querySelector('input[type="hidden"]');
    if (checkbox.checked) {
        hiddenInput.value = 1; // Student is absent
    } else {
        hiddenInput.value = 0; // Student is not absent
    }
}


</script>

</script>

</html>
