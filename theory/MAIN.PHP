<?php
session_start();
require (__DIR__.'/../vendor/autoload.php');
include (__DIR__.'/../database.php');

use PhpOffice\PhpSpreadsheet\IOFactory;


$uploadFile = $_SESSION["file_name"];

// Load the Excel spreadsheet
$spreadsheet = IOFactory::load($uploadFile);

// Select the first worksheet
$worksheet = $spreadsheet->getActiveSheet();

// Get the highest row number containing data
$highestRow = $worksheet->getHighestRow();
$highestColumn = $worksheet->getHighestColumn();
$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); 

$sinoColumn = 'A';

// to get the total students
$count = 0;
// Loop through each row and count the values in the "SI_NO" column
for ($row = 5; $row <= $highestRow; $row++) {
    $siNoValue = $worksheet->getCell($sinoColumn.$row)->getValue();
    
    // Check if the cell is not empty
    if ($siNoValue !== null) {
        $count++;
    }
}
$subject_code=$_SESSION['subject_code'];

$occurrencesCat = 0;

$occurrencesAssign = 0;

$occurrenceco = 0;


// Iterate through each cell in the active sheet
for ($row = 1; $row <= $highestRow; $row++) {
    for ($column = 1; $column <= $highestColumnIndex; $column++) {
        $cellValue = trim($worksheet->getCellByColumnAndRow($column, $row)->getValue()); // Trim whitespace
            
        // Check if the cell value is not empty
        if ($cellValue !== null && $cellValue !== '') {
            // Perform comparisons
            if (preg_match('/\bCAT \d\b/', $cellValue) &&stripos($cellValue, 'CAT') !== false) {
                $occurrencesCat++;
            }

            if (preg_match('/\bASSIGNMENT \d\b/', $cellValue) && stripos($cellValue, 'ASSIGNMENT')!== false) {
                $occurrencesAssign++;
            }

            if (preg_match('/\bCO\d\b/', $cellValue) && stripos($cellValue, 'CO')!== false) {
                $occurrenceco++;
            }
        }
    }
}



$occurrenceco = ($occurrenceco /($occurrencesAssign+$occurrencesCat));


$_SESSION['co_no'] = $occurrenceco ;

$data = [];
foreach ($worksheet->getRowIterator() as $row) {
    $rowData = [];
    foreach ($row->getCellIterator() as $cell) {
        $rowData[] = $cell->getValue();
    }
    $data[] = $rowData;
}
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$cat = $occurrencesCat; 
$assignmentCount = $occurrencesAssign; 

$_SESSION['num_assessments'] = $cat ;
$_SESSION['num_assignments'] = $assignmentCount ;


// Determine the total number of CO columns in the Excel sheet
$totalCOColumns = $occurrenceco; 
$startColumnIndex = 4;


        $uniqueId = "assessment_" . time();
        $_SESSION['uniqueid'] = $uniqueId;
        $createTableSQL = "CREATE TABLE $uniqueId (
        id INT AUTO_INCREMENT PRIMARY KEY,
        assessment_No INT,
        student_regno VARCHAR(255),";

    // Generate columns for COs dynamically based on totalCOColumns
    for ($co = 1; $co <= $totalCOColumns; $co++) {
        $createTableSQL .= "co$co DECIMAL(10,2), ";
    }

    // Remove the trailing comma and add closing parentheses
    $createTableSQL = rtrim($createTableSQL, ', ') . ')';

    if ($conn->query($createTableSQL) !== TRUE) {
        echo "Error creating table: " . $conn->error;
    }

