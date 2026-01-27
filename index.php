<?php
// --- CONFIG ---
require('routeros_api.class.php'); // MikroTik API library (add this file in repo)
$db = new PDO("sqlite:voucher.db"); // SQLite DB

$API = new RouterosAPI();
$API->connect("192.168.88.1","admin","password"); // MikroTik credentials

// --- FUNCTIONS ---
function generateVoucher($len=8){
  return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"),0,$len);
}

// Create tables if not exist
$db->exec("CREATE TABLE IF NOT EXISTS clients(id INTEGER PRIMARY KEY, name TEXT, unique_code TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS vouchers(id INTEGER PRIMARY KEY, code TEXT, package TEXT, status TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS tickets(id INTEGER PRIMARY KEY, issue TEXT, status TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS sales(id INTEGER PRIMARY KEY, date TEXT, amount REAL)");

// --- ROUTES ---
$msg = "";
if(isset($_POST['create_voucher'])){
  $code = generateVoucher();
  $pkg  = $_POST['package'];
  $API->comm("/ip/hotspot/user/add",[
    "name"=>$code,"password"=>$code,"profile"=>$pkg,"limit-uptime"=>"30d"
  ]);
  $db->exec("INSERT INTO vouchers(code,package,status) VALUES('$code','$pkg','active')");
  $db->exec("INSERT INTO sales(date,amount) VALUES(date('now'),100)"); // Example price
  // WhatsApp send (basic link)
  $wa = "https://wa.me/?text=Your+Voucher:+$code+Package:+$pkg";
  $msg = "Voucher Created: $code <a href='$wa' target='_blank'>Send via WhatsApp</a>";
}

if(isset($_POST['add_client'])){
  $name = $_POST['name'];
  $ucode = generateVoucher(6);
  $db->exec("INSERT INTO clients(name,unique_code) VALUES('$name','$ucode')");
  $msg = "Client Added: $name ($ucode)";
}

if(isset($_POST['add_ticket'])){
  $issue = $_POST['issue'];
  $db->exec("INSERT INTO tickets(issue,status) VALUES('$issue','open')");
  $msg = "Ticket Added!";
}

// Export CSV
if(isset($_POST['export_csv'])){
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment;filename=sales.csv');
  $out = fopen('php://output','w');
  foreach($db->query("SELECT * FROM sales") as $row){
    fputcsv($out,$row);
  }
  fclose($out);
  exit;
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
  <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

  <!-- Voucher Generator -->
  <h2>Generate Voucher</h2>
  <form method="post">
    <select name="package" class="form-select">
      <option>Bronze</option>
      <option>Silver</option>
      <option>Gold</option>
    </select>
    <button name="create_voucher" class="btn btn-primary mt-2">Generate</button>
  </form>

  <!-- Client Profiles -->
  <h2 class="mt-4">Add Client</h2>
  <form method="post">
    <input type="text" name="name" class="form-control" placeholder="Client Name">
    <button name="add_client" class="btn btn-success mt-2">Add Client</button>
  </form>
  <ul class="mt-2">
    <?php foreach($db->query("SELECT * FROM clients") as $row){
      echo "<li>{$row['name']} - Code: {$row['unique_code']}</li>";
    } ?>
  </ul>

  <!-- Tickets -->
  <h2 class="mt-4">Support Tickets</h2>
  <form method="post">
    <input type="text" name="issue" class="form-control" placeholder="Issue">
    <button name="add_ticket" class="btn btn-warning mt-2">Add Ticket</button>
  </form>
  <ul class="mt-2">
    <?php foreach($db->query("SELECT * FROM tickets") as $row){
      echo "<li>{$row['issue']} - Status: {$row['status']}</li>";
    } ?>
  </ul>

  <!-- Reports -->
  <h2 class="mt-4">Sales Report</h2>
  <ul>
    <?php foreach($db->query("SELECT * FROM sales") as $row){
      echo "<li>{$row['date']} - Amount: {$row['amount']}</li>";
    } ?>
  </ul>
  <form method="post">
    <button name="export_csv" class="btn btn-info mt-2">Export CSV</button>
  </form>
</body>
</html>
