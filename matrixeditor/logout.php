<?php
session_start();

// Revoke the OAuth 2.0 token granted to your application
if (isset($_SESSION['code'])) {
    $token = $_SESSION['code'];

    $revokeUrl = 'https://accounts.google.com/o/oauth2/revoke?token=' . $token;
    $ch = curl_init($revokeUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // You can check the response to ensure the token was revoked successfully

    // Clear the session
    session_unset();
    session_destroy();
}

header("location: ../index.php");
exit;
?>