// Iterate through assessments and assignments
for ($ast = 1; $ast <= $cat; $ast++) {
        

    // Initialize an array to store CO values
    $coValues = array();

    // Iterate through students (starting from the 5th row)
    for ($j = 4; $j < count($data); $j++) {
        $assessmentNo = $ast;
        $regNo = $data[$j][1]; // Reg No is in the 2nd column for each student

        $coValues = array_slice($data[$j], $startColumnIndex, $totalCOColumns);

        // Construct the INSERT SQL dynamically for CO columns
        $coColumns = implode(", ", array_map(function ($index) {
            return "co$index";
        }, range(1, $totalCOColumns)));

        $coValuesString = implode("', '", $coValues);

        $sql = "INSERT INTO $uniqueId (assessment_no, student_regno, $coColumns) 
                VALUES ('$assessmentNo', '$regNo', '$coValuesString')";

        if ($conn->query($sql) !== TRUE) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Increment the startColumnIndex for the next assessment
    $startColumnIndex += $totalCOColumns;
}

$uniqueId2 = "assignments_" . uniqid();
$_SESSION['uniqueid2'] = $uniqueId2;
$createTableSQL = "CREATE TABLE $uniqueId2 (id INT AUTO_INCREMENT PRIMARY KEY,assignment_no VARCHAR(255), student_regno VARCHAR(255), ";
        
        // Generate columns for assignment dynamically based on assignmentCount
        for ($assignment = 1; $assignment <= $totalCOColumns; $assignment++) {
            $createTableSQL .= "co$assignment DECIMAL(10,2), ";
        }

        // Remove the trailing comma and add closing parentheses
        $createTableSQL = rtrim($createTableSQL, ', ') . ')';

        if ($conn->query($createTableSQL) !== TRUE) {
            echo "Error creating table: " . $conn->error;
        }

    // If there are assignments, process them
    for ($ast = 1; $ast <= $assignmentCount; $ast++) {
        

        // Initialize an array to store assignment values
        $assignmentValues = array();

        // Iterate through students (starting from the 5th row)
        for ($j = 4; $j < count($data); $j++) {
            $assignmentNo = $ast;
            $regNo = $data[$j][1]; // Reg No is in the 2nd column for each student

            $assignmentValues = array_slice($data[$j], $startColumnIndex, $totalCOColumns);

            // Construct the INSERT SQL dynamically for assignment columns
            $assignmentColumns = implode(", ", array_map(function ($index) {
                return "co$index";
            }, range(1, $totalCOColumns)));

            $assignmentValuesString = implode("', '", $assignmentValues);

            $sql = "INSERT INTO $uniqueId2 (assignment_no, student_regno, $assignmentColumns) 
                    VALUES ('$assignmentNo', '$regNo', '$assignmentValuesString')";

            if ($conn->query($sql) !== TRUE) {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        // Increment the startColumnIndex for the next assignment
        $startColumnIndex += $totalCOColumns;
    }

$conn->close();


// Initialize the sets array
$sets = [];

// Define the starting column index
$startColumnIndex = 5; // Column E

// Define the number of CAT categories
$catCategories = $occurrencesCat;
function getColumnLetter($index) {
    $columnName = '';
    while ($index > 0) {
        $remainder = ($index - 1) % 26;
        $columnName = chr(65 + $remainder) . $columnName;
        $index = intdiv($index - $remainder, 26);
    }
    return $columnName;
}

// Define the range for CAT categories
for ($i = 1; $i <= $catCategories; $i++) {
    $endColumnIndex = $startColumnIndex + $occurrenceco - 1; // Columns E to I
    $sets[] = [
        'startColumn' => getColumnLetter($startColumnIndex),
        'endColumn' => getColumnLetter($endColumnIndex),
        'startMaxmark' => getColumnLetter($startColumnIndex) . '4',
        'endMaxmark' => getColumnLetter($endColumnIndex) . '4',
        'category' => 'CAT ' . $i,
    ];

    // Move to the next set of columns
    $startColumnIndex = $endColumnIndex + 1;
}

// Define the number of ASSIGNMENTS categories
$assignmentsCategories = $occurrencesAssign;

// Define the range for ASSIGNMENTS categories
for ($i = 1; $i <= $assignmentsCategories; $i++) {
    $endColumnIndex = $startColumnIndex + $occurrenceco - 1; // Columns T to X
    $sets[] = [
        'startColumn' => getColumnLetter($startColumnIndex),
        'endColumn' => getColumnLetter($endColumnIndex),
        'startMaxmark' => getColumnLetter($startColumnIndex) . '4',
        'endMaxmark' => getColumnLetter($endColumnIndex) . '4',
        'category' => 'ASSIGNMENT ' . $i,
    ];

    // Move to the next set of columns
    $startColumnIndex = $endColumnIndex + 1;
}

?>

<?php
function columnToIndex($column) {
    $index = 0;
    $length = strlen($column);
    for ($i = 0; $i < $length; $i++) {
        $index *= 26;
        $index += ord($column[$i]) - 64;
    }
    return $index - 1;
}
function columnLetterToIndex($letter) {
    $index = 0;
    $letter = strtoupper($letter); // Convert to uppercase to handle both lowercase and uppercase letters
    
    // Loop through each character in reverse order
    for ($i = strlen($letter) - 1, $pow = 0; $i >= 0; $i--, $pow++) {
        $char = $letter[$i];
        $charValue = ord($char) - ord('A') + 1; // Get the character's value (A=1, B=2, ..., Z=26)
        $index += $charValue * (26 ** $pow); // Calculate the index
    }
    
    return $index - 1; // Subtract 1 to get the 0-based index (A=0, B=1, ..., Z=25)
}

function indexToColumn($index) {
    $column = '';
    $index += 1;
    while ($index > 0) {
        $remainder = ($index - 1) % 26;
        $column = chr($remainder + 65) . $column;
        $index = ($index - $remainder - 1) / 26;
    }
    return $column;
}
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}


   
?>

<?php
require (__DIR__.'/../vendor/autoload.php'); // Include the PhpSpreadsheet autoloader


//use PhpOffice\PhpSpreadsheet\IOFactory;
  $uploadFile = $_SESSION["file_name"];
            // Load the Excel spreadsheet
            $spreadsheet = IOFactory::load($uploadFile);
            $worksheet = $spreadsheet->getActiveSheet(0);

            // Get the highest row number containing data
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            $sinoColumn = 'A';

            // Count the number of students
            $count = 0;
            for ($row = 5; $row <= $highestRow; $row++) {
                $siNoValue = $worksheet->getCell($sinoColumn . $row)->getValue();
                if ($siNoValue !== null) {
                    $count++;
                }
            }

            $occurrencesCat = 0;

            $occurrencesAssign = 0;
            
            $occurrenceco = 0;
            
            
            // Iterate through each cell in the active sheet
            for ($row = 1; $row <= $highestRow; $row++) {
                for ($column = 1; $column <= $highestColumnIndex; $column++) {
                    $cellValue = trim($worksheet->getCellByColumnAndRow($column, $row)->getValue()); // Trim whitespace
                        
                    // Check if the cell value is not empty
                    if ($cellValue !== null && $cellValue !== '') {
                        // Perform comparisons
                        if (preg_match('/\bCAT \d\b/', $cellValue) &&stripos($cellValue, 'CAT') !== false) {
                            $occurrencesCat++;
                        }
            
                        if (preg_match('/\bASSIGNMENT \d\b/', $cellValue) && stripos($cellValue, 'ASSIGNMENT')!== false) {
                            $occurrencesAssign++;
                        }
            
                        if (preg_match('/\bCO\d\b/', $cellValue) && stripos($cellValue, 'CO')!== false) {
                            $occurrenceco++;
                        }
                    }
                }
            }
    
    
    $occurrenceco = ($occurrenceco /($occurrencesAssign+$occurrencesCat));
            
$combinations = [];

// Define the starting column index
$startColumnIndex = 5; // Column E

// Define the number of CAT categories
$catCategories = $occurrencesCat;


// Define the range for CAT categories
for ($i = 1; $i <= $catCategories; $i++) {
    $endColumnIndex = $startColumnIndex + $occurrenceco -1; // Columns E to I
    $combinations[] = [
        'startColumn' => getColumnLetter($startColumnIndex),
        'endColumn' => getColumnLetter($endColumnIndex),
        'startMaxmark' => getColumnLetter($startColumnIndex) . '4',
        'endMaxmark' => getColumnLetter($endColumnIndex) . '4',
        'category' => 'CAT ' . $i,
    ];

    // Move to the next set of columns
    $startColumnIndex = $endColumnIndex + 1;
}

// Define the number of ASSIGNMENTS categories
$assignmentsCategories = $occurrencesAssign;

// Define the range for ASSIGNMENTS categories
for ($i = 1; $i <= $assignmentsCategories; $i++) {
    $endColumnIndex = $startColumnIndex + $occurrenceco -1; // Columns T to X
    $combinations[] = [
        'startColumn' => getColumnLetter($startColumnIndex),
        'endColumn' => getColumnLetter($endColumnIndex),
        'startMaxmark' => getColumnLetter($startColumnIndex) . '4',
        'endMaxmark' => getColumnLetter($endColumnIndex) . '4',
        'category' => 'ASSIGNMENT ' . $i,
    ];

    // Move to the next set of columns
    $startColumnIndex = $endColumnIndex + 1;
}

// $columnTotalMarks = [];
// foreach ($combinations as $key => $combination) {
    
//     $columnTotalMarks[$key] = [];
//     for ($column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($combination['startColumn']); $column <= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($combination['endColumn']); $column++) {
//             $totalMarks = 0;
//             $row=4;
//             $cellValue = $worksheet->getCellByColumnAndRow($column, $row)->getValue();
//             $totalMarks += $cellValue;
        
//         $columnTotalMarks[$key][\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column)] = $totalMarks;
        
//     }
// }

// $startColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('E');

// $totalCombinationMarks1 = [];
// $n = $occurrencesAssign+$occurrencesCat;

// for($i=0;$i<$occurrenceco;$i++)
// {
// if($i != 0){    
// $startColumn = $startColumn + 1;
// }
// $totalMarks2 = 0; 
// for ($iteration = 0; $iteration < $n; $iteration++)
//  {
//     if($iteration == 0 && $i == 0){
//         $currentColumn = $startColumn;
//     }
//      else{   
//         $currentColumn = $startColumn + ($iteration * $occurrenceco);
//      }
// $row=4;
// $cellValue = $worksheet->getCellByColumnAndRow($currentColumn, $row)->getValue();
// $totalMarks2 += $cellValue;

// }
// $totalCombinationMarks1[$i] = $totalMarks2;
// }


if (isset($sets,$combinations)) {
    // Generate a unique table name for each user
    $tableName = 'coas_' . uniqid();
    $_SESSION['t_name1'] = $tableName;
    try {
        // Create a new table for the user's data
        $createTableQuery = "CREATE TABLE $tableName (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category VARCHAR(255)";
        for ($i = 1; $i <= $occurrenceco; $i++) { 
            $createTableQuery .= ", co$i DECIMAL(10,2)";
        }
        $createTableQuery .= ")";
        $stmt = $conn->prepare($createTableQuery);
        $stmt->execute();

        // Prepare the SQL query to insert the row data
        $insertQuery = "INSERT INTO $tableName (category, ";
        for ($i = 1; $i <= $occurrenceco; $i++) {
            $insertQuery .= "co$i, ";
        }
        $insertQuery = rtrim($insertQuery, ", "); // Remove the trailing comma and space
        $insertQuery .= ") VALUES (:category, ";

        for ($i = 1; $i <= $occurrenceco; $i++) {
            $insertQuery .= ":co$i, ";
        }
        $insertQuery = rtrim($insertQuery, ", ");
        $insertQuery .=  ")";
        

        foreach ($sets as $set) {
            $startColumn = $set['startColumn'];
            $endColumn = $set['endColumn'];
            $startMaxmark = $worksheet->getCell($set['startMaxmark'])->getValue();
            $endMaxmark = $worksheet->getCell($set['endMaxmark'])->getValue();
            $category = $set['category'];

   

    // Store the CO values in the rowValues array
    $rowValues = array(
        'category' => $category
    );
    
    $startIndex = columnToIndex($startColumn);
    $endIndex = columnToIndex($endColumn);
    for ($j = $startIndex; $j <= $endIndex; $j++) {
        $column = indexToColumn($j);
        $maxmark = $worksheet->getCell($column.'4')->getValue();

        $columnSum = 0;
        $countAbsent=0;
        for ($i = 5; $i <= $highestRow; $i++) { 
            $columnValue = $worksheet->getCell($column.$i)->getValue();
            if($columnValue >= 0){
            $columnSum += $columnValue;
            }
            else{
                $countAbsent++;
            }
        }
    
        if ($maxmark != 0) {
            $average = ($columnSum / (($count-$countAbsent) * $maxmark)) * 100;
            $average = round($average, 1);
        } else {
            $average = null; 
        }

        // Store the CO values in the rowValues array
        $rowValues['co' . ($j - columnToIndex($startColumn) + 1)] = $average;
    }
    
    

    // Execute the prepared query with the row values
    $stmt = $conn->prepare($insertQuery);
    $stmt->execute($rowValues);
}


//table 2 calculations 

// $tableName2 = 'comb_' . uniqid();
// $_SESSION['t_name2'] = $tableName2;

// // Create a new table for the user's data
// $createTableQuery = "CREATE TABLE $tableName2 (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     combinations VARCHAR(255)";
// for ($i = 1; $i <= $occurrenceco; $i++) { 
//     $createTableQuery .= ", co$i DECIMAL(10,2)";
// }
// $createTableQuery .= ")";
// $stmt = $conn->prepare($createTableQuery);
// $stmt->execute();
        
       
        



//         foreach ($columnTotalMarks as $key => $columnMarks) {
            
            
             
//             // Get the combination category value from the $combinations array
//             $category = $combinations[$key]['category'];

//             // Prepare the SQL INSERT statement
//             //$sql = "INSERT INTO $tableName2 (combinations, co1, co2, co3, co4, co5) VALUES (:combination, :co1, :co2, :co3, :co4, :co5)";
//             $sql = "INSERT INTO $tableName2 (combinations, ";
//             for ($i = 1; $i <= $occurrenceco; $i++) {
//                 $sql .= "co$i, ";
//             }
//             $sql = rtrim($sql, ", "); // Remove the trailing comma and space
//             $sql .= ") VALUES (:combination, ";

//             for ($i = 1; $i <= $occurrenceco; $i++) {
//                 $sql .= ":co$i, ";
//             }
//             $sql = rtrim($sql, ", ");
//             $sql .=  ")";
//             $stmt = $conn->prepare($sql);
            
//             // Bind the combination value
//             $stmt->bindValue(':combination', $category);
            
//             // Bind the column marks dynamically
//             $coIndex = 1; 
//             foreach ($columnMarks as $column => $marks) {
//                 if (isset($totalCombinationMarks1[$coIndex - 1])&& !empty($totalCombinationMarks1[$coIndex - 1])&& !empty($marks) && $marks !== 0) {
                    
                    
//                     $percentage = round(($marks / $totalCombinationMarks1[$coIndex - 1]) * 100, 0);
                    
//                 } else {
//                     $percentage = 0; // Handle division by zero gracefully
//                 }
//                 $paramName = ':co' . $coIndex;
//                 $stmt->bindValue($paramName, $percentage, PDO::PARAM_STR); 
//                 $coIndex++;
//             }
            
//             // Execute the statement
//             $stmt->execute();
//         }

    // percentage attainent 

    
// Function to retrieve the input threshold values for checkInput from the database
function getCheckInputThresholds() {
    global $conn;
    $thresholds = array();
    $sql = "SELECT name, value FROM config WHERE name LIKE 'checkInput\_%'";
    $stmt = $conn->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $name = $row['name'];
        $thresholds[substr($name, strpos($name, '_') + 1)] = $row['value'];
    }
    return $thresholds;
}

// Function to retrieve the input threshold values for checkInputuni from the database
function getCheckInputuniThresholds() {
    global $conn;
    $thresholds = array();
    $sql = "SELECT name, value FROM config WHERE name LIKE 'checkInputuni\_%'";
    $stmt = $conn->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $name = $row['name'];
        $thresholds[substr($name, strpos($name, '_') + 1)] = $row['value'];
    }
    return $thresholds;
}

// Function to check input against thresholds for checkInput
function checkInput($input) {
    $thresholds = getCheckInputThresholds();
    if ($input >= $thresholds['high']) {
        return 3;
    } elseif ($input >= $thresholds['medium']) {
        return 2;
    } elseif ($input >= $thresholds['low']) {
        return 1;
    } else {
        return 0;
    }
}

// Function to check input against thresholds for checkInputuni
function checkInputuni($input) {
    $thresholds = getCheckInputuniThresholds();
    if ($input >= $thresholds['high']) {
        return 3;
    } elseif ($input >= $thresholds['medium']) {
        return 2;
    } elseif ($input >= $thresholds['low']) {
        return 1;
    } else {
        return 0;
    }
}
require (__DIR__.'/../vendor/autoload.php');



$uploadFile = $_SESSION["file_name"];
// Load the Excel spreadsheet
$spreadsheet = IOFactory::load($uploadFile);
$worksheet = $spreadsheet->getActiveSheet();
$gradeColumn = getColumnLetter($startColumnIndex);
$startRowNumber = 5;

// Initialize variables
$passCount = 0;

$sinoColumn = 'A';

// to get the total students
$count = 0;

// Loop through each row and count the values in the "SI_NO" column
for ($row = 5; $row <= $highestRow; $row++) {
    $siNoValue = $worksheet->getCell($sinoColumn.$row)->getValue();
    
    // Check if the cell is not empty
    if ($siNoValue !== null) {
        $count++;
    }
}


$rowNumber = $startRowNumber;
// $failCount=0;
$gradeCellValue = $worksheet->getCell($gradeColumn . $rowNumber)->getValue();
    $gradeCellValue = trim($gradeCellValue);
    // $passGrades = ['F','Z'];
    // if (in_array($gradeCellValue, $passGrades)) {
    //     $failCount++;
    // }
    // $rowNumber++;


if ($count!=0) {
    $failPercentage = ($gradeCellValue / $count) * 100;
    $passPercentage = 100 - $failPercentage;
    $pass = round($passPercentage,1);
}else {
    echo"no count";
}

function getAttainmentPercentageThreshold() {
    global $conn;
    $sql = "SELECT value FROM config WHERE name = 'attainment_percentage_threshold'";
    $result = $conn->query($sql);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    return $row['value'];
}
// attained or not 
function isAttainmentReached($attainment, $target) {
    $attainmentPercentage = ($attainment / $target) * 100;
    $attainmentThreshold = getAttainmentPercentageThreshold();
    return $attainmentPercentage >= $attainmentThreshold;
}

function getTargetValue() {
    global $conn;
    $sql = "SELECT value FROM config WHERE name = 'target'";
    $result = $conn->query($sql);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    return $row['value'];
}

$target = getTargetValue(); // Replace with the target value



$tableName4 = "coat_". uniqid();
       $_SESSION['t_name4'] = $tableName4;
       $createTableQuery = "CREATE TABLE $tableName4 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        CO VARCHAR(20),
        AVERAGE1 DECIMAL(10,2),
        ATT1 DECIMAL(10,2),
        RESULT DECIMAL(10,2),
        ATT2 DECIMAL(10,2),
        CO_ATT DECIMAL(10,2),
        TARG DECIMAL(10,2),
        ATT_STATUS VARCHAR(20)
        )";
        $stmt = $conn->prepare($createTableQuery);
        $stmt->execute();

        for ($i = 1; $i <= $occurrenceco; $i++) {
            $coValue = "co" . $i;
            
            // Insert the $coValue into the CO column
            $insertQuery = "INSERT INTO $tableName4 (CO) VALUES ('$coValue')";
            
            // Execute the INSERT query
            $stmt= $conn->prepare($insertQuery);
            $stmt->execute();
        }
        // Generate column names CO1, CO2, CO3, etc.
        $coColumnNames = [];
        for ($i = 1; $i <= $occurrenceco; $i++) {
            $coColumnNames[] = "co" . $i;
        }

foreach ($coColumnNames as $co) {
    try {
        // Your database operations here
    
    // Calculate AVERAGE1 for each CO
    $average1Query = "UPDATE " . $tableName4 . " SET AVERAGE1 = 
        (SELECT AVG(" . $tableName . "." . $co . ")
        FROM $tableName)
        WHERE CO = '" . $co . "'";

      // Calculate AVERAGE1 for each CO (table2 calculations)
    //  $average1Query = "UPDATE " . $tableName4 . " SET AVERAGE1 = 
    //  (SELECT SUM(((" . $tableName . "." . $co . " / 100) * (" . $tableName2 . "." . $co . " / 100)) * 100)
    //  FROM $tableName
    //  JOIN $tableName2 ON $tableName.id = $tableName2.id)
    //  WHERE CO = '" . $co . "'";

    // //another average method 
    // $average1Query = "UPDATE $tableName4 
    //               SET AVERAGE1 = (SELECT AVG(CASE WHEN $tableName.$co != 0 AND $tableName.$co IS NOT NULL THEN $tableName.$co END)
    //                               FROM $tableName)
    //               WHERE CO = '$co'";

        
    $stmt = $conn->prepare($average1Query);
    $stmt->execute();

    $avg_value = null;
    $query = "SELECT AVERAGE1 FROM " . $tableName4 . " WHERE CO = '" . $co . "'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $avg_value = $row['AVERAGE1'];
    }

    

    // Calculate ATT1 and insert into ATT1 column
    $att1Query = "UPDATE " . $tableName4 . " SET ATT1 = " . checkInput($avg_value). " WHERE CO = '" . $co . "'";
    $stmt = $conn->prepare($att1Query);
    $stmt->execute();

    // Set RESULT column to $PASS for all rows
    $resultQuery = "UPDATE " . $tableName4 . " SET RESULT = " . $pass;
    $stmt = $conn->prepare($resultQuery);
    $stmt->execute();

    $result_value = null;
    $query = "SELECT RESULT FROM " . $tableName4 . " WHERE CO = '" . $co . "'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $result_value = $row['RESULT'];
    }

    // Calculate ATT2 and insert into ATT2 column
    $att2Query = "UPDATE " . $tableName4 . " SET ATT2 = " . checkInputuni($result_value) . " WHERE CO = '" . $co . "'";
    $stmt = $conn->prepare($att2Query);
    $stmt->execute();

    // Calculate CO_ATT (30% ATT1 + 70% ATT2) and insert into CO_ATT column
    $coAttQuery = "UPDATE " . $tableName4 . " SET CO_ATT = 
        (SELECT (0.4 * " . $tableName4 . ".ATT1) + (0.6 * " . $tableName4 . ".ATT2))
        WHERE CO = '" . $co . "'";
    $stmt = $conn->prepare($coAttQuery);
    $stmt->execute();

    $resultQuery = "UPDATE " . $tableName4 . " SET TARG = " . $target;
    $stmt = $conn->prepare($resultQuery);
    $stmt->execute();

    $co_att_value = null;
    $query = "SELECT CO_ATT FROM " . $tableName4 . " WHERE CO = '" . $co . "'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $co_att_value = $row['CO_ATT'];
    }


    // Calculate ATT_STATUS and insert into ATT_STATUS column
    $attStatusQuery = "UPDATE " . $tableName4 . " SET ATT_STATUS = '" . (isAttainmentReached($co_att_value, $target) ? "ATTAINED" : "NOT ATTAINED") . "' WHERE CO = '" . $co . "'";
    $stmt = $conn->prepare($attStatusQuery);
    $stmt->execute();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
}


