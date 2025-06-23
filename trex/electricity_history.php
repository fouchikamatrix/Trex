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
        color: #1a1a1a;
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
        color: #404040;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .period-btn.active {
        background: rgba(77, 171, 247, 0.3);
        color: #1a1a1a;
        box-shadow: 0 2px 8px rgba(77, 171, 247, 0.2);
    }

    .period-btn:hover:not(.active) {
        background: rgba(255, 255, 255, 0.1);
        color: #1a1a1a;
    }

    .chart-type-selector {
        display: flex;
        gap: 10px;
    }

    .chart-type-btn {
        padding: 10px 15px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.1);
        color: #404040;
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
        border-color: rgba(77, 171, 247, 0.6);
        background: rgba(77, 171, 247, 0.2);
        color: #1a1a1a;
    }

    .chart-type-btn:hover:not(.active) {
        border-color: rgba(255, 255, 255, 0.4);
        background: rgba(255, 255, 255, 0.15);
        color: #1a1a1a;
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

    .export-btn {
        padding: 10px 20px;
        background: linear-gradient(135deg, #4dabf7 0%, #2196f3 100%);
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
        box-shadow: 0 8px 20px rgba(77, 171, 247, 0.3);
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

// Function to fetch electricity data
async function fetchElectricityData(period = "week") {
    try {
        const response = await fetch(`api/get_electricity_data.php?period=${period}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error("Could not fetch electricity data:", error);
        return null;
    }
}

// Function to update the charts
async function updateCharts(period = "week") {
    const electricityData = await fetchElectricityData(period);

    if (electricityData) {
        // Update Consumption Chart
        if (consumptionChart) {
            consumptionChart.data.labels = electricityData.consumption.labels;
            consumptionChart.data.datasets[0].data = electricityData.consumption.data;
            consumptionChart.update();
        }

        // Update Payments Chart
        if (paymentsChart) {
            paymentsChart.data.labels = electricityData.payments.labels;
            paymentsChart.data.datasets[0].data = electricityData.payments.data;
            paymentsChart.update();
        }

        // Update Stats
        updateStats(electricityData.summary);

        // Update Table
        updateTable(electricityData.details);
    }
}

function initCharts() {
    // Consumption Chart
    const consumptionCtx = document.getElementById("consumptionChart");
    if (consumptionCtx) {
        consumptionChart = new Chart(consumptionCtx, {
            type: "line",
            data: {
                labels: [],
                datasets: [{
                    label: "Consommation électrique (kWh)",
                    data: [],
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

    // Payments Chart
    const paymentsCtx = document.getElementById("paymentsChart");
    if (paymentsCtx) {
        paymentsChart = new Chart(paymentsCtx, {
            type: "bar",
            data: {
                labels: [],
                datasets: [{
                    label: "Paiements ($)",
                    data: [],
                    backgroundColor: "rgba(77, 171, 247, 0.7)",
                    borderColor: "#4dabf7",
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

    // Initial chart update
    updateCharts("week");
}

function updatePeriod(period) {
    // Update active button
    document.querySelectorAll(".period-btn").forEach(btn => btn.classList.remove("active"));
    document.querySelector(`[data-period="${period}"]`).classList.add("active");

    updateCharts(period);
}

function updateStats(summary) {
    document.getElementById("totalConsumption").textContent = summary.totalConsumption.toFixed(0);
    document.getElementById("avgConsumption").textContent = summary.avgConsumption.toFixed(1);
    document.getElementById("totalPayments").textContent = "$" + summary.totalPayments.toFixed(2);

    const trendElement = document.querySelector(".chart-stats .stat-item:nth-child(3) .stat-value");
    trendElement.textContent = (summary.trend > 0 ? "+" : "") + summary.trend.toFixed(1) + "%";
    trendElement.classList.remove("trend-up", "trend-down", "trend-neutral");
    if (summary.trend > 0) {
        trendElement.classList.add("trend-up");
    } else if (summary.trend < 0) {
        trendElement.classList.add("trend-down");
    } else {
        trendElement.classList.add("trend-neutral");
    }
}

function updateTable(details) {
    const tableBody = document.querySelector(".data-table table tbody");
    tableBody.innerHTML = ""; // Clear existing rows

    details.forEach(row => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${row.date}</td>
            <td>${row.consumption}</td>
            <td>${row.rate}</td>
            <td>${row.amount}</td>
            <td><span style="color: ${row.statusColor};">${row.status}</span></td>
            <td><i class="fas fa-arrow-${row.trendDirection} trend-${row.trend}"></i> ${row.trendValue}</td>
        `;
        tableBody.appendChild(tr);
    });
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
    alert("La fonctionnalité d\'exportation serait implémentée ici");
}

// Initialize charts when page loads
document.addEventListener("DOMContentLoaded", function() {
    // Load Chart.js
    const script = document.createElement("script");
    script.src = "https://cdn.jsdelivr.net/npm/chart.js";
    script.onload = initCharts;
    document.head.appendChild(script);

    // Load Font Awesome
    const fontAwesome = document.createElement("link");
    fontAwesome.rel = "stylesheet";
    fontAwesome.href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css";
    document.head.appendChild(fontAwesome);
});
';

$content = '
<div class="history-container">
    <div class="history-header">
        <div class="history-icon">
            <i class="fas fa-history"></i>
        </div>
        <h1>Historique d\'utilisation électrique</h1>
        <p>Suivez vos habitudes de consommation électrique et l\'historique des paiements</p>
    </div>

    <div class="chart-controls">
        <div class="control-group">
            <span class="control-label">Période :</span>
            <div class="period-buttons">
                <button class="period-btn active" data-period="week" onclick="updatePeriod(\'week\')">Semaine</button>
                <button class="period-btn" data-period="month" onclick="updatePeriod(\'month\')">Mois</button>
                <button class="period-btn" data-period="year" onclick="updatePeriod(\'year\')">Année</button>
            </div>
        </div>
    </div>

    <div class="charts-grid">
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
                        <div class="stat-value trend-up">+8.5%</div>
                        <div class="stat-label">vs Période précédente</div>
                    </div>
                </div>
            </div>
            <div class="control-group" data-chart="consumption">
                <span class="control-label">Type de graphique :</span>
                <div class="chart-type-selector">
                    <button class="chart-type-btn active" data-type="line" onclick="changeChartType(\'consumption\', \'line\')">
                        <i class="fas fa-chart-line"></i>
                        Ligne
                    </button>
                    <button class="chart-type-btn" data-type="bar" onclick="changeChartType(\'consumption\', \'bar\')">
                        <i class="fas fa-chart-bar"></i>
                        Barres
                    </button>
                    <button class="chart-type-btn" data-type="doughnut" onclick="changeChartType(\'consumption\', \'doughnut\')">
                        <i class="fas fa-chart-pie"></i>
                        Secteurs
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
                <h3 class="chart-title">Historique des paiements</h3>
                <div class="chart-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="totalPayments">185,75 $</div>
                        <div class="stat-label">Total payé</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">0,087 $</div>
                        <div class="stat-label">Par kWh</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value trend-up">+3.2%</div>
                        <div class="stat-label">vs Période précédente</div>
                    </div>
                </div>
            </div>
            <div class="control-group" data-chart="payments">
                <span class="control-label">Type de graphique :</span>
                <div class="chart-type-selector">
                    <button class="chart-type-btn active" data-type="bar" onclick="changeChartType(\'payments\', \'bar\')">
                        <i class="fas fa-chart-bar"></i>
                        Barres
                    </button>
                    <button class="chart-type-btn" data-type="line" onclick="changeChartType(\'payments\', \'line\')">
                        <i class="fas fa-chart-line"></i>
                        Ligne
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
            <h3 class="table-title">Historique détaillé</h3>
            <button class="export-btn" onclick="exportData()">
                <i class="fas fa-download"></i>
                Exporter données
            </button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Consommation (kWh)</th>
                    <th>Tarif ($/kWh)</th>
                    <th>Montant ($)</th>
                    <th>Statut</th>
                    <th>Tendance</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>';

include 'layout.php';
?>