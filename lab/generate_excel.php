<?php
require __DIR__.'/../vendor/autoload.php';
include (__DIR__.'/../database.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

session_start();
// Check if session variables are not set or null
if (!isset($_SESSION['num_mid_sem']) || $_SESSION['num_mid_sem'] === null ||
    !isset($_SESSION['num_experiments']) || $_SESSION['num_experiments'] === null) {
    // Output JavaScript alert message if session variables are missing
    header("Location: ./errorTemplate.html");
}
else{
$num_assessments = $_SESSION['num_mid_sem'];

$num_assignments = $_SESSION['num_experiments']; 

$student_name_list = $_SESSION['stdnamelist'];
$subject_code=$_SESSION['subject_code'];
$co_no = $_SESSION['co_no'];

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
    $endColumn = $startColumn + $co_no - 1;;
    
    $sheet->mergeCellsByColumnAndRow($startColumn, 1, $endColumn, 1);
    
    for ($j = 0; $j < $co_no; $j++) {
        $column_headers[] = ($j === 0) ? "MIDSEM $i" : " ";
    }
    
    $style = $sheet->getStyleByColumnAndRow($startColumn, 1);
    $alignment = $style->getAlignment();
    $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
}

for ($i = 1; $i <= $num_assignments; $i++) {
    $startColumn = count($column_headers) + 1;
    $endColumn = $startColumn + $co_no - 1;;
    
    $sheet->mergeCellsByColumnAndRow($startColumn, 1, $endColumn, 1);
    
    for ($j = 0; $j < $co_no; $j++) {
        $column_headers[] = ($j === 0) ? "RECORD $i" : " ";
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

$writer = new Xlsx($spreadsheet);

// Set Content-Type and Content-Disposition headers
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$subject_code}_assessment_data.xlsx\"");
header('Cache-Control: max-age=0');

// Output the file to the browser
$writer->save('php://output');
}
?>