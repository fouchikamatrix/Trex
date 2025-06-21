<?php
$page_title = "Gas History";
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'User';

// Get gas usage history
try {
    $stmt = $pdo->prepare("
        SELECT * FROM usage_history 
        WHERE user_id = ? AND service_type = 'gas' 
        ORDER BY reading_date DESC 
        LIMIT 50
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $usage_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get payment history
    $stmt = $pdo->prepare("
        SELECT p.*, b.consumption, b.unit 
        FROM payments p 
        JOIN bills b ON p.bill_id = b.id 
        WHERE p.user_id = ? AND b.service_type = 'gas' 
        ORDER BY p.created_at DESC 
        LIMIT 20
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $payment_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $usage_history = [];
    $payment_history = [];
    error_log("Gas history error: " . $e->getMessage());
}

// Process data for charts
$weekUsageData = [];
$weekUsageLabels = [];
if (!empty($usage_history)) {
    $weekData = array_slice($usage_history, 0, 7);
    $weekUsageLabels = array_map(function($item) { 
        return date('D', strtotime($item['reading_date'])); 
    }, array_reverse($weekData));
    $weekUsageData = array_map(function($item) { 
        return floatval($item['consumption']); 
    }, array_reverse($weekData));
} else {
    $weekUsageLabels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    $weekUsageData = [0, 0, 0, 0, 0, 0, 0];
}

// Process monthly data
$monthUsageData = [];
for($i = 0; $i < 4; $i++) {
    $weekStart = date('Y-m-d', strtotime("-" . ($i * 7) . " days"));
    $weekEnd = date('Y-m-d', strtotime("-" . (($i * 7) - 6) . " days"));
    $weekTotal = 0;
    if(!empty($usage_history)) {
        foreach($usage_history as $usage) {
            if($usage['reading_date'] >= $weekEnd && $usage['reading_date'] <= $weekStart) {
                $weekTotal += floatval($usage['consumption']);
            }
        }
    }
    $monthUsageData[] = $weekTotal;
}
$monthUsageData = array_reverse($monthUsageData);

// Process yearly data
$yearUsageData = array_fill(0, 12, 0);
if(!empty($usage_history)) {
    foreach($usage_history as $usage) {
        $month = intval(date('n', strtotime($usage['reading_date']))) - 1;
        if($month >= 0 && $month < 12) {
            $yearUsageData[$month] += floatval($usage['consumption']);
        }
    }
}

// Process payment data
$weekPaymentData = [];
$weekPaymentLabels = [];
if (!empty($payment_history)) {
    $weekPayments = array_slice($payment_history, 0, 7);
    $weekPaymentLabels = array_map(function($item) { 
        return date('D', strtotime($item['created_at'])); 
    }, array_reverse($weekPayments));
    $weekPaymentData = array_map(function($item) { 
        return floatval($item['amount']); 
    }, array_reverse($weekPayments));
} else {
    $weekPaymentLabels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    $weekPaymentData = [0, 0, 0, 0, 0, 0, 0];
}

// Process monthly payment data
$monthPaymentData = [];
for($i = 0; $i < 4; $i++) {
    $weekStart = date('Y-m-d', strtotime("-" . ($i * 7) . " days"));
    $weekEnd = date('Y-m-d', strtotime("-" . (($i * 7) - 6) . " days"));
    $weekTotal = 0;
    if(!empty($payment_history)) {
        foreach($payment_history as $payment) {
            if($payment['created_at'] >= $weekEnd && $payment['created_at'] <= $weekStart) {
                $weekTotal += floatval($payment['amount']);
            }
        }
    }
    $monthPaymentData[] = $weekTotal;
}
$monthPaymentData = array_reverse($monthPaymentData);

// Process yearly payment data
$yearPaymentData = array_fill(0, 12, 0);
if(!empty($payment_history)) {
    foreach($payment_history as $payment) {
        $month = intval(date('n', strtotime($payment['created_at']))) - 1;
        if($month >= 0 && $month < 12) {
            $yearPaymentData[$month] += floatval($payment['amount']);
        }
    }
}

$additional_css = '
    .history-container {
        max-width: 1400px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .history-header {
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

    .history-header::before {
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

    .history-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .chart-controls {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
        justify-content: space-between;
    }

    .control-group {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .control-label {
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .period-buttons {
        display: flex;
        gap: 10px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 4px;
    }

    .period-btn {
        padding: 8px 16px;
        border: none;
        background: transparent;
        color: rgba(255, 255, 255, 0.7);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .period-btn.active {
        background: rgba(255, 107, 53, 0.3);
        color: white;
        box-shadow: 0 2px 8px rgba(255, 107, 53, 0.2);
    }

    .period-btn:hover:not(.active) {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .chart-type-selector {
        display: flex;
        gap: 10px;
    }

    .chart-type-btn {
        padding: 10px 15px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.7);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .chart-type-btn.active {
        border-color: rgba(255, 107, 53, 0.6);
        background: rgba(255, 107, 53, 0.2);
        color: white;
    }

    .chart-type-btn:hover:not(.active) {
        border-color: rgba(255, 255, 255, 0.4);
        background: rgba(255, 255, 255, 0.15);
        color: white;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .chart-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
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
        color: white;
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
        color: #ff6b35;
        margin-bottom: 2px;
    }

    .stat-label {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.8);
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
        color: white;
        font-family: "Poppins", sans-serif;
    }

    .export-btn {
        padding: 10px 20px;
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.3);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        color: white;
    }

    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    th {
        background: rgba(255, 255, 255, 0.1);
        font-weight: 700;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }

    td {
        color: rgba(255, 255, 255, 0.9);
    }

    tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .trend-up {
        color: #22c55e;
    }

    .trend-down {
        color: #ef4444;
    }

    .trend-neutral {
        color: #fbbf24;
    }

    @media (max-width: 768px) {
        .history-container {
            padding: 0 15px;
        }
        
        .chart-controls {
            flex-direction: column;
            align-items: stretch;
        }
        
        .control-group {
            justify-content: center;
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
let consumptionChart, paymentsChart;

// Generate chart data from database
const consumptionData = {
    week: {
        labels: ' . json_encode($weekUsageLabels) . ',
        data: ' . json_encode($weekUsageData) . '
    },
    month: {
        labels: ["Week 1", "Week 2", "Week 3", "Week 4"],
        data: ' . json_encode($monthUsageData) . '
    },
    year: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        data: ' . json_encode($yearUsageData) . '
    }
};

const paymentsData = {
    week: {
        labels: ' . json_encode($weekPaymentLabels) . ',
        data: ' . json_encode($weekPaymentData) . '
    },
    month: {
        labels: ["Week 1", "Week 2", "Week 3", "Week 4"],
        data: ' . json_encode($monthPaymentData) . '
    },
    year: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        data: ' . json_encode($yearPaymentData) . '
    }
};

function initCharts() {
    // Consumption Chart
    const consumptionCtx = document.getElementById("consumptionChart");
    if (consumptionCtx) {
        consumptionChart = new Chart(consumptionCtx, {
            type: "line",
            data: {
                labels: consumptionData.week.labels,
                datasets: [{
                    label: "Gas Consumption (m³)",
                    data: consumptionData.week.data,
                    borderColor: "#ff6b35",
                    backgroundColor: "rgba(255, 107, 53, 0.1)",
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: "#ff6b35",
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
                            color: "white",
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
                            color: "rgba(255, 255, 255, 0.8)",
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
                            color: "rgba(255, 255, 255, 0.8)",
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

    // Payments Chart
    const paymentsCtx = document.getElementById("paymentsChart");
    if (paymentsCtx) {
        paymentsChart = new Chart(paymentsCtx, {
            type: "bar",
            data: {
                labels: paymentsData.week.labels,
                datasets: [{
                    label: "Payments ($)",
                    data: paymentsData.week.data,
                    backgroundColor: "rgba(255, 107, 53, 0.7)",
                    borderColor: "#ff6b35",
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: "white",
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
                            color: "rgba(255, 255, 255, 0.8)",
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
                            color: "rgba(255, 255, 255, 0.8)",
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
}

function updatePeriod(period) {
    // Update active button
    document.querySelectorAll(".period-btn").forEach(btn => btn.classList.remove("active"));
    document.querySelector(`[data-period="${period}"]`).classList.add("active");

    // Update consumption chart
    if (consumptionChart) {
        consumptionChart.data.labels = consumptionData[period].labels;
        consumptionChart.data.datasets[0].data = consumptionData[period].data;
        consumptionChart.update();
    }

    // Update payments chart
    if (paymentsChart) {
        paymentsChart.data.labels = paymentsData[period].labels;
        paymentsChart.data.datasets[0].data = paymentsData[period].data;
        paymentsChart.update();
    }

    // Update stats
    updateStats(period);
}

function updateStats(period) {
    const consumption = consumptionData[period].data;
    const payments = paymentsData[period].data;
    
    const totalConsumption = consumption.reduce((a, b) => a + b, 0);
    const totalPayments = payments.reduce((a, b) => a + b, 0);
    const avgConsumption = totalConsumption / consumption.length;
    
    document.getElementById("totalConsumption").textContent = totalConsumption.toFixed(1);
    document.getElementById("avgConsumption").textContent = avgConsumption.toFixed(1);
    document.getElementById("totalPayments").textContent = "$" + totalPayments.toFixed(2);
}

function changeChartType(chartId, type) {
    const chart = chartId === "consumption" ? consumptionChart : paymentsChart;
    if (chart) {
        chart.config.type = type;
        chart.update();
    }
    
    // Update active button
    document.querySelectorAll(`[data-chart="${chartId}"] .chart-type-btn`).forEach(btn => btn.classList.remove("active"));
    document.querySelector(`[data-chart="${chartId}"] [data-type="${type}"]`).classList.add("active");
}

function exportData() {
    alert("Export functionality would be implemented here");
}

// Initialize charts when page loads
document.addEventListener("DOMContentLoaded", function() {
    // Load Chart.js
    const script = document.createElement("script");
    script.src = "https://cdn.jsdelivr.net/npm/chart.js";
    script.onload = initCharts;
    document.head.appendChild(script);
});
';

$content = '
<div class="history-container">
    <div class="history-header">
        <div class="history-icon">
            <i class="fas fa-history"></i>
        </div>
        <h1>Gas Usage History</h1>
        <p>Track your gas consumption patterns and payment history</p>
    </div>

    <div class="chart-controls">
        <div class="control-group">
            <span class="control-label">Time Period:</span>
            <div class="period-buttons">
                <button class="period-btn active" data-period="week" onclick="updatePeriod(\'week\')">Week</button>
                <button class="period-btn" data-period="month" onclick="updatePeriod(\'month\')">Month</button>
                <button class="period-btn" data-period="year" onclick="updatePeriod(\'year\')">Year</button>
            </div>
        </div>
    </div>

    <div class="charts-grid">
        <!-- Consumption Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Gas Consumption</h3>
                <div class="chart-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="totalConsumption">242</div>
                        <div class="stat-label">Total m³</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="avgConsumption">48.4</div>
                        <div class="stat-label">Avg m³</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value trend-up">+5.2%</div>
                        <div class="stat-label">vs Last Period</div>
                    </div>
                </div>
            </div>
            <div class="control-group" data-chart="consumption">
                <span class="control-label">Chart Type:</span>
                <div class="chart-type-selector">
                    <button class="chart-type-btn active" data-type="line" onclick="changeChartType(\'consumption\', \'line\')">
                        <i class="fas fa-chart-line"></i>
                        Line
                    </button>
                    <button class="chart-type-btn" data-type="bar" onclick="changeChartType(\'consumption\', \'bar\')">
                        <i class="fas fa-chart-bar"></i>
                        Bar
                    </button>
                    <button class="chart-type-btn" data-type="doughnut" onclick="changeChartType(\'consumption\', \'doughnut\')">
                        <i class="fas fa-chart-pie"></i>
                        Pie
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="consumptionChart"></canvas>
            </div>
        </div>

        <!-- Payments Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Payment History</h3>
                <div class="chart-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="totalPayments">$127.50</div>
                        <div class="stat-label">Total Paid</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">$0.53</div>
                        <div class="stat-label">Per m³</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value trend-down">-2.1%</div>
                        <div class="stat-label">vs Last Period</div>
                    </div>
                </div>
            </div>
            <div class="control-group" data-chart="payments">
                <span class="control-label">Chart Type:</span>
                <div class="chart-type-selector">
                    <button class="chart-type-btn active" data-type="bar" onclick="changeChartType(\'payments\', \'bar\')">
                        <i class="fas fa-chart-bar"></i>
                        Bar
                    </button>
                    <button class="chart-type-btn" data-type="line" onclick="changeChartType(\'payments\', \'line\')">
                        <i class="fas fa-chart-line"></i>
                        Line
                    </button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="paymentsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="data-table">
        <div class="table-header">
            <h3 class="table-title">Detailed History</h3>
            <button class="export-btn" onclick="exportData()">
                <i class="fas fa-download"></i>
                Export Data
            </button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Consumption (m³)</th>
                    <th>Rate ($/m³)</th>
                    <th>Amount ($)</th>
                    <th>Status</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>';

// Generate table rows
if(!empty($usage_history)) {
    foreach(array_slice($usage_history, 0, 10) as $usage) {
        $trend = rand(-20, 20);
        $trend_icon = '';
        $trend_class = '';
        
        if($trend > 0) {
            $trend_icon = '<i class="fas fa-arrow-up trend-up"></i> +' . $trend . '%';
        } elseif($trend < 0) {
            $trend_icon = '<i class="fas fa-arrow-down trend-down"></i> ' . $trend . '%';
        } else {
            $trend_icon = '<i class="fas fa-minus trend-neutral"></i> 0%';
        }
        
        $content .= '
                <tr>
                    <td>' . date('M d, Y', strtotime($usage['reading_date'])) . '</td>
                    <td>' . number_format($usage['consumption'], 1) . '</td>
                    <td>$' . number_format($usage['rate'], 3) . '</td>
                    <td>$' . number_format($usage['consumption'] * $usage['rate'], 2) . '</td>
                    <td><span style="color: #22c55e;">Recorded</span></td>
                    <td>' . $trend_icon . '</td>
                </tr>';
    }
} else {
    $content .= '
                <tr>
                    <td colspan="6" style="text-align: center; color: rgba(255, 255, 255, 0.6);">
                        No usage history available. Please check back later.
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
