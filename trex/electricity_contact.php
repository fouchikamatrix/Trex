<?php
$page_title = "Electricity Contact";
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $user_data['email'] ?? '';

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $priority = $_POST['priority'] ?? 'normal';
    $category = $_POST['category'] ?? 'general';
    
    // Validation
    if (empty($subject) || empty($message)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (strlen($subject) < 5) {
        $error_message = 'Subject must be at least 5 characters long.';
    } elseif (strlen($message) < 10) {
        $error_message = 'Message must be at least 10 characters long.';
    } else {
        try {
            // Store in database
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (user_id, subject, message, priority, category, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $subject,
                $message,
                $priority,
                $category
            ]);
            
            // Email details
            $to = "saskijackson@gmail.com";
            $email_subject = "VoltGaz Contact - " . ucfirst($category) . ": " . $subject;
            $email_body = "New contact message from VoltGaz application\n\n";
            $email_body .= "User: $user_name\n";
            $email_body .= "Email: $user_email\n";
            $email_body .= "Priority: " . ucfirst($priority) . "\n";
            $email_body .= "Category: " . ucfirst($category) . "\n\n";
            $email_body .= "Subject: $subject\n\n";
            $email_body .= "Message:\n$message\n\n";
            $email_body .= "---\n";
            $email_body .= "Sent from VoltGaz Contact System";
            
            $headers = "From: noreply@voltgaz.com\r\n";
            $headers .= "Reply-To: $user_email\r\n";
            $headers .= "X-Mailer: VoltGaz Contact System\r\n";
            
            // Send email
            if (mail($to, $email_subject, $email_body, $headers)) {
                $success_message = 'Thank you! Your message has been sent successfully. We will get back to you within 24 hours.';
                // Clear form data
                $_POST = [];
            } else {
                $error_message = 'Message saved but email notification failed. We will still review your message.';
            }
        } catch (PDOException $e) {
            $error_message = 'Sorry, there was an error processing your request. Please try again.';
            error_log("Contact form error: " . $e->getMessage());
        }
    }
}