//Table 5: calculations

$poCountQuery = "SELECT COUNT(*) AS po_count
                 FROM information_schema.COLUMNS
                 WHERE TABLE_NAME = '$subject_code' AND COLUMN_NAME LIKE 'po%'";
$poCountStmt = $conn->prepare($poCountQuery);
$poCountStmt->execute();
$poCountResult = $poCountStmt->fetch(PDO::FETCH_ASSOC);

// Count columns that start with 'pso'
$psoCountQuery = "SELECT COUNT(*) AS pso_count
                  FROM information_schema.COLUMNS
                  WHERE TABLE_NAME = '$subject_code' AND COLUMN_NAME LIKE 'pso%'";
$psoCountStmt = $conn->prepare($psoCountQuery);
$psoCountStmt->execute();
$psoCountResult = $psoCountStmt->fetch(PDO::FETCH_ASSOC);

// Store the counts in variables
$poColumnCount = $poCountResult['po_count'];
$psoColumnCount = $psoCountResult['pso_count'];

$_SESSION['po']=$poColumnCount;
$_SESSION['pso']=$psoColumnCount;

$tableName5 = "psoat_". uniqid();
       $_SESSION['t_name5'] = $tableName5;
       $createTableQuery = "CREATE TABLE $tableName5 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        CO VARCHAR(20),
        CO_ATTAINMENT DECIMAL(10,2)";
        for($i=1;$i<=$poColumnCount;$i++)
        {
        $createTableQuery .= ",PO$i DECIMAL(10,2)";
        }
        for($i=1;$i<=$psoColumnCount;$i++)
        {
            $createTableQuery .= ",PSO$i DECIMAL(10,2)";
        }
        $createTableQuery.=")";
        $stmt = $conn->prepare($createTableQuery);
        $stmt->execute();

        for ($i = 1; $i <= $occurrenceco; $i++) {
            $coValue = "CO" . $i;
            
            // Insert the $coValue into the CO column
            $insertQuery = "INSERT INTO $tableName5 (CO) VALUES ('$coValue')";
            
            // Execute the INSERT query
            $stmt= $conn->prepare($insertQuery);
            $stmt->execute();
        }
        $insertQuery = "INSERT INTO $tableName5 (CO) VALUES ('ATTAINMENT')";
        $stmt= $conn->prepare($insertQuery);
        $stmt->execute();
        // Generate column names CO1, CO2, CO3, etc.
        $coColumnNames = [];
        for ($i = 1; $i <= $occurrenceco; $i++) {
            $coColumnNames[] = "CO" . $i;
        }
        $poColumnNames = [];
        for($i=1;$i<=$poColumnCount;$i++){
            $poColumnNames[]="PO".$i;
        }
        $psoColumnNames = [];
        for($i=1;$i<=$psoColumnCount;$i++){
            $psoColumnNames[]="PSO".$i;
        }

        foreach ($coColumnNames as $co) {
            $co_att2_value=null;
            $attainmentquery = "SELECT CO_ATT FROM " . $tableName4 . " WHERE CO = '" . $co . "'";
            $stmt = $conn->prepare($attainmentquery);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $co_att2_value = $row['CO_ATT'];
    }

           $attainmentquery = "UPDATE " . $tableName5 . " SET CO_ATTAINMENT = " .$co_att2_value . " WHERE CO = '" . $co . "'";
           $stmt = $conn->prepare($attainmentquery);
           $stmt->execute();
           
           foreach ($poColumnNames as $po) {
            $po_map_value = null;
            $sub_po_mapquery = "SELECT " . $po . " FROM " . $subject_code . " WHERE CO = '" . $co . "'";
            $stmt = $conn->prepare($sub_po_mapquery);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($row) {
                $po_map_value = $row[$po];
            }
        
            $finalpo_map = (($po_map_value / 3) * $co_att2_value);
    
        
            // Only set $finalpo_map to null if it's zero for the current iteration
            if ($finalpo_map == 0 && $po_map_value == 0) {
                $finalpo_map = null;
            }
        
            $poquery = "UPDATE " . $tableName5 . " SET " . $po . " = " . ($finalpo_map !== null ? $finalpo_map : 'NULL') . " WHERE CO = '" . $co . "'";
            $stmt = $conn->prepare($poquery);
            $stmt->execute();
        }
        

