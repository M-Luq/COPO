<?php
session_start();
$tableName = $_SESSION['t_name1'];
// $tableName2 = $_SESSION['t_name2'];
$tableName4=$_SESSION['t_name4'];
$co_no = $_SESSION['co_no'];
$poColumnCount = $_SESSION['po'];
$psoColumnCount =$_SESSION['pso'];
$tableName5 = $_SESSION['t_name5'];
$assessment_table = $_SESSION['uniqueid'];
$assignments_table = $_SESSION['uniqueid2'];
$stdnamelist = $_SESSION['stdnamelist'];
$no_assessment = $_SESSION['num_mid_sem'];
$no_assignment = $_SESSION['num_experiments'];

require_once(__DIR__.'/../PDF/dompdf/autoload.inc.php');
include (__DIR__.'/../database.php');

use Dompdf\Dompdf;
use Dompdf\Options; 

$options = new Options;
$options->set("chroot",realpath(''));
$options->setIsRemoteEnabled(true);//

$dompdf = new Dompdf($options);
$conn = mysqli_connect($servername, $username, $password, $dbname);

    $output = '
    <style>
    table {
    width: 90%;
    margin: 0 auto; /* Center the table horizontally */
    border-spacing: 0;
    background-color: #fff;
    color: #000;
    table-layout:fixed ;
      }
  
  th, td {
      padding:3px;
      text-align: center;
      border-bottom: 1px solid #ddd;
      font-size:13px;
      border: 0.3px solid  black;
  }
  th, td {
    word-wrap: break-word; /* Wrap long words within table cells */
}
  #ptu {
    height: 90px; /* Adjust the height as needed */
    width: auto; /* Let the width adjust proportionally */
    position: absolute;
    top: 20px;
    left: 70px; /* Adjust the left position as needed */
  }
  
  h1{
    position:relative;
    top:20px;
    left:35px;
    font-size:20px;
  }
  th {
      background-color: #eaeaea;
  }
  
  body{
    margin-top: 5px;;
    border: -2px solid #000;
  }
  
  th, td:first-child{
          font-weight: bold;
      }
  
  .table-container {
      display: flex;
      flex-direction: column;
      align-items: center;
  }
  </style>
  <body>
  <center>   
  <img id = "ptu" src="./logo1.png" alt="ptu logo" >
  <h1>PUDUCHERRY TECHNOLOGICAL UNIVERSITY</h1>
  <h2>CO-PO MAPPING REPORT</h2>
  ';
  for ($i=1; $i <= $no_assessment; $i++) { 
      // Execute the query and fetch the results
  $query = "SELECT * FROM $assessment_table,$stdnamelist WHERE $assessment_table.student_regno = $stdnamelist.REGNO AND $assessment_table.midsem_no = '$i'";
  $result = mysqli_query($conn, $query);
  $output .='<br>
  <br>
  <h2>STUDENT MIDSEM '.$i.' MARKS</h2>
    <div class="table-container">
    <table>
    <thead>
    <tr>
    <th>SINO</th>
    <th>REGNO</th>
    <th>NAME</th> ';
    for($j=1;$j<=$co_no;$j++){
        $output .= '<th>CO' . $j .'</th>';
      }
      $output .=' </tr>
      </thead>';
    if ($result->num_rows > 0) {
      // Output data of each row
      while ($row = mysqli_fetch_array($result)) {
          $output .='
          <tr>
              <td>'.$row["id"].'</td>
              <td>'.$row["student_regno"].'</td>
              <td>'.$row["STDNAME"].'</td> ';
              for ($k = 1; $k <= $co_no; $k++) {
                $output .= '<td>'. $row["co$k"] .'</td>';
              }
         $output .=' </tr>';
      }

  }
  $output .='</table></div>';
}
for ($i=1; $i <= $no_assignment; $i++) { 
$output .='
  <h2>STUDENT RECORD '.$i.' MARKS</h2>
  <div class="table-container">
  <table>
  <thead>
  <tr>
  <th>SINO</th>
  <th>REGNO</th>
  <th>NAME</th>';
  for($j=1;$j<=$co_no;$j++){
    $output .= '<th>CO' . $j .'</th>';
  }
  $output .=' </tr>
  </thead>';

  $query = "SELECT * FROM $assignments_table,$stdnamelist WHERE $assignments_table.student_regno = $stdnamelist.REGNO AND $assignments_table.experiment_no = '$i'";
    
  // Execute the query and fetch the results
  $result = mysqli_query($conn, $query);
  if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = mysqli_fetch_array($result)) {
        $output .='
        <tr>
            <td>'.$row["id"].'</td>
            <td>'.$row["student_regno"].'</td>
            <td>'.$row["STDNAME"].'</td> ';
            for ($k = 1; $k <= $co_no; $k++) {
              $output .= '<td>'. $row["co$k"] .'</td>';
            }
       $output .=' </tr>';
    }

}
$output .='</table></div>';
}
  $query = "SELECT * FROM $tableName";
    // Execute the query and fetch the results
    $result = mysqli_query($conn, $query);

  $query2 = "SELECT AVERAGE1, RESULT FROM $tableName4";
  $result1 = mysqli_query($conn, $query2);
  $output .='<br><br>
  <h2>CO-ASSESSMENT TABLE</h2>
  <div class= "table-container">
  <table>
    <thead>
      <tr>
        <th>CO/ASSESSMENT</th>';
        if ($result->num_rows > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            $output .= '<th>' . $row['category'] . '</th>';
        // for($i=1;$i<=$co_no;$i++){
        //   $output .= '<th>CO' . $i .'</th>';
        // }
          }
        }
        $output .= '<th> AVERAGE </th>
        <th>FINAL PASS % </th>';
        $output .=' </tr>
    </thead>
    ';
    // Handle the results as needed
    if ($result->num_rows > 0 && $result1->num_rows>0) {
      
      // Output data column by column
      for ($i = 1; $i <= $co_no; $i++) {
          $output .= '<tr>';
          $output .= '<td>CO' . $i .'</td>'; // Column header

          // Output data for each row in this column
          mysqli_data_seek($result, 0); // Reset the pointer to the beginning
          while ($row = mysqli_fetch_assoc($result)) {
            $formattedValue = $row['co' . $i] !== null ? number_format($row['co' . $i], ($row['co' . $i] == intval($row['co' . $i]) ? 0 : 2)) : '';
            
            $output .= '<td>' . ($formattedValue !== '' ? $formattedValue . '' : '-') . '</td>';
          }
          
        mysqli_data_seek($result1, $i-1); 
        if ($row = mysqli_fetch_assoc($result1)) {
            $output .= '<td>' . htmlspecialchars($row['AVERAGE1']!== null ? $row['AVERAGE1'] . '' : '-') . '</td>';
            $output .= '<td>' . $row['RESULT'] .'</td>';
        }
          $output .= '</tr>';
      }
  }
  
    $output .='</table></div> <br><br>';
    include (__DIR__.'/../matrixeditor/functions_config.php');

