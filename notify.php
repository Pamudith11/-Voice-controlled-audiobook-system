<?php
// notify.php

header("Content-Type: application/json");


$adminEmail = "pravishxp@gmail.com"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);
    $bookName = $data["bookName"] ?? "Unknown Book";

    $subject = "Book Request Notification: " . $bookName;
    
    // ඔයා කියපු විදියට Email Body එක
    $message = "
Hello Admin,

Someone just clicked 'Notify Me' for the book: " . $bookName . ".
Please look into releasing this book soon!

(User asked to get notified when this is available.)
";

    $headers = "From: no-reply@voiceaccess.com";

    // Email එක යවනවා
    if (mail($adminEmail, $subject, $message, $headers)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
}
?>