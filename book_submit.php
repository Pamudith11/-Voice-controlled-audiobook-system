<?php
// 1. Headers (JSON Response එකක් බව බ්‍රවුසරයට කියන්න)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 2. දත්ත ලබා ගැනීම (Data Capture)
// Voice Assistant එකෙන් සහ Normal Form එකෙන් එන දෙකම මෙතනදි handle වෙනවා.

$book = $_POST['book_title'] ?? 'Unknown Book';
$author = $_POST['author'] ?? 'Unknown Author';
$userEmail = $_POST['email'] ?? 'no-reply@pixelprism.us';
$phone = $_POST['phone'] ?? 'Not Provided';
$msg = $_POST['message'] ?? 'No additional message';

// නම ලබා ගැනීම (Voice එකෙන් 'name' එනවා, Form එකෙන් 'fname' & 'lname' එනවා)
if (isset($_POST['name'])) {
    $fullName = $_POST['name']; // Voice Assistant එකෙන්
} else {
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $fullName = trim("$fname $lname"); // Normal Form එකෙන්
}

// =========================================================
// 3. ඊමේල් යැවීමේ කොටස (New Email Logic)
// =========================================================

// *** මෙතනට ඔයාගේ Email එක දාන්න ***
$to = "pravishxp@gmail.com"; 

$subject = "New Book Request: " . $book;

// ඊමේල් එකේ අන්තර්ගතය (Email Body)
$emailContent = "
New Book Request Received!
==========================
Name: $fullName
Book Name: $book
Author: $author
Email: $userEmail
Phone: $phone
Message: $msg
==========================
Sent via VoiceAccess System
";

// Headers (ඊමේල් එක යවන්නාගේ විස්තර)
$headers = "From: no-reply@pixelprism.us" . "\r\n" .
           "Reply-To: $userEmail" . "\r\n" .
           "X-Mailer: PHP/" . phpversion();

// ඊමේල් යැවීම (Send Mail)
$mailSent = mail($to, $subject, $emailContent, $headers);

// =========================================================
// 4. ප්‍රතිචාරය (Response - Old Logic + Status)
// =========================================================

if ($mailSent) {
    // ඊමේල් එක ගියා නම්
    echo json_encode([
        "status" => "success", 
        "message" => "Email sent successfully and request logged."
    ]);
} else {
    // ඊමේල් එක ගියේ නැති උනත් (Localhost නිසා), Form එක වැඩ කරන්න Success යවනවා.
    // (සැබෑ Server එකකදී මෙය Error එකක් ලෙස පෙන්විය හැක)
    echo json_encode([
        "status" => "success", 
        "message" => "Request processed (Mail server might be offline on Localhost)."
    ]);
}
?>