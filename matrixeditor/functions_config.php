<?php
// Database connection
include (__DIR__.'/../database.php');
$conn = new mysqli($servername, $username, $password, $dbname);

// Function to update the target value for isAttainmentReached
function updateTargetValue($target) {
    global $conn;
    $sql = "UPDATE config SET value = ? WHERE name = 'target'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $target);
    $stmt->execute();
}

//function to update the attainment reaching percentage 
function updateAttainmentPercentage($input) {
    global $conn;
    $sql = "UPDATE config SET value = ? WHERE name = 'attainment_percentage_threshold'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $input);
    $stmt->execute();
}

// Function to update input thresholds for checkInput
function updateCheckInputThresholds($high, $medium, $low) {
    global $conn;
    $sql = "UPDATE config SET value = ? WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ds", $high, $name);
    $name = 'checkInput_high';
    $stmt->execute();
    $stmt->bind_param("ds", $medium, $name);
    $name = 'checkInput_medium';
    $stmt->execute();
    $stmt->bind_param("ds", $low, $name);
    $name = 'checkInput_low';
    $stmt->execute();
}

// Function to update input thresholds for checkInputuni
function updateCheckInputuniThresholds($high, $medium, $low) {
    global $conn;
    $sql = "UPDATE config SET value = ? WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ds", $high, $name);
    $name = 'checkInputuni_high';
    $stmt->execute();
    $stmt->bind_param("ds", $medium, $name);
    $name = 'checkInputuni_medium';
    $stmt->execute();
    $stmt->bind_param("ds", $low, $name);
    $name = 'checkInputuni_low';
    $stmt->execute();
}

// Function to get the current target value for isAttainmentReached
function getTargetValue() {
    global $conn;
    $sql = "SELECT value FROM config WHERE name = 'target'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['value'];
}

function getAttainmentPercentageThreshold() {
    global $conn;
    $sql = "SELECT value FROM config WHERE name = 'attainment_percentage_threshold'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['value'];
}

// Function to get the input thresholds for checkInput
function getCheckInputThresholds() {
    global $conn;
    $thresholds = array();
    $sql = "SELECT name, value FROM config WHERE name LIKE 'checkInput\_%'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $thresholds[$row['name']] = $row['value'];
    }
    return $thresholds;
}

// Function to get the input thresholds for checkInputuni
function getCheckInputuniThresholds() {
    global $conn;
    $thresholds = array();
    $sql = "SELECT name, value FROM config WHERE name LIKE 'checkInputuni\_%'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $thresholds[$row['name']] = $row['value'];
    }
    return $thresholds;
}
?>
