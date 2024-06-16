<?php 
session_start();
include (__DIR__.'/../database.php');
if (isset($_SESSION['code'])) {?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Management</title>
    <link rel="stylesheet" href="./style2.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
</head>
<body>
<button class="matrixbtn" type="button" onclick="window.location.href='logout.php'">Logout</button> 
    <h1> Table Updation </h1>
    <?php
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
   
    ?>

    <table border="1"> 
        <?php
        if (isset($_GET['table'])) {
            $table = $_GET['table'];
            $_SESSION['mattable'] = $table;
            $query = "SELECT * FROM $table";
            $result = mysqli_query($conn, $query);

            if ($result -> num_rows>0) {
                $row = mysqli_fetch_assoc($result);
                echo "<tr>";
                echo "<th>ID</th>";
                echo "<th>CO</th>";
                foreach ($row as $key => $value) {
                    if (strpos($key, 'PO') === 0) {
                        echo "<th>$key</th>";
                    }
                }
                foreach ($row as $key => $value) {
                    if (strpos($key, 'PSO') === 0) {
                        echo "<th>$key</th>";
                    }
                }
                echo "<th>Delete</th>";
                echo "</tr>";
            }
            else {
                echo "<script>
                alert('$table has no data');
                window.location.href = 'matrix.php';
            </script>";
            }

            mysqli_data_seek($result, 0);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<form method='POST' action='deleterow.php'>";
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td><input type='text' name='CO' value='{$row['CO']}' required></td>";
                foreach ($row as $key => $value) {
                    if (strpos($key, 'PO') === 0) {
                        echo "<td><input type='number' name='$key' value='$value' required></td>";
                    }
                }
                foreach ($row as $key => $value) {
                    if (strpos($key, 'PSO') === 0) {
                        echo "<td><input type='number' name='$key' value='$value' required></td>";
                    }
                }
                
   echo " <input type='hidden' name='table' value='$table'>
     <input type='hidden' name='id' value='{$row['id']}'>
     <input type='submit' name='edit' style='display : none; '>";
    
     echo " <td><input type='hidden' name='table' value='$table'>
     <input type='hidden' name='id' value='{$row['id']}'>
     <input type='submit' name='delete' value='Delete'></td>
     </tr>
    
 </form>";


 
 }
}?>
</table>
<center>
<button class="delete-button" type="button" onclick="window.location.href='deletetable.php?table=<?php echo $table; ?>'"> Delete table</button><br>
</center>
</body>
</html>
<center>
<strong><p style="color:red;">Refresh the page after you insert a new row</p></strong>
<h1>Insert a new Row</h1>
<?php
if(isset($_SESSION['mattable'])){?>
<iframe src="insert.php" width="600" height="600" frameborder="0"></iframe>
<?php }?>
</center>

<?php
}
else{
  echo "<script>
                alert('You dont have access to the website');
                window.location.href = '../index.php';
            </script>";
}
?>
