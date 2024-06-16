<?php
include (__DIR__.'/../database.php');

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['table'])) {
    $table = $_GET['table'];

    // Sanitize the table name to prevent SQL injection
    $table = mysqli_real_escape_string($conn, $table);

    // SQL query to drop the table
    $query = "DROP TABLE $table";

    if (mysqli_query($conn, $query)) {
         echo "<script>
                alert('$table deleted successfully');
                window.location.href = 'matrix.php';
            </script>";
        
    } else {
        echo "Error dropping the table: " . mysqli_error($conn);
    }
} else {
    echo "No table name specified.";
}
?>
