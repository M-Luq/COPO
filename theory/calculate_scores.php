<?php
session_start();
include (__DIR__.'/../database.php');
$submittedMarks = $_SESSION['marks'];
 $submittedStudentMarks = $_SESSION['student_marks'];
$submissionTableName = $_SESSION['submission'];
$assignmentTableName = $_SESSION['assignment'];
$stdnamelist = $_SESSION['stdnamelist'];
$num_assessments = $_SESSION['num_assessments'];
$num_assignments = $_SESSION['num_assignments'];
$co_no = $_SESSION['co_no'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Process marks for assessment questions
foreach ($submittedMarks as $assessment => $assessmentData) {
    foreach ($assessmentData as $studentRegNo => $studentMarks) {
        foreach ($studentMarks as $questionId => $mark) {
            // Fetch subdivision category of the question from the database
            $sql_category = "SELECT subdivision_category FROM $submissionTableName WHERE id = $questionId";
            $result_category = $conn->query($sql_category);
            $row_category = $result_category->fetch_assoc();
            $category = $row_category['subdivision_category'];

            if (!isset($categoryScores[$category][$studentRegNo][$assessment])) {
                $categoryScores[$category][$studentRegNo][$assessment] = 0;
            }
            $categoryScores[$category][$studentRegNo][$assessment] += $mark;
        }
    }
}

// Fetch student names and registration numbers
$sql_students = "SELECT REGNO, STDNAME FROM $stdnamelist"; // Replace with your actual student table name
$result_students = $conn->query($sql_students);
$studentNames = array();
if ($result_students->num_rows > 0) {
    while ($row_student = $result_students->fetch_assoc()) {
        $studentRegNo = $row_student['REGNO'];
        $studentName = $row_student['STDNAME'];
        $studentNames[$studentRegNo] = $studentName;
    }
}

// Fetch assessment names
$sql_assessments = "SELECT DISTINCT assessment FROM $submissionTableName";
$result_assessments = $conn->query($sql_assessments);
$assessmentNames = array();
if ($result_assessments->num_rows > 0) {
    while ($row = $result_assessments->fetch_assoc()) {
        $assessmentNames[] = $row['assessment'];
    }
}

$allCategories = array();

for ($i = 1; $i <= $co_no; $i++) {
    $category = "co" . $i;
    $allCategories[] = $category;
}

$uniqueId = "assessment_" . time();
$_SESSION['uniqueid'] = $uniqueId;
$createTableSQL = "CREATE TABLE $uniqueId (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_no INT,
    student_regno VARCHAR(255),
";

foreach ($allCategories as $category) {
    $createTableSQL .= "`$category` DECIMAL(10,2) DEFAULT 0, ";
}

$createTableSQL = rtrim($createTableSQL, ", "); // Remove the trailing comma and space
$createTableSQL .= ")";

if ($conn->query($createTableSQL) !== TRUE) {
    echo "Error creating assessment table: " . $conn->error;
}

foreach ($assessmentNames as $assessment) {
    $sino = 1;

    foreach ($studentNames as $studentRegNo => $studentName) {
        $insertRowSQL = "INSERT INTO $uniqueId (assessment_no, student_regno, ";

        foreach ($allCategories as $category) {
            $totalMarks = isset($categoryScores[$category][$studentRegNo][$assessment])
                ? $categoryScores[$category][$studentRegNo][$assessment]
                : 0;

            $insertRowSQL .= "`$category`, ";
        }

        $insertRowSQL = rtrim($insertRowSQL, ", "); // Remove the trailing comma and space
        $insertRowSQL .= ") VALUES ('$assessment', '$studentRegNo', ";

        foreach ($allCategories as $category) {
            $totalMarks = isset($categoryScores[$category][$studentRegNo][$assessment])
                ? $categoryScores[$category][$studentRegNo][$assessment]
                : 0;

            $insertRowSQL .= "$totalMarks, ";
        }

        $insertRowSQL = rtrim($insertRowSQL, ", "); // Remove the trailing comma and space
        $insertRowSQL .= ")";

        if ($conn->query($insertRowSQL) !== TRUE) {
            echo "Error inserting assessment data: " . $conn->error;
        }
    }
}


// Similar approach for assignments
// ...

$conn->close();


if($num_assignments != 0){
$assignmentTableName = $_SESSION['assignment'];
$stdnamelist = $_SESSION['stdnamelist'];


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_assignments = "SELECT DISTINCT ASSIGNMENT_NO FROM $assignmentTableName";
$result_assignments = $conn->query($sql_assignments);
$assignmentNames = array();
if ($result_assignments->num_rows > 0) {
    while ($row = $result_assignments->fetch_assoc()) {
        $assignmentNames[] = $row['ASSIGNMENT_NO'];
    }
}

// Create a single table for assignment marks
$uniqueId2 = "assignments_" . uniqid();
$_SESSION['uniqueid2'] = $uniqueId2;
$createTableSQL = "CREATE TABLE $uniqueId2 (id INT AUTO_INCREMENT PRIMARY KEY,assignment_no INT, student_regno VARCHAR(255), ";

$categories = array();
for ($i = 1; $i <= $co_no; $i++) {
    $category = $i;
    $categories[] = $category;
}

foreach ($categories as $category) {
    $createTableSQL .= "`co$category` DECIMAL(10,2) DEFAULT 0, ";
}

$createTableSQL = rtrim($createTableSQL, ", "); // Remove the trailing comma and space
$createTableSQL .= ")";

if ($conn->query($createTableSQL) !== TRUE) {
    echo "Error creating assignment table: " . $conn->error;
}

foreach ($assignmentNames as $assignment) {
    // Insert student assignment marks
    foreach ($submittedStudentMarks as $studentRegNo => $studentAssignmentMarks) {
        $insertRowSQL = "INSERT INTO $uniqueId2 (assignment_no, student_regno";

        $valuesSQL = "('$assignment', '$studentRegNo'";

        foreach ($categories as $category) {
            if (isset($studentAssignmentMarks[$category][$assignment])) {
                $mark = $studentAssignmentMarks[$category][$assignment];

                // Skip columns with a maximum mark of zero
                if ($mark > 0) {
                    $insertRowSQL .= ", `co$category`";
                    $valuesSQL .= ", $mark";
                }
            }
        }

        $insertRowSQL .= ")";
        $valuesSQL .= ")";

        $insertRowSQL .= " VALUES $valuesSQL";

        if ($conn->query($insertRowSQL) !== TRUE) {
            echo "Error inserting assignment data: " . $conn->error;
        }
    }
}




}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Student Data Table</title>
    <script src="../javascript/student.js"></script>
    <link href="../css/style_scores.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
</head>

<body>
<h1>Student List Preview</h1>
<div class="container">
<div class="table-center">


<?php 
$n = $num_assessments; 
for ($assessmentNumber = 1; $assessmentNumber <= $n; $assessmentNumber++) {
    echo '<h2 class="table-title">Assessment ' . $assessmentNumber . '</h2>';
    echo '<table border="1">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>SINO</th>';
    echo '<th>REGNO</th>';
    echo '<th>NAME</th>';
    for ($i = 1; $i <= $co_no; $i++) {
    echo '<th> CO' . $i . '</th>';
    }
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

        // Create a database connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check if the connection was successful
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch the count of students
        $sino_count = 0;
        $result = $conn->query("SELECT COUNT(*) FROM $stdnamelist");
        if ($result) {
            $row = $result->fetch_assoc();
            $sino_count = $row['COUNT(*)'];
        }

        // Fetch student data
        $student_data = [];
        $result = $conn->query("SELECT REGNO, STDNAME FROM $stdnamelist");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $student_data[] = $row;
            }
        }
        
       

        $column_names = [];
        $result = $conn->query("DESCRIBE $uniqueId"); // Use the dynamic table name
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $column_names[] = $row['Field'];
            }
        }

        // Generate the SQL query to select columns based on their existence
        $sql = "SELECT student_regno";
        foreach ($column_names as $column_name) {
            if (strpos($column_name, 'co') === 0) { // Check if the column starts with 'co'
                $sql .= ", $column_name";
            }
        }
        $sql .= " FROM $uniqueId WHERE assessment_no = $assessmentNumber";

        $marks_data = [];
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $marks_data[] = $row;
            }
        }
        
        // Close the database connection
        $conn->close();
        
        // Generate table rows
        for ($i = 0; $i < $sino_count; $i++) {
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            echo "<td>{$student_data[$i]['REGNO']}</td>";
            echo "<td>{$student_data[$i]['STDNAME']}</td>";
            
            // Loop through co1, co2, co3, co4, co5 and print the marks
            for ($coNumber = 1; $coNumber <= $co_no; $coNumber++) {
                $columnName = 'co' . $coNumber;
                if (isset($marks_data[$i][$columnName])) {
                    $mark = $marks_data[$i][$columnName];
                } else {
                    $mark = number_format(0,2); // Set to zero if the column doesn't exist
                }
                echo "<td>$mark</td>";
            }
            
            echo "</tr>";
        }
        echo '</tbody>';
        echo '</table>';
        
    }?>
    </div>

