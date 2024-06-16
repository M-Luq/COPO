<?php
include 'functions_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update the target value
    updateTargetValue($_POST['target']);

    updateAttainmentPercentage($_POST['attainment_percentage_threshold']);

    // Update thresholds for checkInput
    updateCheckInputThresholds($_POST['checkInput_high'], $_POST['checkInput_medium'], $_POST['checkInput_low']);

    // Update thresholds for checkInputuni
    updateCheckInputuniThresholds($_POST['checkInputuni_high'], $_POST['checkInputuni_medium'], $_POST['checkInputuni_low']);
}

// Get current values
$target = getTargetValue();
$checkInputThresholds = getCheckInputThresholds();
$checkInputuniThresholds = getCheckInputuniThresholds();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configuration Form</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }
    form {
        background-color: #fff;
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }
    input[type="text"] {
        width: calc(100% - 10px);
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    input[type="submit"] {
        background-color: #4caf50;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    input[type="submit"]:hover {
        background-color: #45a049;
    }
</style>
</head>
<body>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <label>Target value for isAttainmentReached:</label>
    <input type="text" name="target" value="<?php echo $target; ?>"><br><br>
     
    <label>Thresholds for Internals:</label><br>
    High: <input type="text" name="checkInput_high" value="<?php echo $checkInputThresholds['checkInput_high']; ?>">
    Medium: <input type="text" name="checkInput_medium" value="<?php echo $checkInputThresholds['checkInput_medium']; ?>">
    Low: <input type="text" name="checkInput_low" value="<?php echo $checkInputThresholds['checkInput_low']; ?>"><br><br>

    <label>Thresholds for University result:</label><br>
    High: <input type="text" name="checkInputuni_high" value="<?php echo $checkInputuniThresholds['checkInputuni_high']; ?>">
    Medium: <input type="text" name="checkInputuni_medium" value="<?php echo $checkInputuniThresholds['checkInputuni_medium']; ?>">
    Low: <input type="text" name="checkInputuni_low" value="<?php echo $checkInputuniThresholds['checkInputuni_low']; ?>"><br><br>

    <label>Attainment Percentage Threshold:</label><br>
    <input type="text" name="attainment_percentage_threshold" value="<?php echo getAttainmentPercentageThreshold(); ?>"><br><br>

    <input type="submit" value="Submit">
</form>
</body>
</html>

