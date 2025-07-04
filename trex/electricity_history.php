<?php
$page_title = "Historique électricité";
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';

// Get electricity usage history
try {
    $stmt = $pdo->prepare("
        SELECT * FROM usage_history 
        WHERE user_id = ? AND service_type = 'electricity' 
        ORDER BY reading_date DESC 
        LIMIT 50
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $usage_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $usage_history = [];
    error_log("Electricity history error: " . $e->getMessage());
}

// Simple chart data - if no data, use sample data
if (!empty($usage_history)) {
    $chartLabels = [];
    $chartData = [];
    foreach (array_slice($usage_history, 0, 7) as $usage) {
        $chartLabels[] = date('d/m', strtotime($usage['reading_date']));
        $chartData[] = floatval($usage['consumption']);
    }
    $chartLabels = array_reverse($chartLabels);
    $chartData = array_reverse($chartData);
} else {
    // Sample data if no real data exists
    $chartLabels = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
    $chartData = [320, 298, 345, 312, 289, 356, 278];
}

$additional_css = '
    .history-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .history-header {
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

    .history-header::before {
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

    .history-header h1 {
        color: #1a1a1a;
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 800;
    }

    .history-header p {
        color: #2d2d2d;
        font-size: 1.2rem;
    }

    .history-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .chart-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        margin-bottom: 30px;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .chart-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1a1a1a;
        font-family: "Poppins", sans-serif;
    }

    .chart-stats {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .stat-item {
        text-align: center;
        padding: 10px 15px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .stat-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #4dabf7;
        margin-bottom: 2px;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #404040;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .chart-container {
        height: 400px;
        position: relative;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 15px;
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .data-table {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow-x: auto;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .table-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1a1a1a;
        font-family: "Poppins", sans-serif;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        color: #1a1a1a;
    }

    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    th {
        background: rgba(255, 255, 255, 0.1);
        font-weight: 700;
        color: #1a1a1a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }

    td {
        color: #2d2d2d;
    }

    tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    @media (max-width: 768px) {
        .history-container {
            padding: 0 15px;
        }
        
        .chart-stats {
            justify-content: center;
        }
        
        .chart-container {
            height: 300px;
        }
    }
';

$additional_js = '
// Chart.js configuration
let consumptionChart;

// Chart data
const chartLabels = ' . json_encode($chartLabels) . ';
const chartData = ' . json_encode($chartData) . ';

function initCharts() {
    try {
        const consumptionCtx = document.getElementById("consumptionChart");
        if (consumptionCtx) {
            consumptionChart = new Chart(consumptionCtx, {
                type: "line",
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: "Consommation électrique (kWh)",
                        data: chartData,
                        borderColor: "#4dabf7",
                        backgroundColor: "rgba(77, 171, 247, 0.1)",
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: "#4dabf7",
                        pointBorderColor: "#ffffff",
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: "#1a1a1a",
                                font: {
                                    size: 14,
                                    weight: "600"
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: "#404040",
                                font: {
                                    size: 12,
                                    weight: "500"
                                }
                            },
                            grid: {
                                color: "rgba(255, 255, 255, 0.1)"
                            }
                        },
                        y: {
                            ticks: {
                                color: "#404040",
                                font: {
                                    size: 12,
                                    weight: "500"
                                }
                            },
                            grid: {
                                color: "rgba(255, 255, 255, 0.1)"
                            }
                        }
                    }
                }
            });
        }
        
        // Update stats
        updateStats();
    } catch (error) {
        console.error("Error initializing charts:", error);
    }
}

function updateStats() {
    const totalConsumption = chartData.reduce((a, b) => a + b, 0);
    const avgConsumption = totalConsumption / chartData.length;
    
    document.getElementById("totalConsumption").textContent = totalConsumption.toFixed(0);
    document.getElementById("avgConsumption").textContent = avgConsumption.toFixed(1);
}

// Initialize charts when page loads
document.addEventListener("DOMContentLoaded", function() {
    // Load Chart.js
    const script = document.createElement("script");
    script.src = "https://cdn.jsdelivr.net/npm/chart.js";
    script.onload = initCharts;
    script.onerror = function() {
        console.error("Failed to load Chart.js");
    };
    document.head.appendChild(script);
});
';

$content = '
<div class="history-container">
    <div class="history-header">
        <div class="history-icon">
            <i class="fas fa-bolt"></i>
        </div>
        <h1>Historique d\'utilisation électrique</h1>
        <p>Suivez vos habitudes de consommation électrique</p>
    </div>

    <!-- Consumption Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Consommation électrique</h3>
            <div class="chart-stats">
                <div class="stat-item">
                    <div class="stat-value" id="totalConsumption">2135</div>
                    <div class="stat-label">Total kWh</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="avgConsumption">305.0</div>
                    <div class="stat-label">Moy kWh</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" style="color: #22c55e;">+8.5%</div>
                    <div class="stat-label">vs Période précédente</div>
                </div>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="consumptionChart"></canvas>
        </div>
    </div>

    <!-- Data Table -->
    <div class="data-table">
        <div class="table-header">
            <h3 class="table-title">Historique détaillé</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Consommation (kWh)</th>
                    <th>Coût (TND)</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>';

// Generate table rows
if(!empty($usage_history)) {
    foreach(array_slice($usage_history, 0, 10) as $usage) {
        $content .= '
                <tr>
                    <td>' . date('d M Y', strtotime($usage['reading_date'])) . '</td>
                    <td>' . number_format($usage['consumption'], 1) . '</td>
                    <td>' . number_format($usage['cost'], 2) . ' TND</td>
                    <td><span style="color: #22c55e;">Enregistré</span></td>
                </tr>';
    }
} else {
    $content .= '
                <tr>
                    <td colspan="4" style="text-align: center; color: #404040;">
                        Aucun historique d\'utilisation disponible.
                    </td>
                </tr>';
}

$content .= '
            </tbody>
        </table>
    </div>
</div>';

include 'layout.php';
?>
