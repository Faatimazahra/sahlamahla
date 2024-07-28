<?php
// Include the database connection file
require_once 'db_connect.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust the path if needed

// Initialize a variable to hold the message
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message_content = htmlspecialchars($_POST['message']);

    try {
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (:name, :email, :message)");

        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':message', $message_content);

        // Execute the statement
        if ($stmt->execute()) {
            // Create a new PHPMailer instance
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sahlamahla35@gmail.com';
                $mail->Password   = 'sppw vpje mffj pgev';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom($email, $name);
                $mail->addAddress('sahlamahla35@gmail.com'); // Add a recipient

                // Content
                $mail->isHTML(false); // Set email format to HTML
                $mail->Subject = 'New Contact Message from ' . $name;
                $mail->Body    = "Name: $name\nEmail: $email\nMessage:\n$message_content";

                $mail->send();
                $message = 'Message sent successfully.';
            } catch (Exception $e) {
                $message = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
            }
        } else {
            $message = 'Failed to save message to database.';
        }
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
    }
}
?>

<?php require_once __DIR__ . './header.php'; ?>

<!-- Contact Start -->
    <div class="container py-5">
        <div class="contact-detail position-relative p-5">
            <div class="row g-5 mb-5 justify-content-center">
                <div class="col-xl-4 col-lg-6 wow fadeIn" data-wow-delay=".3s">
                    <div class="d-flex bg-light p-3 rounded">
                        <div class="flex-shrink-0 btn-square bg-secondary rounded-circle" style="width: 64px; height: 64px;">
                            <i class="fas fa-map-marker-alt text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="text-primary">Address</h4>
                            <a href="https://goo.gl/maps/Zd4BCynmTb98ivUJ6" target="_blank" class="h5">Hay Riyad, Rabat</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6 wow fadeIn" data-wow-delay=".5s">
                    <div class="d-flex bg-light p-3 rounded">
                        <div class="flex-shrink-0 btn-square bg-secondary rounded-circle" style="width: 64px; height: 64px;">
                            <i class="fa fa-phone text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="text-primary">Appelez-nous</h4>
                            <a class="h5" href="tel:+0123456789" target="_blank">+0000000</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6 wow fadeIn" data-wow-delay=".7s">
                    <div class="d-flex bg-light p-3 rounded">
                        <div class="flex-shrink-0 btn-square bg-secondary rounded-circle" style="width: 64px; height: 64px;">
                            <i class="fa fa-envelope text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="text-primary">E-mail</h4>
                            <a class="h5" href="mailto:info@example.com" target="_blank">Sahlamahla@gmail.com</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-5">
                <div class="col-lg-6 wow fadeIn" data-wow-delay=".3s">
                    <div class="p-5 h-100 rounded contact-map">
                        <iframe class="rounded w-100 h-100" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3025.4710403339755!2d-73.82241512404069!3d40.685622471397615!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c26749046ee14f%3A0xea672968476d962c!2s123rd%20St%2C%20Queens%2C%20NY%2C%20USA!5e0!3m2!1sen!2sbd!4v1686493221834!5m2!1sen!2sbd" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeIn" data-wow-delay=".5s">
                    <div class="p-5 rounded contact-form">
                        <form action="contact.php" method="POST">
                            <div class="mb-4">
                                <input type="text" class="form-control border-0 py-3" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="mb-4">
                                <input type="email" class="form-control border-0 py-3" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="mb-4">
                                <textarea class="w-100 form-control border-0 py-3" name="message" rows="6" cols="10" placeholder="Message" required></textarea>
                            </div>
                            <div class="text-start">
                                <button class="btn bg-primary text-white py-3 px-5" type="submit">Send Message</button>
                            </div>
                        </form>
                        <!-- Display success or error message -->
                        <?php if ($message): ?>
                            <div class="alert alert-info mt-3">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>
<!-- Contact End -->
<?php require_once __DIR__ . './footer.php'; ?>