<div class="table-center">
<?php

$n = $num_assignments; 
for ($assignmentNumber = 1; $assignmentNumber <= $n; $assignmentNumber++) {
    echo '<h2 class="table-title">Assignment ' . $assignmentNumber . '</h2>';
    echo '<table border="1">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>SINO</th>';
    echo '<th>REGNO</th>';
    echo '<th>NAME</th>';
    for ($i = 1; $i <= $co_no; $i++) {
    echo '<th>CO' . $i . '</th>';
    }
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
        

        // Create a database connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check if the connection was successful
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch the count of students
        $sino_count = 0;
        $result = $conn->query("SELECT COUNT(*) FROM $stdnamelist");
        if ($result) {
            $row = $result->fetch_assoc();
            $sino_count = $row['COUNT(*)'];
        }

        // Fetch student data
        $student_data = [];
        $result = $conn->query("SELECT REGNO, STDNAME FROM $stdnamelist");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $student_data[] = $row;
            }
        }
        
       

        $column_names = [];
        $result = $conn->query("DESCRIBE $uniqueId2"); // Use the dynamic table name
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $column_names[] = $row['Field'];
            }
        }

        // Generate the SQL query to select columns based on their existence
        $sql = "SELECT student_regno";
        foreach ($column_names as $column_name) {
            if (strpos($column_name, 'co') === 0) { // Check if the column starts with 'co'
                $sql .= ", $column_name";
            }
        }
        $sql .= " FROM $uniqueId2 WHERE assignment_no = $assignmentNumber";

        $marks_data = [];
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $marks_data[] = $row;
            }
        }
        
        // Close the database connection
        $conn->close();
        
        // Generate table rows
        for ($i = 0; $i < $sino_count; $i++) {
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            echo "<td>{$student_data[$i]['REGNO']}</td>";
            echo "<td>{$student_data[$i]['STDNAME']}</td>";
            
            // Loop through co1, co2, co3, co4, co5 and print the marks
            for ($coNumber = 1; $coNumber <= $co_no; $coNumber++) {
                $columnName = 'co' . $coNumber;
                if (isset($marks_data[$i][$columnName])) {
                    $mark = $marks_data[$i][$columnName];
                } else {
                    $mark = number_format(0,2); // Set to zero if the column doesn't exist
                }
                echo "<td>$mark</td>";
            }
            
            echo "</tr>";
        }
        echo '</tbody>';
        echo '</table>';
    }?>
</div>
</div>
<br>
<div>
<button class="button" onclick="window.location.href = 'sheet.html';">Go to Next Page</button>
</div>
<br>


<script>
$(document).ready(function() {
    // Hide all tables on page load
    $("table").hide();

    
        $(".table-title").click(function () {
            // Toggle the visibility of the next table (the one right after the title)
            var nextTable = $(this).next("table");
            if (!nextTable.is(":visible")) {
                // Close other open tables
                $(".table-title").not(this).next("table").slideUp();
            }
            nextTable.slideToggle();
        });
});
</script>

</body>
</html>

<?php
//hi
?>



