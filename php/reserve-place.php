<?php
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Initialize PDO database connection
$username = "admin";
$password = "admin";
try {
    $database = new PDO("mysql:host=localhost;dbname=HairCut;charset=utf8", $username, $password);
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize PHPMailer
$mail = new PHPMailer(true);

$response = []; // Initialize the response array

if (isset($_POST["reserve"])) {
    // Sanitize input data
    $fullname = htmlspecialchars($_POST["fullName"]);
    $email = htmlspecialchars($_POST["email"]);
    $phoneNumber = htmlspecialchars($_POST["phoneNumber"]);
    $haircutType = htmlspecialchars($_POST["haircutType"]);
    $appointmentDate = htmlspecialchars($_POST["appointmentDate"]);
    $appointmentTime = htmlspecialchars($_POST["appointmentTime"]);
    $additionalNotes = htmlspecialchars($_POST["additionalNotes"]);

    // Check for existing reservations
    $checkExistingReservation = $database->prepare("SELECT COUNT(*) FROM Reservation WHERE  phoneNumber = :phoneNumber AND appointmentDate = :appointmentDate AND appointmentTime = :appointmentTime ");
    $checkExistingReservation->bindParam(":phoneNumber", $phoneNumber);
    $checkExistingReservation->bindParam(":appointmentDate", $appointmentDate);
    $checkExistingReservation->bindParam(":appointmentTime", $appointmentTime);
    $checkExistingReservation->execute();
    $checkColumn = $checkExistingReservation->fetchColumn();

    if ($checkColumn > 0) {
        echo "This reservation is full";
    } else {
        // Prepare and execute the database insert
        $insertData = $database->prepare("INSERT INTO Reservation (fullname, email, phoneNumber, haircutType, appointmentDate, appointmentTime, additionalNotes)
        VALUES (:fullname, :email, :phoneNumber, :haircutType, :appointmentDate, :appointmentTime, :additionalNotes)");

        $insertData->bindParam(':fullname', $fullname);
        $insertData->bindParam(':email', $email);
        $insertData->bindParam(':phoneNumber', $phoneNumber);
        $insertData->bindParam(':haircutType', $haircutType);
        $insertData->bindParam(':appointmentDate', $appointmentDate);
        $insertData->bindParam(':appointmentTime', $appointmentTime);
        $insertData->bindParam(':additionalNotes', $additionalNotes);

        if ($insertData->execute()) {
            try {
                // Server settings for sending email
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'zobirofkir30@gmail.com';
                $mail->Password = 'kecyhzdgblfawogi';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $reserveEmail = htmlspecialchars(filter_var($email, FILTER_SANITIZE_EMAIL));
                $reserveName = htmlspecialchars(filter_var($fullname, FILTER_SANITIZE_SPECIAL_CHARS));

                $mail->setFrom($reserveEmail, $reserveName);
                $mail->addAddress('Zobirofkir30@gmail.com', 'Zobir');

                $mail->isHTML(true);
                $mail->Subject = 'HairCut Reservation';
                $mail->Body = 'Name: ' . $fullname . ', Email: ' . $email . ', Phone Number: ' . $phoneNumber . ', Haircut Type: ' . $haircutType . ', Appointment Date: ' . $appointmentDate . ', Appointment Time: ' . $appointmentTime . ', Additional Notes: ' . $additionalNotes;

                $mail->send();
                $response["success"] = true;
                $response["message"] = "You are reserved successfully";
                echo $response["message"];
            } catch (Exception $e) {
                $response["success"] = false;
                $response["message"] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            // Error inserting data into the database
            $response["success"] = false;
            $response["message"] = "Error submitting reservation.";
        }
    }
}

?>

