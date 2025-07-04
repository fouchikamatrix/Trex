<?php
$page_title = "Contact électricité";
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';
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
        $error_message = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (strlen($subject) < 5) {
        $error_message = 'Le sujet doit contenir au moins 5 caractères.';
    } elseif (strlen($message) < 10) {
        $error_message = 'Le message doit contenir au moins 10 caractères.';
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
            
            $success_message = 'Merci ! Votre message a été envoyé avec succès. Nous vous répondrons dans les 24 heures.';
            // Clear form data
            $_POST = [];
        } catch (PDOException $e) {
            $error_message = 'Désolé, une erreur s\'est produite lors du traitement de votre demande. Veuillez réessayer.';
            error_log("Electricity contact form error: " . $e->getMessage());
        }
    }
}

// Set additional CSS for electricity contact page styling
$additional_css = '
    body {
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
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
        color: #1a1a1a;
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
            rgba(77, 171, 247, 0.1) 0%, 
            rgba(33, 150, 243, 0.1) 50%,
            rgba(77, 171, 247, 0.1) 100%);
        background-size: 400% 400%;
        animation: gradientShift 10s ease infinite;
        z-index: -1;
    }

    .contact-header h1 {
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 800;
        color: #1a1a1a;
        font-family: "Poppins", sans-serif;
    }

    .contact-header p {
        font-size: 1.2rem;
        color: #2d2d2d;
        font-weight: 500;
    }

    .contact-icon {
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
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
        background: rgba(77, 171, 247, 0.1);
        backdrop-filter: blur(10px);
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
        border: 1px solid rgba(77, 171, 247, 0.3);
        position: relative;
    }

    .user-info::before {
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        border-radius: 0 2px 2px 0;
    }

    .user-info h3 {
        color: #1a1a1a;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 10px;
        font-family: "Poppins", sans-serif;
    }

    .user-info p {
        color: #2d2d2d;
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
        color: #1a1a1a;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: "Poppins", sans-serif;
    }

    .form-group label i {
        color: rgba(77, 171, 247, 0.8);
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
        color: #1a1a1a;
        font-weight: 500;
        font-family: "Inter", sans-serif;
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: rgba(26, 26, 26, 0.5);
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: rgba(77, 171, 247, 0.6);
        box-shadow: 0 0 0 4px rgba(77, 171, 247, 0.1);
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
        color: #404040;
        font-size: 0.9rem;
    }

    .priority-option input[type="radio"]:checked + .priority-label {
        background: rgba(77, 171, 247, 0.2);
        border-color: rgba(77, 171, 247, 0.6);
        color: #1a1a1a;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(77, 171, 247, 0.2);
    }

    .priority-label:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-1px);
    }

    .priority-label.low {
        border-color: rgba(34, 197, 94, 0.3);
    }

    .priority-label.normal {
        border-color: rgba(77, 171, 247, 0.3);
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
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
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
        box-shadow: 0 15px 35px rgba(77, 171, 247, 0.4);
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
        color: #16a34a;
        border-color: rgba(34, 197, 94, 0.3);
    }

    .alert.error {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
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
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 3rem;
        margin-bottom: 20px;
    }

    .info-card h3 {
        color: #1a1a1a;
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 15px;
        font-family: "Poppins", sans-serif;
    }

    .info-card p {
        color: #2d2d2d;
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
            <i class="fas fa-bolt"></i>
        </div>
        <h1>Support électricité</h1>
        <p>Contactez notre équipe de support électricité</p>
    </div>

    ' . (!empty($success_message) ? '<div class="alert success"><i class="fas fa-check-circle"></i>' . htmlspecialchars($success_message) . '</div>' : '') . '
    ' . (!empty($error_message) ? '<div class="alert error"><i class="fas fa-exclamation-triangle"></i>' . htmlspecialchars($error_message) . '</div>' : '') . '

    <div class="contact-form-card">
        <div class="user-info">
            <h3><i class="fas fa-user"></i> Envoi en tant que :</h3>
            <p><strong>Nom :</strong> ' . htmlspecialchars($user_name) . '</p>
            <p><strong>Email :</strong> ' . htmlspecialchars($user_email) . '</p>
        </div>

        <form method="POST" action="" id="contactForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="category">
                        <i class="fas fa-tags"></i>
                        Catégorie
                    </label>
                    <select id="category" name="category" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option value="billing" ' . (($_POST['category'] ?? '') === 'billing' ? 'selected' : '') . '>Problèmes de facturation</option>
                        <option value="outage" ' . (($_POST['category'] ?? '') === 'outage' ? 'selected' : '') . '>Panne électrique</option>
                        <option value="meter" ' . (($_POST['category'] ?? '') === 'meter' ? 'selected' : '') . '>Lecture de compteur</option>
                        <option value="connection" ' . (($_POST['category'] ?? '') === 'connection' ? 'selected' : '') . '>Nouvelle connexion</option>
                        <option value="voltage" ' . (($_POST['category'] ?? '') === 'voltage' ? 'selected' : '') . '>Problème de tension</option>
                        <option value="safety" ' . (($_POST['category'] ?? '') === 'safety' ? 'selected' : '') . '>Sécurité électrique</option>
                        <option value="maintenance" ' . (($_POST['category'] ?? '') === 'maintenance' ? 'selected' : '') . '>Maintenance</option>
                        <option value="complaint" ' . (($_POST['category'] ?? '') === 'complaint' ? 'selected' : '') . '>Réclamation</option>
                        <option value="general" ' . (($_POST['category'] ?? '') === 'general' ? 'selected' : '') . '>Demande générale</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-exclamation-circle"></i>
                        Niveau de priorité
                    </label>
                    <div class="priority-selector">
                        <div class="priority-option">
                            <input type="radio" id="low" name="priority" value="low" ' . (($_POST['priority'] ?? 'normal') === 'low' ? 'checked' : '') . '>
                            <label for="low" class="priority-label low">
                                <i class="fas fa-clock"></i><br>
                                Faible
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
                    Sujet
                </label>
                <input type="text" id="subject" name="subject" required 
                       placeholder="Brève description de votre problème"
                       value="' . htmlspecialchars($_POST['subject'] ?? '') . '"
                       minlength="5" maxlength="100">
            </div>

            <div class="form-group">
                <label for="message">
                    <i class="fas fa-comment-alt"></i>
                    Message
                </label>
                <textarea id="message" name="message" required 
                          placeholder="Veuillez fournir des informations détaillées sur votre demande ou problème lié à l\'électricité..."
                          minlength="10" maxlength="1000">' . htmlspecialchars($_POST['message'] ?? '') . '</textarea>
                <small style="color: #404040; font-size: 0.85rem; margin-top: 5px; display: block;">
                    <span id="charCount">0</span>/1000 caractères
                </small>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fas fa-paper-plane"></i>
                Envoyer le message
            </button>
        </form>
    </div>

    <div class="contact-info">
        <div class="info-card">
            <div class="info-card-icon">
                <i class="fas fa-phone"></i>
            </div>
            <h3>Urgence électricité</h3>
            <p>Ligne d\'urgence 24/7<br><strong>+1 (555) 911-ELEC</strong></p>
        </div>

        <div class="info-card">
            <div class="info-card-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h3>Support par email</h3>
            <p>Service électricité<br><strong>electricite@Gaztronik.com</strong></p>
        </div>

        <div class="info-card">
            <div class="info-card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h3>Temps de réponse</h3>
            <p>Nous répondons généralement dans les<br><strong>24 heures</strong></p>
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
        document.getElementById("charCount").style.color = "#dc2626";
    } else {
        document.getElementById("charCount").style.color = "#404040";
    }
});

// Form submission with loading state
document.getElementById("contactForm").addEventListener("submit", function() {
    const btn = document.getElementById("submitBtn");
    btn.classList.add("loading");
    btn.innerHTML = \'<i class="fas fa-spinner fa-spin"></i> Envoi en cours...\';
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