foreach($psoColumnNames as $pso){
    $pso_map_value = null;
    $sub_pso_mapquery = "SELECT ". $pso ." FROM " . $subject_code . " WHERE CO = '" . $co . "'";
    $stmt = $conn->prepare($sub_pso_mapquery);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
$pso_map_value = $row["$pso"];
}
$finalpso_map = (($pso_map_value / 3) * $co_att2_value);
if ($finalpso_map == 0 && $pso_map_value == 0) {
    $finalpso_map = null;
}
$psoquery = "UPDATE " . $tableName5 . " SET " . $pso . " = " . ($finalpso_map !== null ? $finalpso_map : 'NULL') . " WHERE CO = '" . $co . "'";
$stmt = $conn->prepare($psoquery);
$stmt->execute();
}  




           
}
foreach ($poColumnNames as $po) {
    $avgquery = "SELECT AVG(" . $po . ") AS " . $po . "_avg FROM " . $tableName5;
    $stmt = $conn->prepare($avgquery);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result !== false) {
        $avgValue = $result[$po . '_avg'];

        // Update the corresponding row in the $tableName5 table
        $updateQuery = "UPDATE " . $tableName5 . " SET " . $po . " = :avgValue WHERE CO = 'ATTAINMENT'";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':avgValue', $avgValue, PDO::PARAM_STR);
        $updateStmt->execute();

    } else {
        echo "Error fetching $po average.\n";
    }
}

