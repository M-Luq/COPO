<?php
session_start();
require __DIR__.'/../vendor/autoload.php';
include (__DIR__.'/../database.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


$num_assessments = $_SESSION['num_assessments'];
$submissionTableName = $_SESSION['submission'];
$num_assignments = $_SESSION['num_assignments']; 
$assignmentTableName = $_SESSION['assignment'];
$student_name_list = $_SESSION['stdnamelist'];
$uniqueid = $_SESSION['uniqueid'];
$uniqueid2 = $_SESSION['uniqueid2'];
//$uniqueid3 = $_SESSION['uniqueid3'];
$subject_code=$_SESSION['subject_code'];
$co_no = $_SESSION['co_no'];
$num_failures = $_SESSION['num_failures'];


try {
    $dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Create a new Excel spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// ... (your header and column setup code)
$column_headers = ['S.No','Register no', 'Name of the Student',' '];
$coHeaderRange = 'A1:' . Coordinate::stringFromColumnIndex(count($column_headers)) . '1';

$sheet->getStyle($coHeaderRange)
    ->getFont()->setBold(true);
$sheet->getStyle($coHeaderRange)
    ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->mergeCellsByColumnAndRow(1, 1, 1, 4);
$sheet->mergeCellsByColumnAndRow(2, 1, 2, 4);
$sheet->mergeCellsByColumnAndRow(3, 1, 3, 4);

for ($i = 1; $i <= $num_assessments; $i++) {
    $startColumn = count($column_headers) + 1;
    $endColumn = $startColumn + $co_no - 1;
    
    $sheet->mergeCellsByColumnAndRow($startColumn, 1, $endColumn, 1);
    
    for ($j = 0; $j < $co_no ; $j++) {
        $column_headers[] = ($j === 0) ? "CAT $i" : " ";
    }
    
    $style = $sheet->getStyleByColumnAndRow($startColumn, 1);
    $alignment = $style->getAlignment();
    $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
}

for ($i = 1; $i <= $num_assignments; $i++) {
    $startColumn = count($column_headers) + 1;
    $endColumn = $startColumn + $co_no - 1;
    
    $sheet->mergeCellsByColumnAndRow($startColumn, 1, $endColumn, 1);
    
    for ($j = 0; $j < $co_no; $j++) {
        $column_headers[] = ($j === 0) ? "ASSIGNMENT $i" : " ";
    }
    
    $style = $sheet->getStyleByColumnAndRow($startColumn, 1);
    $alignment = $style->getAlignment();
    $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
}

$startColumn = count($column_headers) + 1;
$endColumn = $startColumn + 1;
$sheet->mergeCellsByColumnAndRow($startColumn, 1, $endColumn, 1);

$column_headers[] = "University Result";
$column_headers[] = " ";

$style = $sheet->getStyleByColumnAndRow($startColumn, 1);
$alignment = $style->getAlignment();
$alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->fromArray([$column_headers], null, 'A1');

$sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')
    ->getFont()->setBold(true);




    $co_headers = [' ', ' ', ' ', 'CO'];

    for ($i = 1; $i <= ($num_assessments + $num_assignments); $i++) {
        $startColumn = count($co_headers) + 1;
        
        for ($coNumber = 1; $coNumber <= $co_no; $coNumber++) {
            $co_headers[] = "CO$coNumber";
        }
        
    }
    
    $sheet -> mergeCellsByColumnAndRow(count($co_headers)+1, 2,count($co_headers)+2 , 4);
    $co_headers[] = "No of Failures";
    $style = $sheet->getStyleByColumnAndRow($startColumn, 2);
       
    $coHeaderRange = 'A2:' . Coordinate::stringFromColumnIndex(count($co_headers)) . '2';
    $sheet->getStyle($coHeaderRange)->getFont()->setBold(true);
    $sheet->getStyle($coHeaderRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->fromArray([$co_headers], null, 'A2');

    $questions = [' ', ' ', ' ', 'QUESTIONS'];
$MM = [' ', ' ', ' ', 'MAXIMUM MARKS'];

$styleRange = 'A3:' . Coordinate::stringFromColumnIndex(count($questions)) . '3';
$sheet->getStyle($styleRange)
    ->getFont()->setBold(true);
$sheet->getStyle($styleRange)
    ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->fromArray([$questions], null, 'A3');

$styleRange = 'A4:' . Coordinate::stringFromColumnIndex(count($MM)) . '4';
$sheet->getStyle($styleRange)
    ->getFont()->setBold(true);
$sheet->getStyle($styleRange)
    ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->fromArray([$MM], null, 'A4');

   
    // Fetch question and set cell value
    for ($assessment = 1; $assessment <= $num_assessments; $assessment++) {
        for ($coNumber = 1; $coNumber <= $co_no; $coNumber++) {
            $coCategory = "co" . $coNumber;
    
            $query = "SELECT question FROM $submissionTableName WHERE subdivision_category = '$coCategory' AND assessment = $assessment";
            try {
                $stmt = $dbh->prepare($query);
                $stmt->execute();
                $questionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                $cell = Coordinate::stringFromColumnIndex(($assessment - 1) * $co_no + $coNumber + 4) . '3';
                $cellValue = "";
    
                foreach ($questionData as $questionRow) {
                    if ($cellValue !== "") {
                        $cellValue .= ","; // Add new line if multiple questions
                    }
                    $cellValue .= $questionRow['question'];
                }
    
                $sheet->setCellValue($cell, $cellValue);
    
                if ($cellValue !== "") {
                    $style = $sheet->getStyle($cell);
                    $alignment = $style->getAlignment();
                    $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $alignment->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            } catch (PDOException $e) {
                echo "Error fetching data: " . $e->getMessage();
            }
        }
    }
    

    for ($assessment = 1; $assessment <= $num_assessments; $assessment++) {
        for ($coNumber = 1; $coNumber <= $co_no; $coNumber++) {
        $coCategory = "co" .$coNumber; // Change this to match your CO category naming
    
        $query = "SELECT subdivision_mark FROM $submissionTableName WHERE subdivision_category = '$coCategory' AND assessment = $assessment";
        try {
            $stmt = $dbh->prepare($query);
            $stmt->execute();
            $marksData = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
            $cell = Coordinate::stringFromColumnIndex(($assessment - 1)*$co_no + $coNumber + 4 ). '4';
            // E4, F4, G4, ...
            if(!empty($marksData)){
            $sum = array_sum($marksData);
            if($sum!=0){
            $sheet->setCellValue($cell, $sum);
            $style = $sheet->getStyle($cell);
            $alignment = $style->getAlignment();
            $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $alignment->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            }
            
            else {
            $sum = 0;
            $sheet->setCellValue($cell, $sum);
            $style = $sheet->getStyle($cell);
            $alignment = $style->getAlignment();
            $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $alignment->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER); // Set the cell to empty
            }
            
        }
        } catch (PDOException $e) {
            echo "Error fetching data: " . $e->getMessage();
        }
    }
    }

    //to handle zero cell entry
    /*
    for ($assessment = 1; $assessment <= $num_assessments; $assessment++) {
        for ($coNumber = 1; $coNumber <= $co_no; $coNumber++) {
            $coCategory = "co" . $coNumber; // Change this to match your CO category naming
    
            $query = "SELECT subdivision_mark FROM $submissionTableName WHERE subdivision_category = '$coCategory' AND assessment = $assessment";
            try {
                $stmt = $dbh->prepare($query);
                $stmt->execute();
                $marksData = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
                $cell = Coordinate::stringFromColumnIndex(($assessment - 1) * 5 + $coNumber + 4) . '4'; // E4, F4, G4, ...
    
                // Check if $marksData is empty
                if (empty($marksData)) {
                    $sum = 0;
                } else {
                    $sum = array_sum($marksData);
                }
    
                // Set the cell value to $sum
                $sheet->setCellValue($cell, $sum);
    
                // Apply alignment to the cell
                $style = $sheet->getStyle($cell);
                $alignment = $style->getAlignment();
                $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $alignment->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            } catch (PDOException $e) {
                echo "Error fetching data: " . $e->getMessage();
            }
        }
    }
*/    



    for ($assignmentNumber = 1; $assignmentNumber <= $num_assignments; $assignmentNumber++) {
        // Calculate the start column index for assignment marks for the current assignment
        $assignmentColumnStart = ($num_assessments-1+$assignmentNumber) *$co_no + 4;
    
        $queryAssignment = "SELECT * FROM $assignmentTableName WHERE ASSIGNMENT_NO = $assignmentNumber";
        try {
            $stmtAssignment = $dbh->query($queryAssignment);
            $rowNumber = 4; // Start row number for student names
    
            while ($rowAssignment = $stmtAssignment->fetch(PDO::FETCH_ASSOC)) {
                for ($coNumber = 1; $coNumber <= $co_no; $coNumber++) {
                    $cell = Coordinate::stringFromColumnIndex($assignmentColumnStart + $coNumber) . $rowNumber;
                    $assignmentMark = $rowAssignment["CO$coNumber"];
                    $sheet->setCellValue($cell, $assignmentMark);
    
                    $style = $sheet->getStyle($cell);
                    $alignment = $style->getAlignment();
                    $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $alignment->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
                $rowNumber++;
            }
        } catch (PDOException $e) {
            echo "Error fetching assignment data for Assignment $assignmentNumber: " . $e->getMessage();
        }
    }


$query = "SELECT STDNAME FROM $student_name_list";
$stmt = $dbh->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_COLUMN);

$query = "SELECT REGNO FROM $student_name_list";
$stmt = $dbh->prepare($query);
$stmt->execute();
$regno = $stmt->fetchAll(PDO::FETCH_COLUMN);

$rowNumber = 5; // Start from the fifth row to avoid overwriting the headers
$serialNumber = 1;

foreach ($students as $index => $student) {
    $sheet->setCellValue('A' . $rowNumber, $serialNumber);
    $sheet->setCellValue('B' . $rowNumber, $regno[$index]); // Access the correct registration number
    $sheet->setCellValue('C' . $rowNumber, $student);
    $rowNumber++;
    $serialNumber++;
}

for ($j = 1; $j <= $num_assessments; $j++) {
    $sql = "SELECT * FROM $uniqueid WHERE assessment_no = $j";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    // Fetch data as associative array
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Specify starting row
    $startRow = 5;

    // Reset column index for each assessment
    $startCol = ($j - 1) * $co_no + 5; // Starting from column E for the first assessment, F for the second, and so on

    foreach ($data as $index => $row) {
        // Loop through co1 to co5 columns
        for ($i = 1; $i <= $co_no; $i++) {
            $columnName = "co$i";

            // Check if the column exists in the row
            if (isset($row[$columnName])) {
                $coValue = $row[$columnName];
                $sheet->setCellValueByColumnAndRow($startCol, $startRow + $index, $coValue);
                $cellCoordinate = Coordinate::stringFromColumnIndex($startCol) . ($startRow + $index);
                $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($cellCoordinate)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            }

            // Move to the next column
            $startCol++;
        }
        $startCol = ($j - 1) * $co_no + 5;
    }
}


$lastColumnFirstLoop = ($num_assessments * $co_no) + 5;
for ($j = 1; $j <= $num_assignments; $j++) {
    $sql = "SELECT * FROM $uniqueid2 where assignment_no =$j";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    // Fetch data as associative array
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($data as $index => $row) {
        // Calculate the starting column for the current assignment
        $startCol = $lastColumnFirstLoop + ($j - 1) * $co_no;

        // Loop through co1 to co5 columns
        for ($i = 1; $i <= $co_no; $i++) {
            $columnName = "co$i";

            // Check if the column exists in the row
            if (isset($row[$columnName])) {
                $coValue = $row[$columnName];
                $sheet->setCellValueByColumnAndRow($startCol, $startRow + $index, $coValue);
                $cellCoordinate = Coordinate::stringFromColumnIndex($startCol) . ($startRow + $index);
                $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($cellCoordinate)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                
            }

            // Move to the next column
            $startCol++;
        }
    }
}

$lastColumnFirstLoop = ($num_assessments + $num_assignments) * $co_no + 5;


// Determine the cell coordinate for the number of failures
$failureCellCoordinate = Coordinate::stringFromColumnIndex($lastColumnFirstLoop) . ($startRow);
$failureCellCoordinate1 = Coordinate::stringFromColumnIndex($lastColumnFirstLoop + 1) . ($startRow);

// Insert the number of failures in the respective cell
$sheet->setCellValue($failureCellCoordinate, $num_failures);

// Merge the cell for the number of failures
$sheet->mergeCells("$failureCellCoordinate:$failureCellCoordinate1");

// Center align the content in the cell
$sheet->getStyle($failureCellCoordinate)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle($failureCellCoordinate)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// for ($j = 1; $j <= 1; $j++) {
//     $sql = "SELECT * FROM $uniqueid3";
//     $stmt = $dbh->prepare($sql);
//     $stmt->execute();

//     $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     foreach ($data as $index => $row) {
//         if (isset($row["grade"])) {
//             $gradeval = $row["grade"];
//             $cellCoordinate = Coordinate::stringFromColumnIndex($lastColumnFirstLoop) . ($startRow + $index);
//             $nextCellCoordinate = Coordinate::stringFromColumnIndex($lastColumnFirstLoop + 1) . ($startRow + $index);

//             $sheet->setCellValueByColumnAndRow($lastColumnFirstLoop, $startRow + $index, $gradeval);

//             // Merge the current and next cells, and center content
//             $sheet->mergeCells("$cellCoordinate:$nextCellCoordinate");
//             $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
//             $sheet->getStyle($cellCoordinate)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
//         }
//     }
// }






// to make the column width accordingto content in it
foreach(range('A',$sheet->getHighestDataColumn()) as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// to make the cell to be centered both horizontally and vertically
$highestColumn = $sheet->getHighestColumn();
$highestRow = $sheet->getHighestRow();

// Loop through all cells in the worksheet
for ($row = 1; $row <= $highestRow; $row++) {
    for ($column = 'A'; $column <= $highestColumn; $column++) {
        // Get the style of each cell
        $style = $sheet->getStyle($column . $row);
        
        // Set horizontal and vertical alignment to center
        $style->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $style->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    }
}


// ... (header settings)

$writer = new Xlsx($spreadsheet);

// Set Content-Type and Content-Disposition headers
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$subject_code}_assessment_data.xlsx\"");
header('Cache-Control: max-age=0');

// Output the file to the browser
$writer->save('php://output');

?>
