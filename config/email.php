<?php
// Email Configuration for Portfolio
class EmailSender {
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_username = 'anikanawer010@gmail.com';
    private $smtp_password = ''; // You need to set Gmail App Password here
    private $from_email = 'anikanawer010@gmail.com';
    private $from_name = 'Portfolio Admin';
    
    public function sendEmail($to, $subject, $message) {
        // Use PHPMailer if available, otherwise fallback to basic mail()
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return $this->sendWithPHPMailer($to, $subject, $message);
        } else {
            return $this->sendWithBasicMail($to, $subject, $message);
        }
    }
    
    private function sendWithBasicMail($to, $subject, $message) {
        $headers = "From: {$this->from_name} <{$this->from_email}>\r\n";
        $headers .= "Reply-To: {$this->from_email}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        return mail($to, $subject, $message, $headers);
    }
    
    private function sendWithPHPMailer($to, $subject, $message) {
        // PHPMailer implementation would go here
        // For now, fallback to basic mail
        return $this->sendWithBasicMail($to, $subject, $message);
    }
    
    public function testEmailConfiguration() {
        // Test if email is configured
        $test_result = @mail('test@example.com', 'Test', 'Test message', 'From: test@test.com');
        return $test_result !== false;
    }
}
?>