foreach ($psoColumnNames as $pso) {
    $avgquery = "SELECT AVG(" . $pso . ") AS " . $pso . "_avg FROM " . $tableName5;
    $stmt = $conn->prepare($avgquery);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result !== false) {
        $avgValue = $result[$pso . '_avg'];

        // Update the corresponding row in the $tableName5 table
        $updateQuery = "UPDATE " . $tableName5 . " SET " . $pso . " = :avgValue WHERE CO = 'ATTAINMENT'";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':avgValue', $avgValue, PDO::PARAM_STR);
        $updateStmt->execute();

    } else {
        echo "Error fetching $pso average.\n";
    }
}



        



} catch (PDOException $e) {
    // Handle any errors
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>My Webpage</title>
<link rel="stylesheet" type="text/css" href="../css/style.css"> 
<script src="https://unpkg.com/exceljs/dist/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script> 
</head>
<body >         
<section class="ptu-title__container">
<div class="ptu-title__logo-header">
<a href="https://www.ptuniv.edu.in/" style="text-decoration: none;"> 
<img src="../img/logo1.png" class="ptu-title__logo1" alt="Puducherry Technological University"> 
</a>
</div>
<div class="ptu-title__collage-name-container">
<h1 class="ptu-title__collage-name">
<span class="ptu-title__first-letter">P</span>
<span>UDUCHERRY </span>
<span class="ptu-title__first-letter">T</span>
<span>ECHNOLOGICAL </span>
<span class="ptu-title__first-letter">U</span>
<span>NIVERSITY</span>
</h1>
<h5 class="ptu-title__place1">(Government of Puducherry Institution)</h5>
<h5 class="ptu-title__place2">Puducherry, India - 605 014.</h5>
</div>
</section>
<div class="table-container">
    <h2 class="table-title">CO ASSESSMENT TABLE</h2>
    <table id="assessment">
        <tr>
            <th>CO/ASSESSMENT</th>
            <?php
            $selectQuery = "SELECT * FROM $tableName";
            $result = $conn->prepare($selectQuery);
            $result->execute();
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo '<th>' . htmlspecialchars($row['category']) . '</th>';
            }
            ?>
            <th>AVERAGE</th>
            <th>FINAL PASS %</th>
        </tr>
        <?php

        // Output data column by column
        for ($i = 1; $i <= $occurrenceco; $i++) {
            echo '<tr>';
            echo '<td>CO' . $i . '</td>'; // Column header

            // Output data for each row in this column from $result
            $result->execute(); // Reset the pointer to the beginning
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $formattedValue = $row['co' . $i] !== null ? number_format($row['co' . $i], ($row['co' . $i] == intval($row['co' . $i]) ? 0 : 2)) : '';
                echo '<td>' . ($formattedValue !== '' ? $formattedValue . '' : '-') . '</td>';
            }

            // Fetch one row from $result1 for each iteration of the outer loop
            $query2 = "SELECT AVERAGE1, RESULT FROM $tableName4 LIMIT " . ($i - 1) . ", 1";
            $result1 = $conn->prepare($query2);
            $result1->execute();
            if ($row = $result1->fetch(PDO::FETCH_ASSOC)) {
                echo '<td>' . htmlspecialchars($row['AVERAGE1'] !== null ? $row['AVERAGE1'] . '' : '-') . '</td>';
                echo '<td>' . $row['RESULT'] . '</td>';
            }
            echo '</tr>';
        }
        ?>
    </table>
