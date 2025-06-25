<?php
$page_title = "Actualités gaz";
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get gas news
try {
    $stmt = $pdo->prepare("
        SELECT * FROM news 
        WHERE service_type IN ('gas', 'both') AND status = 'published'
        AND (expires_at IS NULL OR expires_at > NOW())
        ORDER BY priority DESC, published_at DESC 
        LIMIT 20
    ");
    $stmt->execute();
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $news = [];
    error_log("Erreur actualités gaz: " . $e->getMessage());
}

$additional_css = '
    .news-container {
        max-width: 1000px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .news-header {
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

    .news-header::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, 
            rgba(255, 107, 53, 0.1) 0%, 
            rgba(247, 147, 30, 0.1) 50%,
            rgba(255, 107, 53, 0.1) 100%);
        background-size: 400% 400%;
        animation: gradientShift 10s ease infinite;
        z-index: -1;
    }

    .news-header h1 {
        color: #1a1a1a;
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 800;
    }

    .news-header p {
        color: #2d2d2d;
        font-size: 1.2rem;
    }

    .news-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .news-grid {
        display: grid;
        gap: 25px;
        margin-bottom: 30px;
    }

    .news-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    .news-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .news-category {
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .category-maintenance {
        background: rgba(251, 191, 36, 0.2);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.3);
    }

    .category-outage {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .category-update {
        background: rgba(77, 171, 247, 0.2);
        color: #4dabf7;
        border: 1px solid rgba(77, 171, 247, 0.3);
    }

    .category-announcement {
        background: rgba(34, 197, 94, 0.2);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .category-emergency {
        background: rgba(239, 68, 68, 0.3);
        color: #fecaca;
        border: 1px solid rgba(239, 68, 68, 0.5);
        animation: pulse 2s infinite;
    }

    .news-date {
        color: #404040;
        font-size: 0.9rem;
    }

    .news-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 15px;
        font-family: "Poppins", sans-serif;
        line-height: 1.3;
    }

    .news-image {
        margin-bottom: 20px;
        text-align: center;
        border-radius: 15px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .news-image img {
        width: 100%;
        height: auto;
        max-height: 300px;
        object-fit: cover;
        transition: transform 0.3s ease;
        border-radius: 15px;
    }

    .news-image:hover img {
        transform: scale(1.02);
    }

    .news-content {
        color: #2d2d2d;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .news-author {
        color: #404040;
        font-size: 0.85rem;
        font-style: italic;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .news-author i {
        color: #ff6b35;
    }

    .priority-high {
        border-left: 4px solid #ef4444;
    }

    .priority-urgent {
        border-left: 4px solid #dc2626;
        background: rgba(239, 68, 68, 0.05);
    }

    .no-news {
        text-align: center;
        padding: 60px 30px;
        color: #404040;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .no-news i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .news-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 0.85rem;
        color: #666;
    }

    .news-service-type {
        background: rgba(255, 107, 53, 0.2);
        color: #ff6b35;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    @media (max-width: 768px) {
        .news-container {
            padding: 0 15px;
        }
        
        .news-meta {
            flex-direction: column;
            align-items: flex-start;
        }

        .news-image img {
            max-height: 200px;
        }

        .news-footer {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }
    }
';

$content = '
<div class="news-container">
    <div class="news-header">
        <div class="news-icon">
            <i class="fas fa-fire"></i>
        </div>
        <h1>Actualités du service gaz</h1>
        <p>Restez informé des dernières annonces et mises à jour du service gaz</p>
    </div>

    <div class="news-grid">';

if (!empty($news)) {
    foreach ($news as $article) {
        $categoryClass = 'category-' . $article['category'];
        $priorityClass = $article['priority'] === 'high' ? 'priority-high' : ($article['priority'] === 'urgent' ? 'priority-urgent' : '');
        
        // Translate categories
        $categoryTranslations = [
            'maintenance' => 'Maintenance',
            'outage' => 'Panne',
            'update' => 'Mise à jour',
            'announcement' => 'Annonce',
            'emergency' => 'Urgence'
        ];
        
        $categoryText = $categoryTranslations[$article['category']] ?? ucfirst($article['category']);
        
        // Service type display
        $serviceTypeText = '';
        switch($article['service_type']) {
            case 'gas':
                $serviceTypeText = 'Gaz';
                break;
            case 'electricity':
                $serviceTypeText = 'Électricité';
                break;
            case 'both':
                $serviceTypeText = 'Gaz & Électricité';
                break;
        }
        
        $content .= '
        <div class="news-card ' . $priorityClass . '">
            <div class="news-meta">
                <div class="news-category ' . $categoryClass . '">' . $categoryText . '</div>
                <div class="news-date">' . date('d M Y', strtotime($article['published_at'])) . '</div>
            </div>
            
            <h2 class="news-title">' . htmlspecialchars($article['title']) . '</h2>';
            
        // Display image if exists
        if (!empty($article['image_url']) && file_exists($article['image_url'])) {
            $content .= '
            <div class="news-image">
                <img src="' . htmlspecialchars($article['image_url']) . '" alt="' . htmlspecialchars($article['title']) . '" loading="lazy">
            </div>';
        }
        
        $content .= '
            <div class="news-content">' . nl2br(htmlspecialchars($article['content'])) . '</div>
            
            <div class="news-footer">
                <div class="news-service-type">' . $serviceTypeText . '</div>
                ' . (!empty($article['author']) ? '<div class="news-author"><i class="fas fa-user"></i> Par ' . htmlspecialchars($article['author']) . '</div>' : '') . '
            </div>
        </div>';
    }
} else {
    $content .= '
    <div class="no-news">
        <i class="fas fa-fire"></i>
        <h3>Aucune actualité disponible</h3>
        <p>Il n\'y a actuellement aucune actualité ou annonce du service gaz. Revenez plus tard pour les mises à jour.</p>
    </div>';
}

$content .= '
    </div>
</div>';

include 'layout.php';
?>
