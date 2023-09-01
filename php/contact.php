<?php
// Start session
session_start();
session_regenerate_id();

// Database credentials
$dbHost = "localhost";
$dbName = "HairCut";
$dbCharset = "utf8";
$dbUsername = "admin";
$dbPassword = "admin";

try {
    // Initialize PDO database connection
    $database = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=$dbCharset", $dbUsername, $dbPassword);

    // Set PDO error mode to exception
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle database connection error
    die("Database connection failed: " . $e->getMessage());
}

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Include necessary files for PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require "../vendor/autoload.php";

// Initialize PHPMailer
$mail = new PHPMailer(true);

// Initialize the response array
$response = array();

if (isset($_POST["contact"])) {
    // Generate and store a CSRF token (not shown in this snippet)
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

    // Sanitize input data and check for XSS payloads
    $username = sanitizeAndCheckXss($_POST['username']);
    $email = sanitizeAndCheckXss($_POST['email']);
    $subject = sanitizeAndCheckXss($_POST['subject']);
    $message = sanitizeAndCheckXss($_POST['message']);

    if (!$username || !$email || !$subject || !$message) {
        $response["success"] = false;
        $response["message"] = "XSS attack attempt detected!";
    } else if (empty($username) || empty($email) || empty($subject) || empty($message)) {
        $response["success"] = false;
        $response["message"] = "Required fields are empty.";
    } else {
        try {
            // Prepare and execute the database insert
            $InsertData = $database->prepare("INSERT INTO Contact(username, email, subject, message) VALUES(:username, :email, :subject, :message)");
            $InsertData->bindParam(':username', $username);
            $InsertData->bindParam(':email', $email);
            $InsertData->bindParam(':subject', $subject);
            $InsertData->bindParam(':message', $message);

            if ($InsertData->execute()) {
                // Send email
                try {
                    configureAndSendEmail($mail, $email, $username);

                    $response["success"] = true;
                    $response["message"] = "Message has been sent";
                } catch (Exception $e) {
                    $response["success"] = false;
                    $response["message"] = "Error sending email: " . $e->getMessage();
                }
            } else {
                $response["success"] = false;
                $response["message"] = "Database insertion failed.";
            }
        } catch (Exception $e) {
            $response["success"] = false;
            $response["message"] = "Database error: " . $e->getMessage();
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

// Function to sanitize input and check for XSS payloads
function sanitizeAndCheckXss($input) {
    $sanitized = htmlspecialchars($input);
    
    // Check for common XSS patterns (add more if needed)
    if (preg_match("/<script|alert|onerror|javascript:/i", $sanitized)) {
        return false; // XSS attempt detected
    }
    
    return $sanitized;
}

// Function to configure and send email
function configureAndSendEmail($mail, $email, $username) {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'zobirofkir30@gmail.com';
    $mail->Password = 'isoumjralpzqxrvp';
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;

    // Set sender and recipient
    $SendEmail = htmlspecialchars(filter_var($email, FILTER_SANITIZE_SPECIAL_CHARS));
    $SendName = htmlspecialchars(filter_var($username, FILTER_SANITIZE_EMAIL));
    $mail->setFrom($SendEmail, $SendName);
    $mail->addAddress('Zobirofkir19@gmail.com', $SendName);

    $mail->isHTML(true);
    $mail->Subject = 'Here is the subject';
    $mail->Body = 'This is the HTML message body <b>in bold!</b>';

    $mail->send();
}
?>
