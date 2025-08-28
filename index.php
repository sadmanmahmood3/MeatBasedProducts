
<?php
require_once 'db.php';
include 'header.php';

// Fetch counts and aggregated statistics
$totalProducts = $pdo->query('SELECT COUNT(*) FROM meatproduct')->fetchColumn();
$totalLivestock = $pdo->query('SELECT COUNT(*) FROM livestock')->fetchColumn();
$capacities = $pdo->query('SELECT SUM(Capacity) FROM coldstorage')->fetchColumn();
$occupiedWeight = $pdo->query('SELECT SUM(Weight) FROM packagedbatch')->fetchColumn();
$today = date('Y-m-d');
$threeDays = date('Y-m-d', strtotime('+3 days'));
$expiringSoon = $pdo->prepare('SELECT COUNT(*) FROM packagedbatch WHERE ExpiryDate BETWEEN :today AND :soon');
$expiringSoon->execute([':today' => $today, ':soon' => $threeDays]);
$expiringCount = $expiringSoon->fetchColumn();

// Analytical tool 1: Livestock & Product Growth (by month)
$growth = [];
$livestock = $pdo->query('SELECT Date FROM livestock')->fetchAll(PDO::FETCH_COLUMN);
foreach ($livestock as $d) {
  $m = date('Y-m', strtotime($d));
  if (!isset($growth[$m])) $growth[$m] = ['livestock' => 0, 'product' => 0];
  $growth[$m]['livestock']++;
}
$products = $pdo->query('SELECT PackagingDate FROM packagedbatch')->fetchAll(PDO::FETCH_COLUMN);
foreach ($products as $d) {
  $m = date('Y-m', strtotime($d));
  if (!isset($growth[$m])) $growth[$m] = ['livestock' => 0, 'product' => 0];
  $growth[$m]['product']++;
}
ksort($growth);
$growth_labels = array_keys($growth);
$growth_livestock = array_map(fn($v) => $v['livestock'], $growth);
$growth_products = array_map(fn($v) => $v['product'], $growth);

// Analytical tool 2: Stock Utilization Breakdown
$available = $capacities - $occupiedWeight;
if ($available < 0) $available = 0;

?>
<h2 class="mb-4">Overview</h2>
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="card text-white bg-primary h-100">
      <div class="card-body">
        <h5 class="card-title">Total Products</h5>
        <p class="card-text display-6 fw-bold"><?php echo htmlspecialchars($totalProducts); ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-white bg-success h-100">
      <div class="card-body">
        <h5 class="card-title">Livestock Entries</h5>
        <p class="card-text display-6 fw-bold"><?php echo htmlspecialchars($totalLivestock); ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-white bg-info h-100">
      <div class="card-body">
        <h5 class="card-title">Stock Occupancy</h5>
        <p class="card-text display-6 fw-bold">
          <?php
          if ($capacities > 0) {
            $percent = ($occupiedWeight / $capacities) * 100;
            echo number_format($percent, 1) . '%';
          } else {
            echo 'N/A';
          }
          ?>
        </p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-white bg-danger h-100">
      <div class="card-body">
        <h5 class="card-title">Expiring Soon</h5>
        <p class="card-text display-6 fw-bold"><?php echo htmlspecialchars($expiringCount); ?></p>
      </div>
    </div>
  </div>
</div>

<!-- Analytical Tools Section -->
<div class="row mb-5">
  <div class="col-md-7 mb-4">
    <div class="card">
      <div class="card-header bg-primary text-white">Livestock & Product Growth</div>
      <div class="card-body">
        <canvas id="growthLineChart" height="200"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-5 mb-4">
    <div class="card">
      <div class="card-header bg-success text-white">Stock Utilization Breakdown</div>
      <div class="card-body">
        <canvas id="stockDoughnutChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>


<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Livestock & Product Growth Line Chart
const growthLineCtx = document.getElementById('growthLineChart').getContext('2d');
const growthLineChart = new Chart(growthLineCtx, {
  type: 'line',
  data: {
    labels: <?php echo json_encode($growth_labels); ?>,
    datasets: [
      {
        label: 'Livestock',
        data: <?php echo json_encode($growth_livestock); ?>,
        borderColor: 'rgba(54, 162, 235, 1)',
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        fill: true,
        tension: 0.3
      },
      {
        label: 'Products',
        data: <?php echo json_encode($growth_products); ?>,
        borderColor: 'rgba(255, 99, 132, 1)',
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        fill: true,
        tension: 0.3
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'top' },
      title: { display: false }
    },
    scales: {
      y: { beginAtZero: true, title: { display: true, text: 'Count' } },
      x: { title: { display: true, text: 'Month' } }
    }
  }
});