</div>

<br>
<br>
<!-- <div class='table-container'> //table calculations 
<h2 class="table-title">CO-QUESTION(%) TABLE</h2>
<table id="tbl_exporttable_to_xls">
<tr><th>COMBINATION</th>
<?php
    // for ($i = 1; $i <= $occurrenceco; $i++) {
    // echo '<th>CO' . $i . '</th>';
    // }
    ?>
</tr>
<?php
// $selectQuery = "SELECT * FROM $tableName2";
// $result = $conn->prepare($selectQuery);
// $result->execute();
// while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
//     echo '<tr>';
//     echo '<td>' . htmlspecialchars($row['combinations']) . '</td>';
    
//     // Loop through co1, co2, co3, ...
//     for ($i = 1; $i <= $occurrenceco; $i++) {
//         $formattedValue = number_format($row['co' . $i], ($row['co' . $i] == intval($row['co' . $i]) ? 0 : 2));
//         echo '<td>' . $formattedValue . '%' . '</td>';
//         }
    
//     echo '</tr>';
// }
?>
</table>
</div> 
<br>
<br> -->
<br>
<br>
<div class='table-container'>
<h2 class="table-title">CO-ATTAINMENT TABLE</h2>
<table id="third_table">
<tr>
<th rowspan="2" colspan="1">CO</th>
<th colspan="2">INTERNAL TEST(40%)</th>
<th colspan="2">UNIVERSITY RESULT(60%)</th>
<th rowspan="2">CO ATTAINMENT</th>
<th rowspan="2">TARGET</th>
<th rowspan="2">ATTAINED / NOT ATTAINED</th>
</tr>
<tr>