// Set additional CSS for contact page styling
$additional_css = '
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .contact-container {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .contact-header {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
        backdrop-filter: blur(20px);
        color: white;
        padding: 40px;
        border-radius: 20px;
        margin-bottom: 35px;
        text-align: center;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
    }

    .contact-header::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, 
            rgba(102, 126, 234, 0.1) 0%, 
            rgba(118, 75, 162, 0.1) 50%,
            rgba(102, 126, 234, 0.1) 100%);
        background-size: 400% 400%;
        animation: gradientShift 10s ease infinite;
        z-index: -1;
    }

    .contact-header h1 {
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 800;
        background: linear-gradient(135deg, #ffffff 0%, rgba(255, 255, 255, 0.8) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-family: "Poppins", sans-serif;
    }

    .contact-header p {
        font-size: 1.2rem;
        opacity: 0.9;
        font-weight: 500;
    }

    .contact-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
    }

    .contact-form-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
    }

    .contact-form-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.1) 0%, 
            rgba(255, 255, 255, 0.05) 50%,
            rgba(255, 255, 255, 0.1) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }

    .user-info {
        background: rgba(102, 126, 234, 0.1);
        backdrop-filter: blur(10px);
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
        border: 1px solid rgba(102, 126, 234, 0.3);
        position: relative;
    }

    .user-info::before {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        border-radius: 0 2px 2px 0;
    }

    .user-info h3 {
        color: white;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 10px;
        font-family: "Poppins", sans-serif;
    }

    .user-info p {
        color: rgba(255, 255, 255, 0.9);
        margin: 5px 0;
        font-weight: 500;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 25px;
    }

    .form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 700;
        color: white;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: "Poppins", sans-serif;
    }

    .form-group label i {
        color: rgba(102, 126, 234, 0.8);
        margin-right: 8px;
    }

    .input-wrapper {
        position: relative;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 18px 20px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        font-size: 1rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        color: white;
        font-weight: 500;
        font-family: "Inter", sans-serif;
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: rgba(102, 126, 234, 0.6);
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        outline: none;
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }

    .form-group select {
        cursor: pointer;
    }

    .form-group select option {
        background: #333;
        color: white;
        padding: 10px;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
        font-family: "Inter", sans-serif;
    }

    .priority-selector {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }

    .priority-option {
        flex: 1;
        position: relative;
    }

    .priority-option input[type="radio"] {
        display: none;
    }

    .priority-label {
        display: block;
        padding: 15px 20px;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
    }

    .priority-option input[type="radio"]:checked + .priority-label {
        background: rgba(102, 126, 234, 0.2);
        border-color: rgba(102, 126, 234, 0.6);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
    }

    .priority-label:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-1px);
    }

    .priority-label.low {
        border-color: rgba(34, 197, 94, 0.3);
    }

    .priority-label.normal {
        border-color: rgba(102, 126, 234, 0.3);
    }

    .priority-label.high {
        border-color: rgba(239, 68, 68, 0.3);
    }

    .priority-option input[type="radio"]:checked + .priority-label.low {
        background: rgba(34, 197, 94, 0.2);
        border-color: rgba(34, 197, 94, 0.6);
    }

    .priority-option input[type="radio"]:checked + .priority-label.high {
        background: rgba(239, 68, 68, 0.2);
        border-color: rgba(239, 68, 68, 0.6);
    }

    .submit-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        width: 100%;
        padding: 20px;
        color: white;
        border: none;
        border-radius: 15px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        margin-top: 20px;
        position: relative;
        overflow: hidden;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-family: "Poppins", sans-serif;
    }

    .submit-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .submit-btn:hover::before {
        left: 100%;
    }

    .submit-btn:hover {
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        transform: translateY(-3px);
    }

    .submit-btn:active {
        transform: translateY(-1px);
    }

    .submit-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .alert {
        padding: 20px 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        backdrop-filter: blur(10px);
        border: 1px solid;
        animation: slideDown 0.5s ease-out;
    }

    .alert.success {
        background: rgba(34, 197, 94, 0.1);
        color: #bbf7d0;
        border-color: rgba(34, 197, 94, 0.3);
    }

    .alert.error {
        background: rgba(239, 68, 68, 0.1);
        color: #fecaca;
        border-color: rgba(239, 68, 68, 0.3);
    }

    @keyframes slideDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .contact-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-top: 40px;
    }

    .info-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        padding: 30px;
        border-radius: 20px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .info-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.1) 0%, 
            rgba(255, 255, 255, 0.05) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }

    .info-card:hover::before {
        opacity: 1;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    .info-card-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 3rem;
        margin-bottom: 20px;
    }

    .info-card h3 {
        color: white;
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 15px;
        font-family: "Poppins", sans-serif;
    }

    .info-card p {
        color: rgba(255, 255, 255, 0.8);
        line-height: 1.6;
        font-weight: 500;
    }

    .loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .loading::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 20px;
        height: 20px;
        border: 2px solid transparent;
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: translate(-50%, -50%) rotate(0deg); }
        to { transform: translate(-50%, -50%) rotate(360deg); }
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .contact-container {
            padding: 0 15px;
        }

        .contact-header {
            padding: 30px 25px;
        }

        .contact-header h1 {
            font-size: 2rem;
        }

        .contact-icon {
            font-size: 3rem;
        }

        .contact-form-card {
            padding: 30px 25px;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .priority-selector {
            flex-direction: column;
            gap: 10px;
        }

        .contact-info {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .contact-header {
            padding: 25px 20px;
        }

        .contact-header h1 {
            font-size: 1.8rem;
        }

        .contact-form-card {
            padding: 25px 20px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 15px 18px;
            font-size: 0.95rem;
        }

        .submit-btn {
            padding: 18px;
            font-size: 1rem;
        }
    }
';

// Set the content to be displayed in the layout
$content = '
<div class="contact-container">
    <div class="contact-header">
        <div class="contact-icon">
            <i class="fas fa-headset"></i>
        </div>
        <h1>Electricity Support</h1>
        <p>Get in touch with our electricity support team</p>
    </div>

    ' . (!empty($success_message) ? '<div class="alert success"><i class="fas fa-check-circle"></i>' . htmlspecialchars($success_message) . '</div>' : '') . '
    ' . (!empty($error_message) ? '<div class="alert error"><i class="fas fa-exclamation-triangle"></i>' . htmlspecialchars($error_message) . '</div>' : '') . '

    <div class="contact-form-card">
        <div class="user-info">
            <h3><i class="fas fa-user"></i> Sending as:</h3>
            <p><strong>Name:</strong> ' . htmlspecialchars($user_name) . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($user_email) . '</p>
        </div>

        <form method="POST" action="" id="contactForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="category">
                        <i class="fas fa-tags"></i>
                        Category
                    </label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="billing" ' . (($_POST['category'] ?? '') === 'billing' ? 'selected' : '') . '>Billing Issues</option>
                        <option value="outage" ' . (($_POST['category'] ?? '') === 'outage' ? 'selected' : '') . '>Power Outage</option>
                        <option value="meter" ' . (($_POST['category'] ?? '') === 'meter' ? 'selected' : '') . '>Meter Reading</option>
                        <option value="connection" ' . (($_POST['category'] ?? '') === 'connection' ? 'selected' : '') . '>New Connection</option>
                        <option value="technical" ' . (($_POST['category'] ?? '') === 'technical' ? 'selected' : '') . '>Technical Support</option>
                        <option value="complaint" ' . (($_POST['category'] ?? '') === 'complaint' ? 'selected' : '') . '>Complaint</option>
                        <option value="general" ' . (($_POST['category'] ?? '') === 'general' ? 'selected' : '') . '>General Inquiry</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-exclamation-circle"></i>
                        Priority Level
                    </label>
                    <div class="priority-selector">
                        <div class="priority-option">
                            <input type="radio" id="low" name="priority" value="low" ' . (($_POST['priority'] ?? 'normal') === 'low' ? 'checked' : '') . '>
                            <label for="low" class="priority-label low">
                                <i class="fas fa-clock"></i><br>
                                Low
                            </label>
                        </div>
                        <div class="priority-option">
                            <input type="radio" id="normal" name="priority" value="normal" ' . (($_POST['priority'] ?? 'normal') === 'normal' ? 'checked' : '') . '>
                            <label for="normal" class="priority-label normal">
                                <i class="fas fa-minus-circle"></i><br>
                                Normal
                            </label>
                        </div>
                        <div class="priority-option">
                            <input type="radio" id="high" name="priority" value="high" ' . (($_POST['priority'] ?? 'normal') === 'high' ? 'checked' : '') . '>
                            <label for="high" class="priority-label high">
                                <i class="fas fa-exclamation-triangle"></i><br>
                                Urgent
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="subject">
                    <i class="fas fa-envelope"></i>
                    Subject
                </label>
                <input type="text" id="subject" name="subject" required 
                       placeholder="Brief description of your issue"
                       value="' . htmlspecialchars($_POST['subject'] ?? '') . '"
                       minlength="5" maxlength="100">
            </div>

            <div class="form-group">
                <label for="message">
                    <i class="fas fa-comment-alt"></i>
                    Message
                </label>
                <textarea id="message" name="message" required 
                          placeholder="Please provide detailed information about your electricity-related inquiry or issue..."
                          minlength="10" maxlength="1000">' . htmlspecialchars($_POST['message'] ?? '') . '</textarea>
                <small style="color: rgba(255, 255, 255, 0.6); font-size: 0.85rem; margin-top: 5px; display: block;">
                    <span id="charCount">0</span>/1000 characters
                </small>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fas fa-paper-plane"></i>
                Send Message
            </button>
        </form>
    </div>

    <div class="contact-info">
        <div class="info-card">
            <div class="info-card-icon">
                <i class="fas fa-phone"></i>
            </div>
            <h3>Emergency Hotline</h3>
            <p>24/7 Emergency Support<br><strong>+1 (555) 123-4567</strong></p>
        </div>

        <div class="info-card">
            <div class="info-card-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h3>Email Support</h3>
            <p>General Inquiries<br><strong>support@voltgaz.com</strong></p>
        </div>

        <div class="info-card">
            <div class="info-card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h3>Response Time</h3>
            <p>We typically respond within<br><strong>24 hours</strong></p>
        </div>
    </div>
</div>';

// Set additional JavaScript
$additional_js = '
// Character counter for message textarea
document.getElementById("message").addEventListener("input", function() {
    const charCount = this.value.length;
    document.getElementById("charCount").textContent = charCount;
    
    if (charCount > 900) {
        document.getElementById("charCount").style.color = "#fecaca";
    } else {
        document.getElementById("charCount").style.color = "rgba(255, 255, 255, 0.6)";
    }
});

// Form submission with loading state
document.getElementById("contactForm").addEventListener("submit", function() {
    const btn = document.getElementById("submitBtn");
    btn.classList.add("loading");
    btn.innerHTML = \'<i class="fas fa-spinner fa-spin"></i> Sending...\';
    btn.disabled = true;
});

// Auto-hide success/error messages
setTimeout(function() {
    const alerts = document.querySelectorAll(".alert");
    alerts.forEach(function(alert) {
        alert.style.transition = "all 0.5s ease";
        alert.style.opacity = "0";
        alert.style.transform = "translateY(-20px)";
        setTimeout(function() {
            alert.remove();
        }, 500);
    });
}, 5000);

// Enhanced form validation
document.querySelectorAll("input, select, textarea").forEach(function(field) {
    field.addEventListener("blur", function() {
        if (this.hasAttribute("required") && !this.value.trim()) {
            this.style.borderColor = "rgba(239, 68, 68, 0.6)";
        } else {
            this.style.borderColor = "rgba(255, 255, 255, 0.2)";
        }
    });
    
    field.addEventListener("input", function() {
        if (this.style.borderColor === "rgba(239, 68, 68, 0.6)") {
            this.style.borderColor = "rgba(255, 255, 255, 0.2)";
        }
    });
});

// Initialize character counter
document.getElementById("charCount").textContent = document.getElementById("message").value.length;
';

// Include layout with the content
include 'layout.php';
?>
