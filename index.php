<?php
// --- CONFIG ---
require('routeros_api.class.php'); // MikroTik API library (add this file in repo)
$db = new PDO("sqlite:voucher.db"); // SQLite DB for storage

$API = new RouterosAPI();
$API->connect("192.168.88.1","admin","password"); // MikroTik credentials

// --- FUNCTIONS ---
function generateVoucher($len=8){
  return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"),0,$len);
}

// --- ROUTES ---
if(isset($_POST['create_voucher'])){
  $code = generateVoucher();
  $pkg  = $_POST['package'];
  $API->comm("/ip/hotspot/user/add",[
    "name"=>$code,"password"=>$code,"profile"=>$pkg,"limit-uptime"=>"30d"
  ]);
  $db->exec("INSERT INTO vouchers(code,package,status) VALUES('$code','$pkg','active')");
  $msg = "Voucher Created: $code";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>MikroTik Voucher System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container">
  <h1 class="mt-3">Voucher Dashboard</h1>

  <!-- Voucher Generator -->
  <form method="post" class="mt-3">
    <label class="form-label">Select Package</label>
    <select name="package" class="form-select">
      <option>Bronze</option>
      <option>Silver</option>
      <option>Gold</option>
    </select>
    <button name="create_voucher" class="btn btn-primary mt-2">Generate Voucher</button>
  </form>
  <?php if(isset($msg)) echo "<div class='alert alert-success mt-2'>$msg</div>"; ?>

  <!-- Client Profiles -->
  <h2 class="mt-4">Clients</h2>
  <?php
    foreach($db->query("SELECT * FROM clients") as $row){
      echo "<p>{$row['name']} - Code: {$row['unique_code']}</p>";
    }
  ?>

  <!-- Tickets -->
  <h2 class="mt-4">Support Tickets</h2>
  <?php
    foreach($db->query("SELECT * FROM tickets") as $row){
      echo "<p>{$row['issue']} - Status: {$row['status']}</p>";
    }
  ?>

  <!-- Reports -->
  <h2 class="mt-4">Sales Report</h2>
  <?php
    foreach($db->query("SELECT * FROM sales") as $row){
      echo "<p>Date: {$row['date']} - Amount: {$row['amount']}</p>";
    }
  ?>
</body>
</html>
