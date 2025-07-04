<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/news/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Function to handle image upload
function handleImageUpload($file) {
    global $upload_dir;
    
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $file['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.');
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Le fichier est trop volumineux. Taille maximale: 5MB.');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'news_' . uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    } else {
        throw new Exception('Erreur lors du téléchargement du fichier.');
    }
}

// Function to delete image file
function deleteImageFile($image_url) {
    if (!empty($image_url) && file_exists($image_url)) {
        unlink($image_url);
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $title = trim($_POST['title'] ?? '');
                $content = trim($_POST['content'] ?? '');
                $category = $_POST['category'] ?? '';
                $service_type = $_POST['service_type'] ?? '';
                $priority = $_POST['priority'] ?? 'normal';
                $author = trim($_POST['author'] ?? '');
                $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
                
                if (empty($title) || empty($content) || empty($category) || empty($service_type)) {
                    $error_message = 'Veuillez remplir tous les champs obligatoires.';
                } else {
                    try {
                        // Handle image upload
                        $image_url = null;
                        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                            $image_url = handleImageUpload($_FILES['image']);
                        }
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO news (title, content, category, service_type, priority, author, status, expires_at, published_at, image_url) 
                            VALUES (?, ?, ?, ?, ?, ?, 'published', ?, NOW(), ?)
                        ");
                        $stmt->execute([$title, $content, $category, $service_type, $priority, $author, $expires_at, $image_url]);
                        $success_message = 'Actualité créée avec succès !';
                    } catch (Exception $e) {
                        $error_message = 'Erreur lors de la création de l\'actualité: ' . $e->getMessage();
                        error_log("News creation error: " . $e->getMessage());
                    }
                }
                break;
                
            case 'delete':
                $news_id = $_POST['news_id'] ?? '';
                if (!empty($news_id)) {
                    try {
                        // Get image URL before deleting
                        $stmt = $pdo->prepare("SELECT image_url FROM news WHERE id = ?");
                        $stmt->execute([$news_id]);
                        $news = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Delete the news
                        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                        $stmt->execute([$news_id]);
                        
                        // Delete associated image file
                        if ($news && !empty($news['image_url'])) {
                            deleteImageFile($news['image_url']);
                        }
                        
                        $success_message = 'Actualité supprimée avec succès !';
                    } catch (PDOException $e) {
                        $error_message = 'Erreur lors de la suppression: ' . $e->getMessage();
                        error_log("News deletion error: " . $e->getMessage());
                    }
                }
                break;
                
            case 'toggle_status':
                $news_id = $_POST['news_id'] ?? '';
                $new_status = $_POST['new_status'] ?? '';
                if (!empty($news_id) && !empty($new_status)) {
                    try {
                        $stmt = $pdo->prepare("UPDATE news SET status = ? WHERE id = ?");
                        $stmt->execute([$new_status, $news_id]);
                        $success_message = 'Statut mis à jour avec succès !';
                    } catch (PDOException $e) {
                        $error_message = 'Erreur lors de la mise à jour du statut: ' . $e->getMessage();
                        error_log("News status update error: " . $e->getMessage());
                    }
                }
                break;
                
            case 'update_image':
                $news_id = $_POST['news_id'] ?? '';
                if (!empty($news_id)) {
                    try {
                        // Get current image URL
                        $stmt = $pdo->prepare("SELECT image_url FROM news WHERE id = ?");
                        $stmt->execute([$news_id]);
                        $current_news = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        $new_image_url = null;
                        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                            $new_image_url = handleImageUpload($_FILES['new_image']);
                            
                            // Delete old image if exists
                            if ($current_news && !empty($current_news['image_url'])) {
                                deleteImageFile($current_news['image_url']);
                            }
                        }
                        
                        if ($new_image_url) {
                            $stmt = $pdo->prepare("UPDATE news SET image_url = ? WHERE id = ?");
                            $stmt->execute([$new_image_url, $news_id]);
                            $success_message = 'Image mise à jour avec succès !';
                        }
                    } catch (Exception $e) {
                        $error_message = 'Erreur lors de la mise à jour de l\'image: ' . $e->getMessage();
                        error_log("Image update error: " . $e->getMessage());
                    }
                }
                break;
                
            case 'remove_image':
                $news_id = $_POST['news_id'] ?? '';
                if (!empty($news_id)) {
                    try {
                        // Get current image URL
                        $stmt = $pdo->prepare("SELECT image_url FROM news WHERE id = ?");
                        $stmt->execute([$news_id]);
                        $current_news = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Remove image URL from database
                        $stmt = $pdo->prepare("UPDATE news SET image_url = NULL WHERE id = ?");
                        $stmt->execute([$news_id]);
                        
                        // Delete image file
                        if ($current_news && !empty($current_news['image_url'])) {
                            deleteImageFile($current_news['image_url']);
                        }
                        
                        $success_message = 'Image supprimée avec succès !';
                    } catch (Exception $e) {
                        $error_message = 'Erreur lors de la suppression de l\'image: ' . $e->getMessage();
                        error_log("Image removal error: " . $e->getMessage());
                    }
                }
                break;
        }
    }
}

