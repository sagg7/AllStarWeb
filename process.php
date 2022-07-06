<?php
require 'mailer/PHPMailerAutoload.php';

$mail = new PHPMailer;

// Form Data
$fullname = $_REQUEST['fullname'] ;
$email = $_REQUEST['email'] ;
$mobile = $_REQUEST['mobile'];
$category = $_REQUEST['category'] ;
$message = $_REQUEST['message'] ;

$mailbody = 'New Lead Enquiry' . PHP_EOL . PHP_EOL .
            'Name: ' . $fullname . '' . PHP_EOL .
            'Mobile:' . $mobile . '' . PHP_EOL .
            'Interested In: ' . $category . '' . PHP_EOL .
            'Message: ' . $message . '' . PHP_EOL;

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
$mail->SMTPAuth = true; // Enable SMTP authentication
$mail->Username = 'tricksmob.tk@gmail.com'; // SMTP username
$mail->Password = '33110055'; // SMTP password
$mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587; // TCP port to connect to

$mail->setFrom('admin@domain.com', 'WebMaster'); // Admin ID
$mail->addAddress('owner@domain.com', 'Business Owner'); // Business Owner ID
$mail->addReplyTo($email, $fullname); // Form Submitter's ID

$mail->isHTML(false); // Set email format to HTML

$mail->Subject = 'New Lead Enquiry';
$mail->Body    = $mailbody;

if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}