<th >AVERAGE</th>
<th>ATTAINMENT</th>
<th>%RESULT</th>
<th>ATTAINMENT</th>

</tr>
<?php
$query = "SELECT CO, AVERAGE1, ATT1,RESULT,ATT2,CO_ATT, TARG, ATT_STATUS FROM $tableName4";


$result = $conn->query($query);

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo '<tr>';
    echo '<td>' . strtoupper(htmlspecialchars($row['CO'])) . '</td>';
    // echo '<td>' . htmlspecialchars($row['AVERAGE1']) . '</td>';
    echo '<td>' . htmlspecialchars($row['AVERAGE1']!== null ? $row['AVERAGE1'] . '' : '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['ATT1']) . '</td>';
    echo '<td>' . htmlspecialchars($row['RESULT']) . '</td>';
    echo '<td>' . htmlspecialchars($row['ATT2']) . '</td>';
    echo '<td>' . htmlspecialchars($row['CO_ATT']) . '</td>';
    echo '<td>' . htmlspecialchars($row['TARG']) . '</td>';
    echo '<td>' . ($row['ATT_STATUS']) . '</td>';
    echo '</tr>';
}
?>
</table>
</div>
<br>
<br>
<br>
<br>
<div class = "table-container">
<h2 class="table-title">PO/PSO-ATTAINMENT TABLE</h2>
<table id="pso_table">
<tr>
<th>CO</th>
<th>CO ATTAINMENT</th>
<?php
    for ($i = 1; $i <= $poColumnCount; $i++) {
    echo '<th>PO' . $i . '</th>';
    }
    for ($i = 1; $i <= $psoColumnCount; $i++) {
        echo '<th>PSO' . $i . '</th>';
        }