// Get the threshold values from the database
$internal_average_thresholds = getCheckInputThresholds();
$university_exam_thresholds = getCheckInputuniThresholds();

// Generate HTML output
$output .= '<h2>Attainment Level Calculation</h2>

<h3>Internal Average % Attainment Level</h3>
<table>
  <tr>
    <th>%</th>
    <th>Attainment Level</th>
  </tr>
  <tr>
    <td>&gt;=' . $internal_average_thresholds['checkInput_high'] . '</td>
    <td>3</td>
  </tr>
  <tr>
    <td>' . ($internal_average_thresholds['checkInput_medium'] ) . ' to ' . ($internal_average_thresholds['checkInput_high'] - 1) . '</td>
    <td>2</td>
  </tr>
  <tr>
    <td>' . $internal_average_thresholds['checkInput_low'] . ' to ' . ($internal_average_thresholds['checkInput_medium'] - 1) . '</td>
    <td>1</td>
  </tr>
</table>

<h3>University Exam Pass Percentage Attainment Level</h3>
<table>
  <tr>
    <th>%</th>
    <th>Attainment Level</th>
  </tr>
  <tr>
    <td>&gt;=' . $university_exam_thresholds['checkInputuni_high'] . '</td>
    <td>3</td>
  </tr>
  <tr>
    <td>' . ($university_exam_thresholds['checkInputuni_medium'] ) . ' to ' . ($university_exam_thresholds['checkInputuni_high'] - 1) . '</td>
    <td>2</td>
  </tr>
  <tr>
    <td>' . $university_exam_thresholds['checkInputuni_low'] . ' to ' . ($university_exam_thresholds['checkInputuni_medium'] - 1) . '</td>
    <td>1</td>
  </tr>
</table>

<h3>CO Attainment Status Calculation (Target: ' . getTargetValue() . ')</h3>
<h4 style = "color:red;"> Target / CO_Attainment = Attainment Percentage </h4>
<table>
  <tr>
    <th>Attainment Percentage</th>
    <th>Attainment Status</th>
  </tr>
  <tr>
    <td>' .getAttainmentPercentageThreshold() . ' % </td>
    <td>Attained</td>
  </tr>
  <tr>
    <td>&lt;' .getAttainmentPercentageThreshold() . ' % </td>
    <td>Not Attained</td>
  </tr>