// Get all news with better error handling
try {
    $stmt = $pdo->query("SELECT * FROM news ORDER BY published_at DESC, id DESC");
    $all_news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_news = [];
    error_log("Error fetching news: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaztronik - Gestion des actualités</title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --admin-gradient: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            --success-gradient: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .admin-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .admin-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-title i {
            font-size: 2.5rem;
            background: var(--admin-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .admin-title h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #1a1a1a;
            font-family: 'Poppins', sans-serif;
        }

        .back-btn {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.1);
            color: #1a1a1a;
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .form-section, .news-list-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--glass-border);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a1a1a;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: #1a1a1a;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: rgba(255, 107, 53, 0.6);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        .form-group select option {
            background: #333;
            color: white;
        }

        /* File upload styling */
        .file-upload-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .file-upload-label:hover {
            border-color: rgba(255, 107, 53, 0.6);
            background: rgba(255, 107, 53, 0.1);
        }

        .file-upload-label i {
            font-size: 1.5rem;
            color: #ff6b35;
        }

        .image-preview {
            margin-top: 10px;
            text-align: center;
        }

        .image-preview img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: var(--admin-gradient);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.4);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert.success {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .alert.error {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .news-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .news-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .news-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            gap: 15px;
        }

        .news-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 5px;
        }

        .news-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .news-badge {
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-gas {
            background: rgba(255, 107, 53, 0.2);
            color: #ff6b35;
        }

        .badge-electricity {
            background: rgba(77, 171, 247, 0.2);
            color: #4dabf7;
        }

        .badge-both {
            background: rgba(147, 51, 234, 0.2);
            color: #9333ea;
        }

        .badge-published {
            background: rgba(34, 197, 94, 0.2);
            color: #16a34a;
        }

        .badge-draft {
            background: rgba(156, 163, 175, 0.2);
            color: #6b7280;
        }

        .badge-urgent {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .badge-high {
            background: rgba(251, 191, 36, 0.2);
            color: #f59e0b;
        }

        .news-content {
            color: #2d2d2d;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .news-image {
            margin-bottom: 15px;
            text-align: center;
        }

        .news-image img {
            max-width: 300px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .image-management {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .image-management h4 {
            color: #1a1a1a;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .news-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-toggle {
            background: rgba(77, 171, 247, 0.2);
            color: #4dabf7;
            border: 1px solid rgba(77, 171, 247, 0.3);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .btn-image {
            background: rgba(147, 51, 234, 0.2);
            color: #9333ea;
            border: 1px solid rgba(147, 51, 234, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .no-news {
            text-align: center;
            padding: 40px;
            color: #404040;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            margin: 5% auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            border: 1px solid var(--glass-border);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                text-align: center;
            }

            .news-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .news-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-title">
            <i class="fas fa-newspaper"></i>
            <h1>Gestion des actualités</h1>
        </div>
        <a href="admin_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Retour au tableau de bord
        </a>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="content-grid">
        <div class="form-section">
            <h2 class="section-title">
                <i class="fas fa-plus-circle"></i>
                Créer une actualité
            </h2>
            
            <form method="POST" id="newsForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="title">Titre *</label>
                    <input type="text" id="title" name="title" required placeholder="Titre de l'actualité" maxlength="255">
                </div>

                <div class="form-group">
                    <label for="content">Contenu *</label>
                    <textarea id="content" name="content" required placeholder="Contenu de l'actualité"></textarea>
                </div>

                <div class="form-group">
                    <label for="category">Catégorie *</label>
                    <select id="category" name="category" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="outage">Panne</option>
                        <option value="update">Mise à jour</option>
                        <option value="announcement">Annonce</option>
                        <option value="emergency">Urgence</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="service_type">Type de service *</label>
                    <select id="service_type" name="service_type" required>
                        <option value="">Sélectionner un service</option>
                        <option value="gas">Gaz</option>
                        <option value="electricity">Électricité</option>
                        <option value="both">Les deux</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Priorité</label>
                    <select id="priority" name="priority">
                        <option value="normal">Normale</option>
                        <option value="high">Élevée</option>
                        <option value="urgent">Urgente</option>
                        <option value="low">Faible</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="author">Auteur</label>
                    <input type="text" id="author" name="author" placeholder="Nom de l'auteur" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="image">Image (optionnel)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="image" name="image" class="file-upload-input" accept="image/*" onchange="previewImage(this)">
                        <label for="image" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Cliquez pour sélectionner une image</span>
                        </label>
                    </div>
                    <div id="imagePreview" class="image-preview"></div>
                    <small style="color: #666; font-size: 0.8rem;">
                        Formats acceptés: JPG, PNG, GIF, WebP. Taille max: 5MB
                    </small>
                </div>

                <div class="form-group">
                    <label for="expires_at">Date d'expiration (optionnel)</label>
                    <input type="datetime-local" id="expires_at" name="expires_at">
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-plus"></i>
                    Créer l'actualité
                </button>
            </form>
        </div>

        <div class="news-list-section">
            <h2 class="section-title">
                <i class="fas fa-list"></i>
                Actualités existantes (<?php echo count($all_news); ?>)
            </h2>

            <?php if (!empty($all_news)): ?>
                <?php foreach ($all_news as $news): ?>
                    <div class="news-item">
                        <div class="news-header">
                            <div>
                                <div class="news-title"><?php echo htmlspecialchars($news['title']); ?></div>
                                <div class="news-meta">
                                    <span class="news-badge badge-<?php echo $news['service_type']; ?>">
                                        <?php 
                                        echo $news['service_type'] === 'gas' ? 'Gaz' : 
                                             ($news['service_type'] === 'electricity' ? 'Électricité' : 'Les deux'); 
                                        ?>
                                    </span>
                                    <span class="news-badge badge-<?php echo $news['status']; ?>">
                                        <?php echo $news['status'] === 'published' ? 'Publié' : 'Brouillon'; ?>
                                    </span>
                                    <?php if ($news['priority'] !== 'normal'): ?>
                                        <span class="news-badge badge-<?php echo $news['priority']; ?>">
                                            <?php echo ucfirst($news['priority']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="news-badge" style="background: rgba(156, 163, 175, 0.2); color: #6b7280;">
                                        <?php echo ucfirst($news['category']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($news['image_url'])): ?>
                            <div class="news-image">
                                <img src="<?php echo htmlspecialchars($news['image_url']); ?>" alt="Image de l'actualité">
                            </div>
                        <?php endif; ?>
                        
                        <div class="news-content">
                            <?php echo nl2br(htmlspecialchars(substr($news['content'], 0, 200))); ?>
                            <?php if (strlen($news['content']) > 200): ?>...<?php endif; ?>
                        </div>

                        <?php if (!empty($news['author'])): ?>
                            <div style="color: #666; font-size: 0.9rem; margin-bottom: 10px;">
                                <i class="fas fa-user"></i> Par: <?php echo htmlspecialchars($news['author']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($news['expires_at'])): ?>
                            <div style="color: #666; font-size: 0.9rem; margin-bottom: 10px;">
                                <i class="fas fa-calendar-times"></i> Expire le: <?php echo date('d/m/Y H:i', strtotime($news['expires_at'])); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Image Management -->
                        <div class="image-management">
                            <h4><i class="fas fa-image"></i> Gestion de l'image</h4>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                <?php if (!empty($news['image_url'])): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette image ?');">
                                        <input type="hidden" name="action" value="remove_image">
                                        <input type="hidden" name="news_id" value="<?php echo $news['id']; ?>">
                                        <button type="submit" class="action-btn btn-delete">
                                            <i class="fas fa-trash"></i> Supprimer image
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button type="button" class="action-btn btn-image" onclick="openImageModal(<?php echo $news['id']; ?>)">
                                    <i class="fas fa-edit"></i> 
                                    <?php echo !empty($news['image_url']) ? 'Changer image' : 'Ajouter image'; ?>
                                </button>
                            </div>
                        </div>
                        
                        <div class="news-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="news_id" value="<?php echo $news['id']; ?>">
                                <input type="hidden" name="new_status" value="<?php echo $news['status'] === 'published' ? 'draft' : 'published'; ?>">
                                <button type="submit" class="action-btn btn-toggle">
                                    <i class="fas fa-<?php echo $news['status'] === 'published' ? 'eye-slash' : 'eye'; ?>"></i>
                                    <?php echo $news['status'] === 'published' ? 'Masquer' : 'Publier'; ?>
                                </button>
                            </form>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="news_id" value="<?php echo $news['id']; ?>">
                                <button type="submit" class="action-btn btn-delete">
                                    <i class="fas fa-trash"></i>
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-news">
                    <i class="fas fa-newspaper" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>Aucune actualité trouvée</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Image Upload Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeImageModal()">&times;</span>
            <h3 style="margin-bottom: 20px;">Mettre à jour l'image</h3>
            <form method="POST" enctype="multipart/form-data" id="imageUpdateForm">
                <input type="hidden" name="action" value="update_image">
                <input type="hidden" name="news_id" id="modalNewsId">
                
                <div class="form-group">
                    <label for="new_image">Nouvelle image</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="new_image" name="new_image" class="file-upload-input" accept="image/*" required onchange="previewModalImage(this)">
                        <label for="new_image" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Sélectionner une nouvelle image</span>
                        </label>
                    </div>
                    <div id="modalImagePreview" class="image-preview"></div>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-upload"></i>
                    Mettre à jour l'image
                </button>
            </form>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('newsForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const category = document.getElementById('category').value;
            const service_type = document.getElementById('service_type').value;
            
            if (!title || !content || !category || !service_type) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires.');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('.submit-btn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...';
            submitBtn.disabled = true;
        });

        // Image preview function
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Modal functions
        function openImageModal(newsId) {
            document.getElementById('modalNewsId').value = newsId;
            document.getElementById('imageModal').style.display = 'block';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
            document.getElementById('modalImagePreview').innerHTML = '';
        }

        function previewModalImage(input) {
            const preview = document.getElementById('modalImagePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target == modal) {
                closeImageModal();
            }
        }
    </script>
</body>
</html>