?>
</tr>

<?php 
$selectQuery = "SELECT CO,CO_ATTAINMENT";
for ($i = 1; $i <= $poColumnCount; $i++) {
    $selectQuery .= ", PO$i";
}
for ($i = 1; $i <= $psoColumnCount; $i++) {
    $selectQuery .= ", PSO$i";
}

$selectQuery .= " FROM $tableName5";
$result = $conn->prepare($selectQuery);
$result->execute();
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['CO']) . '</td>';
    echo '<td>' . htmlspecialchars($row['CO_ATTAINMENT']) . '</td>';
    for ($i = 1; $i <= $poColumnCount; $i++) {
        $formattedValue = htmlspecialchars(($row['PO' . $i])!== null ? $row['PO' . $i]: '-') ;
        echo '<td>' . $formattedValue .  '</td>';
        }
        for ($i = 1; $i <= $psoColumnCount; $i++) {
            $formattedValue = htmlspecialchars(($row['PSO' . $i])!== null ? $row['PSO' . $i]: '-') ;
            echo '<td>' . $formattedValue .  '</td>';
        }
    echo '</tr>';
}
  ?> 
</table>
</div> 

<br>
<br>





<center>
<!-- <button onclick="exportToExcel()" class="export-button">Export Tables to Excel</button> -->

<button class="export-button" type="button" onclick="window.location.href='generate-pdf2.php'">Download pdf</button>

</center>




<!-- <script>

function exportToExcel() {  
var workbook = new ExcelJS.Workbook();

// Create Sheet 1 for CO-QUESTION table
var sheet1 = workbook.addWorksheet('ASSESSMENT');
var table1 = document.getElementById('assessment');

for (var i = 0; i < table1.rows.length; i++) {
var row = table1.rows[i];
var excelRow = sheet1.addRow();

for (var j = 0; j < row.cells.length; j++) {
    var cell = row.cells[j];
    var excelCell = excelRow.getCell(j + 1);

    excelCell.value = cell.textContent;
}
}

// Create Sheet 2 for ASSESSMENT table

var sheet2 = workbook.addWorksheet('CO-QUESTION');
var table2 = document.getElementById('tbl_exporttable_to_xls');

for (var i = 0; i < table2.rows.length; i++) {
var row = table2.rows[i];
var excelRow = sheet2.addRow();

for (var j = 0; j < row.cells.length; j++) {
    var cell = row.cells[j];
    var excelCell = excelRow.getCell(j + 1);

    excelCell.value = cell.textContent;
}
}

//Create sheet 3 for co-attainment table
var sheet3 = workbook.addWorksheet('CO-ATTAINMENT');
var table3 = document.getElementById('third_table');

sheet3.mergeCells('A1:A2');
sheet3.getCell('A1').value = 'CO';
sheet3.mergeCells('B1:C1');
sheet3.getCell('B1').value = 'Internal test(30%)';
sheet3.getCell('B2').value = 'Average';
sheet3.getCell('C2').value = 'Attainment';
sheet3.mergeCells('D1:E1');
sheet3.getCell('D1').value = 'University Result(70%)';
sheet3.getCell('D2').value = 'Result%';
sheet3.getCell('E2').value = 'Attainment';
sheet3.mergeCells('F1:F2');
sheet3.getCell('F1').value = 'CO Attainment';
sheet3.mergeCells('G1:G2');
sheet3.getCell('G1').value = 'Target';
sheet3.mergeCells('H1:H2');
sheet3.getCell('H1').value = 'Attained/Not Attained';

// Insert data into sheet3
for (var i = 2; i < table3.rows.length; i++) {
var row = table3.rows[i];
var excelRow = sheet3.addRow();

for (var j =0; j < row.cells.length; j++) {
    var cell = row.cells[j];
    var excelCell = excelRow.getCell(j + 1);

    excelCell.value = cell.textContent;
}
}
console.log('Exporting sheet4');
var sheet4 = workbook.addWorksheet('PO_PSO-ATTAINMENT');
var table4 = document.getElementById('pso_table');
for (var i = 0; i < table4.rows.length; i++) {
var row = table4.rows[i];
var excelRow = sheet4.addRow();

for (var j = 0; j < row.cells.length; j++) {
    var cell = row.cells[j];
    var excelCell = excelRow.getCell(j + 1);

    excelCell.value = cell.textContent;
}
}


workbook.xlsx.writeBuffer().then(function (data) {
var blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
saveAs(blob, 'tables.xlsx');
});
}
</script> -->
</body>
</html>


<?php

}
?>