</table>';
    // $output .='
    // <h2>CO-QUESTION(%) TABLE</h2>';
    // $output .='<div class="table-container"></table></div>';
    // $output .='<table>
    // <thead>
    //   <tr>
    //     <th>COMBINATION</th>';
    //     for($i=1;$i<=$co_no;$i++){
    //       $output .= '<th>CO' . $i .'</th>';
    //     }
    //     $output .=' </tr>
    // </thead>
    // ';
    // $query = "SELECT * FROM $tableName2";
    
    // // Execute the query and fetch the results
    // $result = mysqli_query($conn, $query);
    // // Handle the results as needed
    // if ($result->num_rows > 0) {
    //     // Output data of each row
    //     while ($row = mysqli_fetch_array($result)) {
    //         $output .='
    //         <tr>
    //             <td>'.$row["combinations"].'</td>';
    //             for($i=1;$i<=$co_no;$i++){
    //               $formattedValue = number_format($row['co' . $i], ($row['co' . $i] == intval($row['co' . $i]) ? 0 : 2));
    //               $output .= '<td>' . $formattedValue . '%' . '</td>';
    //             }

    //         $output .= '</tr>';
    //     }

    // }
    // $output .='</table></div>';

    $output .= '<br><br>
    <br><br><br><br>

    <h2>CO-ATTAINMENT TABLE</h2>
    <div class="table-container">
    <table>
    <thead>
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
</thead>';

$query = "SELECT CO, AVERAGE1, ATT1, RESULT, ATT2, CO_ATT, TARG, ATT_STATUS FROM $tableName4";
$result = mysqli_query($conn, $query);

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = mysqli_fetch_assoc($result)) { // Use mysqli_fetch_assoc to fetch an associative array
        $output .= '<tr>';
        $output .= '<td>' . strtoupper($row['CO']) . '</td>';
        $output .= '<td>' . $row['AVERAGE1'] . '</td>';
        $output .= '<td>' . $row['ATT1'] . '</td>';
        $output .= '<td>' . $row['RESULT'] . '</td>';
        $output .= '<td>' . $row['ATT2'] . '</td>';
        $output .= '<td>' . $row['CO_ATT'] . '</td>';
        $output .= '<td>' . $row['TARG'] . '</td>';
        $output .= '<td>' . $row['ATT_STATUS'] . '</td>';
        $output .= '</tr>';
    }
}

// Now $options contains the HTML table rows based on the database query results.

$output .='</table>
</div>';

$output .= '<br><br>

    <h2>PO/PSO-ATTAINMENT TABLE</h2>
    <div class="table-container">
    <table>
    <thead>
    <tr>
    <th>CO</th>
    <th>CO ATTAINMENT</th>';

    for ($i = 1; $i <= $poColumnCount; $i++) {
        $output .= '<th>PO' . $i . '</th>';
    }
    for ($i = 1; $i <= $psoColumnCount; $i++) {
        $output .= '<th>PSO' . $i . '</th>';
        }
$output .='</tr> 
</thead>';

$query = "SELECT * FROM $tableName5";
$result = mysqli_query($conn, $query);

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = mysqli_fetch_assoc($result)) { // Use mysqli_fetch_assoc to fetch an associative array
        $output .= '<tr>';
        $output .= '<td>' . $row['CO'] . '</td>';
        $output .= '<td>' . $row['CO_ATTAINMENT'] . '</td>';
        for ($i = 1; $i <= $poColumnCount; $i++) {
          $formattedValue = htmlspecialchars(($row['PO' . $i])!== null ? $row['PO' . $i]: '-') ;
          $output .= '<td>' . $formattedValue .  '</td>';
          }
          for ($i = 1; $i <= $psoColumnCount; $i++) {
              $formattedValue = htmlspecialchars(($row['PSO' . $i])!== null ? $row['PSO' . $i]: '-') ;
              $output .= '<td>' . $formattedValue .  '</td>';
          }
        $output .= '</tr>';
    }
}

// Now $options contains the HTML table rows based on the database query results.

$output .='</table>
</div>
</center>
</body>
';


    $dompdf->loadHtml($output);

$dompdf->setPaper("A4", "portrait");
$dompdf->render();

$dompdf->stream("invoice.pdf", ["Attachment" => 0]);
$output = $dompdf->output();
file_put_contents("file.pdf", $output);
?>
