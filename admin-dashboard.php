<?php include 'db.php'; 

// Overview analytics queries
$stats = [
  'products' => 0,
  'batches' => 0,
  'stock' => 0,
  'expiring' => 0,
  'retailers' => 0,
  'farmers' => 0,
  'compliance' => 0
];


// Total products
$res = $conn->query("SELECT COUNT(*) AS cnt FROM meatproduct");
if ($row = $res->fetch_assoc()) $stats['products'] = $row['cnt'];

// Total batches (distinct BatchID in packagedbatch)
$res = $conn->query("SELECT COUNT(DISTINCT BatchID) AS cnt FROM packagedbatch");
if ($row = $res->fetch_assoc()) $stats['batches'] = $row['cnt'];

// Total stock (sum of all batchwarehouse quantities)
$res = $conn->query("SELECT SUM(Quantity) AS cnt FROM batchwarehouse");
if ($row = $res->fetch_assoc()) $stats['stock'] = $row['cnt'];

// Expiring soon (next 7 days)
$res = $conn->query("SELECT COUNT(*) AS cnt FROM packagedbatch WHERE ExpiryDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
if ($row = $res->fetch_assoc()) $stats['expiring'] = $row['cnt'];

// Retailers
$res = $conn->query("SELECT COUNT(*) AS cnt FROM retailer");
if ($row = $res->fetch_assoc()) $stats['retailers'] = $row['cnt'];

// Farmers
$res = $conn->query("SELECT COUNT(*) AS cnt FROM farmer");
if ($row = $res->fetch_assoc()) $stats['farmers'] = $row['cnt'];

// Compliance issues (example: expired batches in packagedbatch)
$res = $conn->query("SELECT COUNT(*) AS cnt FROM packagedbatch WHERE ExpiryDate < CURDATE()");
if ($row = $res->fetch_assoc()) $stats['compliance'] = $row['cnt'];

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MeatTrack Pro | Admin Dashboard</title>
    <link href="/stylesheets/normalize.css" rel="stylesheet" type="text/css" />
    <link href="/stylesheets/meattrackpro.css" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" type="text/css" />
    <style>
      body {
        background: var(--mtp-gray);
      }
      .mtp-dashboard-wrapper {
        display: flex;
        min-height: 100vh;
      }
      .mtp-sidebar {
        width: 240px;
        background: var(--mtp-dark);
        color: var(--mtp-light);
        display: flex;
        flex-direction: column;
        padding: 2rem 1rem 1rem 1rem;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        z-index: 100;
      }
      .mtp-sidebar-logo {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--mtp-red);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 2.5rem;
      }
      .mtp-sidebar-nav {
        flex: 1;
      }
      .mtp-sidebar-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
      }
      .mtp-sidebar-nav a {
        color: var(--mtp-light);
        text-decoration: none;
        font-size: 1.08rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: background 0.18s;
      }
      .mtp-sidebar-nav a.active, .mtp-sidebar-nav a:hover {
        background: var(--mtp-red);
        color: var(--mtp-light);
      }
      .mtp-dashboard-main {
        margin-left: 240px;
        padding: 2.5rem 2rem 2rem 2rem;
        width: 100%;
      }
      .mtp-dashboard-header {
        font-size: 2rem;
        font-weight: 600;
        color: var(--mtp-red);
        margin-bottom: 2rem;
      }
      .mtp-dashboard-sections {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 2rem;
      }
      .mtp-dashboard-card {
        background: var(--mtp-light);
        border-radius: 1rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 2rem 1.2rem;
        min-height: 220px;
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
      }
      .mtp-dashboard-card h3 {
        color: var(--mtp-green);
        margin-bottom: 0.7rem;
        font-size: 1.2rem;
      }
      .mtp-dashboard-card .mtp-card-icon {
        font-size: 2rem;
        color: var(--mtp-red);
        margin-bottom: 0.5rem;
      }
      .mtp-logout-btn {
        border: 2px solid var(--mtp-red);
        color: var(--mtp-red);
        background: transparent;
        font-weight: 600;
        margin-top: 2rem;
        transition: background 0.2s, color 0.2s;
      }
      .mtp-logout-btn:hover {
        background: var(--mtp-red);
        color: var(--mtp-light);
      }
      @media (max-width: 900px) {
        .mtp-dashboard-main {
          margin-left: 0;
          padding: 1.2rem 0.5rem;
        }
        .mtp-sidebar {
          position: static;
          width: 100%;
          flex-direction: row;
          align-items: center;
          justify-content: space-between;
          padding: 1rem;
        }
        .mtp-sidebar-nav ul {
          flex-direction: row;
          gap: 0.7rem;
        }
        .mtp-logout-btn {
          margin-top: 0.5rem;
          margin-left: 1rem;
          display: inline-block;
        }
      }
    </style>
  </head>
  <body>
    <div class="mtp-dashboard-wrapper">
      <aside class="mtp-sidebar">
        <div class="mtp-sidebar-logo">
          <i class="fas fa-drumstick-bite"></i>
          <span>MeatTrack Pro</span>
        </div>
        <nav class="mtp-sidebar-nav">
          <ul>
            <li><a href="meat-data.php"><i class="fas fa-info-circle"></i>Overview</a></li>
            <li><a href="meat-data.php"><i class="fas fa-info-circle"></i>Meat Product Data</a></li>
            <li><a href="incoming.html"><i class="fas fa-truck-loading"></i>Incoming Livestock/Meat</a></li>
            <li><a href="stock.html"><i class="fas fa-warehouse"></i>Stock Levels</a></li>
            <li><a href="expiration.html"><i class="fas fa-calendar-alt"></i>Expiration & Batch</a></li>
            <li><a href="storage.html"><i class="fas fa-thermometer-half"></i>Storage Conditions</a></li>
            <li><a href="yield.html"><i class="fas fa-chart-line"></i>Yield Analysis</a></li>
            <li><a href="compliance.html"><i class="fas fa-clipboard-check"></i>Compliance & Recall</a></li>
          </ul>
          <a href="index.html" class="mtp-btn mtp-btn-outline mtp-logout-btn" style="margin-top:2rem;display:block;text-align:center;"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </nav>
      </aside>
      <main class="mtp-dashboard-main">
        <div class="mtp-dashboard-header">Admin Dashboard</div>
        <!-- Overview Section: Cards and Analytics Charts -->
        <div class="mtp-dashboard-sections" style="margin-bottom:2.5rem;">
          <!-- Overview Cards -->
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.5rem;margin-bottom:2.5rem;">
            <div class="mtp-dashboard-card" style="min-height:120px;">
              <div class="mtp-card-icon"><i class="fas fa-box"></i></div>
              <h3 style="margin:0.5rem 0 0.25rem 0;">Total Products</h3>
              <div style="font-size:2rem;font-weight:bold;"><?php echo h($stats['products']); ?></div>
            </div>
            <div class="mtp-dashboard-card" style="min-height:120px;">
              <div class="mtp-card-icon"><i class="fas fa-layer-group"></i></div>
              <h3 style="margin:0.5rem 0 0.25rem 0;">Total Batches</h3>
              <div style="font-size:2rem;font-weight:bold;"><?php echo h($stats['batches']); ?></div>
            </div>
            <div class="mtp-dashboard-card" style="min-height:120px;">
              <div class="mtp-card-icon"><i class="fas fa-warehouse"></i></div>
              <h3 style="margin:0.5rem 0 0.25rem 0;">Stock Quantity</h3>
              <div style="font-size:2rem;font-weight:bold;"><?php echo h($stats['stock']); ?></div>
            </div>
            <div class="mtp-dashboard-card" style="min-height:120px;">
              <div class="mtp-card-icon"><i class="fas fa-calendar-times"></i></div>
              <h3 style="margin:0.5rem 0 0.25rem 0;">Expiring Soon</h3>
              <div style="font-size:2rem;font-weight:bold;"><?php echo h($stats['expiring']); ?></div>
            </div>
            <div class="mtp-dashboard-card" style="min-height:120px;">
              <div class="mtp-card-icon"><i class="fas fa-store"></i></div>
              <h3 style="margin:0.5rem 0 0.25rem 0;">Retailers</h3>
              <div style="font-size:2rem;font-weight:bold;"><?php echo h($stats['retailers']); ?></div>
            </div>
            <div class="mtp-dashboard-card" style="min-height:120px;">
              <div class="mtp-card-icon"><i class="fas fa-tractor"></i></div>
              <h3 style="margin:0.5rem 0 0.25rem 0;">Farmers</h3>
              <div style="font-size:2rem;font-weight:bold;"><?php echo h($stats['farmers']); ?></div>
            </div>
            <div class="mtp-dashboard-card" style="min-height:120px;">
              <div class="mtp-card-icon"><i class="fas fa-exclamation-triangle"></i></div>
              <h3 style="margin:0.5rem 0 0.25rem 0;">Compliance Issues</h3>
              <div style="font-size:2rem;font-weight:bold;"><?php echo h($stats['compliance']); ?></div>
            </div>
          </div>
          <!-- End Overview Cards -->
          <!-- Overview Analytics Charts -->
          <div style="width:100%;max-width:900px;margin:auto;">
            <canvas id="overviewBarChart" height="120"></canvas>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:2rem;justify-content:center;margin-top:2rem;">
            <div style="flex:1 1 300px;max-width:400px;min-width:250px;">
              <canvas id="overviewPieChart"></canvas>
            </div>
            <div style="flex:1 1 300px;max-width:400px;min-width:250px;">
              <canvas id="overviewDoughnutChart"></canvas>
            </div>
          </div>
          <!-- End Overview Analytics Charts -->
        </div>
        <!-- End Overview Section -->

        <!-- Chart.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        // PHP data to JS
        const stats = <?php echo json_encode($stats); ?>;
        // Bar Chart
        new Chart(document.getElementById('overviewBarChart').getContext('2d'), {
          type: 'bar',
          data: {
            labels: ['Products', 'Batches', 'Stock', 'Expiring Soon', 'Retailers', 'Farmers', 'Compliance'],
            datasets: [{
              label: 'Overview',
              data: [stats.products, stats.batches, stats.stock, stats.expiring, stats.retailers, stats.farmers, stats.compliance],
              backgroundColor: [
                '#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69'
              ]
            }]
          },
          options: {
            responsive: true,
            plugins: {legend: {display: false}},
            scales: {y: {beginAtZero: true}}
          }
        });
        // Pie Chart (Product Distribution Example)
        new Chart(document.getElementById('overviewPieChart').getContext('2d'), {
          type: 'pie',
          data: {
            labels: ['Products', 'Batches', 'Stock', 'Expiring', 'Retailers', 'Farmers', 'Compliance'],
            datasets: [{
              data: [stats.products, stats.batches, stats.stock, stats.expiring, stats.retailers, stats.farmers, stats.compliance],
              backgroundColor: [
                '#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69'
              ]
            }]
          },
          options: {responsive:true}
        });
        // Doughnut Chart (Stock vs Expiring vs Compliance)
        new Chart(document.getElementById('overviewDoughnutChart').getContext('2d'), {
          type: 'doughnut',
          data: {
            labels: ['Stock', 'Expiring Soon', 'Compliance'],
            datasets: [{
              data: [stats.stock, stats.expiring, stats.compliance],
              backgroundColor: ['#36b9cc','#f6c23e','#e74a3b']
            }]
          },
          options: {responsive:true}
        });
        </script>
        <div class="mtp-dashboard-sections">
          <section class="mtp-dashboard-card" id="meat-data">
            <div class="mtp-card-icon"><i class="fas fa-info-circle"></i></div>
            <h3>Meat Product Data</h3>
            <ul>
              <li>Animal Type (e.g., beef, poultry, pork)</li>
              <li>Cut Type</li>
              <li>Processing Date</li>
              <li>Storage Requirements</li>
              <li>Shelf Life</li>
              <li>Packaging Details</li>
              <li>Supplier Information</li>
            </ul>
            <div style="overflow:auto;">
              <table class="mtp-table" style="width:100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Product ID</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Batch ID</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Animal Type</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Cut Type</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Processing Date</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Storage Requirements</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Shelf Life (days)</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Packaging Details</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sql = "SELECT mp.ProductID, mp.BatchID, ls.AnimalType, mp.CutType, pr.ProcessingDate, mp.StorageRequirements, mp.ShelfLife, pk.PackagingDetails
                            FROM meatproduct mp
                            LEFT JOIN processedbatch pr ON mp.BatchID = pr.BatchID
                            LEFT JOIN livestock ls ON pr.LivestockID = ls.LivestockID
                            LEFT JOIN packagedbatch pk ON mp.BatchID = pk.BatchID
                            LIMIT 10";
                    if ($result = $conn->query($sql)) {
                      if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                          echo "<tr>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['ProductID']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['BatchID']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['AnimalType']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['CutType']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['ProcessingDate']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['StorageRequirements']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['ShelfLife']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['PackagingDetails']) . "</td>" .
                               "</tr>";
                        }
                      } else {
                        echo "<tr><td colspan='8' style='padding:8px;'>No data available.</td></tr>";
                      }
                      $result->free();
                    } else {
                      echo "<tr><td colspan='8' style='padding:8px; color:#a00;'>Query error.</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </section>
          <section class="mtp-dashboard-card" id="incoming">
            <div class="mtp-card-icon"><i class="fas fa-truck-loading"></i></div>
            <h3>Incoming Livestock/Meat Monitoring</h3>
            <ul>
              <li>Track incoming livestock/meat cuts</li>
              <li>Quantities & Processing Dates</li>
              <li>Movement across processing units</li>
              <li>Traceability & Quality Control</li>
            </ul>
            <div style="overflow:auto;">
              <table class="mtp-table" style="width:100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Livestock ID</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Farmer</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Animal Type</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Quantity</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sql = "SELECT l.LivestockID, f.Name AS FarmerName, l.AnimalType, l.Quantity, l.Date
                            FROM livestock l
                            LEFT JOIN farmer f ON l.FarmerID = f.FarmerID
                            ORDER BY l.Date DESC
                            LIMIT 10";
                    if ($result = $conn->query($sql)) {
                      if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                          echo "<tr>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['LivestockID']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['FarmerName']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['AnimalType']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Quantity']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Date']) . "</td>" .
                               "</tr>";
                        }
                      } else {
                        echo "<tr><td colspan='5' style='padding:8px;'>No data available.</td></tr>";
                      }
                      $result->free();
                    } else {
                      echo "<tr><td colspan='5' style='padding:8px; color:#a00;'>Query error.</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </section>
          <section class="mtp-dashboard-card" id="stock">
            <div class="mtp-card-icon"><i class="fas fa-warehouse"></i></div>
            <h3>Stock Levels</h3>
            <ul>
              <li>Continuous tracking across facilities</li>
              <li>Timely restocking</li>
              <li>Reduce risk of stockouts/overstocking</li>
            </ul>
            <div style="overflow:auto;">
              <table class="mtp-table" style="width:100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Batch ID</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Warehouse</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Date</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Quantity</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Weight</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sql = "SELECT bw.BatchID, w.Address AS Warehouse, bw.Date, bw.Quantity, bw.Weight
                            FROM batchwarehouse bw
                            JOIN warehouse w ON w.WarehouseID = bw.WarehouseID
                            ORDER BY bw.Date DESC
                            LIMIT 10";
                    if ($result = $conn->query($sql)) {
                      if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                          echo "<tr>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['BatchID']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Warehouse']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Date']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Quantity']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Weight']) . "</td>" .
                               "</tr>";
                        }
                      } else {
                        echo "<tr><td colspan='5' style='padding:8px;'>No data available.</td></tr>";
                      }
                      $result->free();
                    } else {
                      echo "<tr><td colspan='5' style='padding:8px; color:#a00;'>Query error.</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </section>
          <section class="mtp-dashboard-card" id="expiration">
            <div class="mtp-card-icon"><i class="fas fa-calendar-alt"></i></div>
            <h3>Expiration & Batch Tracking</h3>
            <ul>
              <li>Track expiration dates & batch numbers</li>
              <li>FIFO/FEFO inventory rotation</li>
              <li>Minimize spoilage & waste</li>
            </ul>
            <div style="overflow:auto;">
              <table class="mtp-table" style="width:100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Batch ID</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Packaging Date</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Expiry Date</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Quantity</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Weight</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Barcode</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sql = "SELECT BatchID, PackagingDate, ExpiryDate, Quantity, Weight, Barcode
                            FROM packagedbatch
                            ORDER BY ExpiryDate ASC
                            LIMIT 10";
                    if ($result = $conn->query($sql)) {
                      if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                          echo "<tr>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['BatchID']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['PackagingDate']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['ExpiryDate']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Quantity']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Weight']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Barcode']) . "</td>" .
                               "</tr>";
                        }
                      } else {
                        echo "<tr><td colspan='6' style='padding:8px;'>No data available.</td></tr>";
                      }
                      $result->free();
                    } else {
                      echo "<tr><td colspan='6' style='padding:8px; color:#a00;'>Query error.</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </section>
          <section class="mtp-dashboard-card" id="storage">
            <div class="mtp-card-icon"><i class="fas fa-thermometer-half"></i></div>
            <h3>Storage Condition Monitoring</h3>
            <ul>
              <li>Sensor integration (temperature, humidity)</li>
              <li>Optimal storage environments</li>
              <li>Preserve meat quality</li>
            </ul>
            <div style="overflow:auto;">
              <table class="mtp-table" style="width:100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Storage ID</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Name</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Capacity</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Current Temp (°C)</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Current Humidity (%)</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Monitoring System</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sql = "SELECT StorageID, Name, Capacity, CurrentTemperature, CurrentHumidity, MonitoringSystemID
                            FROM coldstorage
                            LIMIT 10";
                    if ($result = $conn->query($sql)) {
                      if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                          echo "<tr>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['StorageID']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Name']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Capacity']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['CurrentTemperature']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['CurrentHumidity']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['MonitoringSystemID']) . "</td>" .
                               "</tr>";
                        }
                      } else {
                        echo "<tr><td colspan='6' style='padding:8px;'>No data available.</td></tr>";
                      }
                      $result->free();
                    } else {
                      echo "<tr><td colspan='6' style='padding:8px; color:#a00;'>Query error.</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </section>
          <section class="mtp-dashboard-card" id="yield">
            <div class="mtp-card-icon"><i class="fas fa-chart-line"></i></div>
            <h3>Yield Analysis</h3>
            <ul>
              <li>Track yields from processing</li>
              <li>Trimmings, bones, offal</li>
              <li>Assess efficiency & reduce waste</li>
            </ul>
            <div style="overflow:auto;">
              <table class="mtp-table" style="width:100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Batch ID</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Processing Date</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Quantity</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Weight</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Expiry Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sql = "SELECT BatchID, ProcessingDate, Quantity, Weight, ExpiryDate
                            FROM processedbatch
                            ORDER BY ProcessingDate DESC
                            LIMIT 10";
                    if ($result = $conn->query($sql)) {
                      if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                          echo "<tr>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['BatchID']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['ProcessingDate']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Quantity']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Weight']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['ExpiryDate']) . "</td>" .
                               "</tr>";
                        }
                      } else {
                        echo "<tr><td colspan='5' style='padding:8px;'>No data available.</td></tr>";
                      }
                      $result->free();
                    } else {
                      echo "<tr><td colspan='5' style='padding:8px; color:#a00;'>Query error.</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </section>
          <section class="mtp-dashboard-card" id="compliance">
            <div class="mtp-card-icon"><i class="fas fa-clipboard-check"></i></div>
            <h3>Compliance & Recall</h3>
            <ul>
              <li>Detailed records for food safety</li>
              <li>Trace & recall specific batches</li>
              <li>Regulatory adherence</li>
            </ul>
            <div style="overflow:auto;">
              <table class="mtp-table" style="width:100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Batch ID</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Retailer ID</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Retailer Location</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Date</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Quantity</th>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd;">Weight</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sql = "SELECT br.BatchID, br.RetailerID, rs.Location, br.Date, br.Quantity, br.Weight
                            FROM batchretailer br
                            LEFT JOIN retailerstore rs ON br.RetailerID = rs.RetailerID
                            ORDER BY br.Date DESC
                            LIMIT 10";
                    if ($result = $conn->query($sql)) {
                      if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                          echo "<tr>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['BatchID']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['RetailerID']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Location']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Date']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Quantity']) . "</td>" .
                               "<td style='padding:8px; border-bottom:1px solid #f0f0f0;'>" . h($row['Weight']) . "</td>" .
                               "</tr>";
                        }
                      } else {
                        echo "<tr><td colspan='6' style='padding:8px;'>No data available.</td></tr>";
                      }
                      $result->free();
                    } else {
                      echo "<tr><td colspan='6' style='padding:8px; color:#a00;'>Query error.</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </main>
    </div>
  </body>
</html> 