// Stock Utilization Doughnut Chart
const stockDoughnutCtx = document.getElementById('stockDoughnutChart').getContext('2d');
const stockDoughnutChart = new Chart(stockDoughnutCtx, {
  type: 'doughnut',
  data: {
    labels: ['Occupied', 'Available'],
    datasets: [{
      data: [<?php echo floatval($occupiedWeight); ?>, <?php echo floatval($available); ?>],
      backgroundColor: [
        'rgba(255, 99, 132, 0.7)',
        'rgba(75, 192, 192, 0.7)'
      ],
      borderColor: '#fff',
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' },
      title: { display: false }
    }
  }
});
</script>

<!-- More Analytical Graphs -->
<div class="row mb-5">
  <div class="col-md-4 mb-4">
    <div class="card">
      <div class="card-header bg-info text-white">Animal Type Distribution</div>
      <div class="card-body">
        <canvas id="animalTypePieChart" height="200"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card">
      <div class="card-header bg-warning text-dark">Product Type Distribution</div>
      <div class="card-body">
        <canvas id="productTypePieChart" height="200"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-4">
    <div class="card">
      <div class="card-header bg-danger text-white">Expiring Batches Trend</div>
      <div class="card-body">
        <canvas id="expiringTrendChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<?php
// Animal Type Distribution
$animal_types = $pdo->query('SELECT AnimalType, SUM(Quantity) AS qty FROM livestock GROUP BY AnimalType')->fetchAll(PDO::FETCH_ASSOC);
$animal_labels = array_column($animal_types, 'AnimalType');
$animal_data = array_map('intval', array_column($animal_types, 'qty'));

// Product Type Distribution
$product_types = $pdo->query('SELECT CutType, COUNT(*) AS cnt FROM meatproduct GROUP BY CutType')->fetchAll(PDO::FETCH_ASSOC);
$product_labels = array_column($product_types, 'CutType');
$product_data = array_map('intval', array_column($product_types, 'cnt'));

// Expiring Batches Trend (per month)
$exp_batches = $pdo->query('SELECT ExpiryDate FROM packagedbatch')->fetchAll(PDO::FETCH_COLUMN);
$exp_trend = [];
foreach ($exp_batches as $d) {
  $m = date('Y-m', strtotime($d));
  if (!isset($exp_trend[$m])) $exp_trend[$m] = 0;
  $exp_trend[$m]++;
}
ksort($exp_trend);
$exp_trend_labels = array_keys($exp_trend);
$exp_trend_data = array_values($exp_trend);
?>
<script>
// Animal Type Distribution Pie Chart
const animalTypePieCtx = document.getElementById('animalTypePieChart').getContext('2d');
const animalTypePieChart = new Chart(animalTypePieCtx, {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($animal_labels); ?>,
    datasets: [{
      data: <?php echo json_encode($animal_data); ?>,
      backgroundColor: [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 159, 64, 0.7)'
      ],
      borderColor: '#fff',
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' },
      title: { display: false }
    }
  }
});

// Product Type Distribution Pie Chart
const productTypePieCtx = document.getElementById('productTypePieChart').getContext('2d');
const productTypePieChart = new Chart(productTypePieCtx, {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($product_labels); ?>,
    datasets: [{
      data: <?php echo json_encode($product_data); ?>,
      backgroundColor: [
        'rgba(255, 206, 86, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 99, 132, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 159, 64, 0.7)'
      ],
      borderColor: '#fff',
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' },
      title: { display: false }
    }
  }
});

// Expiring Batches Trend Line Chart
const expiringTrendCtx = document.getElementById('expiringTrendChart').getContext('2d');
const expiringTrendChart = new Chart(expiringTrendCtx, {
  type: 'line',
  data: {
    labels: <?php echo json_encode($exp_trend_labels); ?>,
    datasets: [{
      label: 'Expiring Batches',
      data: <?php echo json_encode($exp_trend_data); ?>,
      borderColor: 'rgba(255, 99, 132, 1)',
      backgroundColor: 'rgba(255, 99, 132, 0.2)',
      fill: true,
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'top' },
      title: { display: false }
    },
    scales: {
      y: { beginAtZero: true, title: { display: true, text: 'Batches' } },
      x: { title: { display: true, text: 'Month' } }
    }
  }
});
</script>

<?php include 'footer.php'; ?>