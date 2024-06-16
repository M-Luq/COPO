
<?php
if (isset($_FILES['excelFile'])) {
        // Unset the session variables
        unset($_SESSION['uniqueid']);
        unset($_SESSION['uniqueid2']);
        unset($_SESSION['num_assessments']);
        unset($_SESSION['num_assignments']);
        unset($_SESSION['co_no']);
    }
?>