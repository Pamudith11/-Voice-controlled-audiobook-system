<?php
header("Content-Type: application/json");

// Admin email address
$adminEmail = "pravishxp@gmail.com";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Collect form data safely
    $fname      = $_POST["fname"] ?? "";
    $lname      = $_POST["lname"] ?? "";
    $email      = $_POST["email"] ?? "";
    $phone      = $_POST["phone"] ?? "";
    $book_title = $_POST["book_title"] ?? "";
    $author     = $_POST["author"] ?? "";
    $message    = $_POST["message"] ?? "";

    // Construct the email content
    $fullMessage = "
New Book Request Received:

Name: $fname $lname
Email: $email
Phone: $phone

Requested Book: $book_title
Author: $author

Additional Notes:
$message
";

    $headers = "From: no-reply@pixelprism.us"; // Update this if you have a real domain email

    // Send Email
    $sent = mail($adminEmail, "New AudioBook Request: $book_title", $fullMessage, $headers);

    if ($sent) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Mail sending failed. Check server logs."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request"]);
}
?>