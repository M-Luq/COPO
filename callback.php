<?php
session_start();
include('database.php');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
$jsonString = file_get_contents('config.json');

// Parse the JSON string into an associative array
$config = json_decode($jsonString, true);

if ($config && isset($config['web']['client_id']) && isset($config['web']['client_secret']) && isset($config['web']['redirect_uris'])) {
    $client_id = $config['web']['client_id'];
    $client_secret = $config['web']['client_secret'];
    $redirect_uri = $config['web']['redirect_uris'][0]; // Assuming you want the first redirect URI in the array
} else {
    echo "Error parsing JSON file or missing values.";
}

if (empty($_GET['code'])) {

$auth_url = "https://accounts.google.com/o/oauth2/v2/auth" .
  "?client_id=$client_id" .
  "&redirect_uri=$redirect_uri" .
  "&scope=email" .
  "&response_type=code";
// Redirect users to Google Sign-In
header("Location: $auth_url");
exit;
}
else{
    $code = $_GET['code'];
    // Exchange the code for an access token
    $token_url = "https://accounts.google.com/o/oauth2/token";
    $token_data = array(
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    );

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $token_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $token_response = curl_exec($ch);
    curl_close($ch);

    $token_data = json_decode($token_response, true);

    if (isset($token_data['access_token'])) {
        // Get user's email
        $user_info_url = "https://www.googleapis.com/oauth2/v1/userinfo?access_token=" . $token_data['access_token'];
        $user_info = json_decode(file_get_contents($user_info_url), true);
        $user_email = $user_info['email'];
        echo $user_email;
        $_SESSION['code'] = $token_data['access_token'];
        $_SESSION['email'] = $user_email;

        $checkEmailQuery = "SELECT email FROM admin";
        $checkStmt = $conn->prepare($checkEmailQuery);
        $checkStmt->execute();

        $emails = $checkStmt->fetchAll(PDO::FETCH_COLUMN, 0); // Extract emails from the result
        
        // Trim whitespace from email array
        $emails = array_map('trim', $emails);
    
        if (in_array($user_email, $emails, true)) {
            // Email matches - redirect to the matrix page
            header("Location: ./matrixeditor/matrix.php");
            exit;
        } else {
            echo "Email does not match. Please use the correct email.";
        }
    } else {
        echo "Error retrieving access token.";
    }

}
?>

