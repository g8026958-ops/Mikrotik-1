<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ISP Voucher Management</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <style>
    body { font-family: Arial, sans-serif; margin:0; padding:0; background:#f4f4f4; }
    header { background:#2196F3; color:#fff; padding:15px; text-align:center; }
    nav { position:fixed; bottom:0; width:100%; background:#333; display:flex; justify-content:space-around; }
    nav a { color:#fff; padding:10px; text-decoration:none; flex:1; text-align:center; }
    section { padding:20px; display:none; }
    section.active { display:block; }
    .fab { position:fixed; bottom:70px; right:20px; background:#2196F3; color:#fff; border-radius:50%; width:60px; height:60px; display:flex; align-items:center; justify-content:center; font-size:30px; cursor:pointer; }
    .card { background:#fff; margin:10px 0; padding:15px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
    button { background:#2196F3; color:#fff; border:none; padding:10px 15px; border-radius:5px; cursor:pointer; margin-top:5px; }
    input, textarea { width:100%; padding:8px; margin:5px 0; border:1px solid #ccc; border-radius:5px; }
  </style>
</head>
<body>

<header>
  <h1>ISP Voucher Management</h1>
</header>

<section id="dashboard" class="active">
  <h2>Dashboard</h2>
  <div class="card" id="voucherCount">Total Vouchers: 0</div>
  <div class="card" id="clientCount">Active Clients: 0</div>
  <div class="card" id="ticketCount">Open Tickets: 0</div>
</section>

<section id="voucher">
  <h2>Voucher Generator</h2>
  <div class="card">
    <input type="text" id="voucherAmount" placeholder="Voucher Amount">
    <input type="date" id="voucherExpiry">
    <button onclick="createVoucher()">Create Voucher</button>
  </div>
  <div id="voucherList"></div>
</section>

<section id="clients">
  <h2>Client Profiles</h2>
  <div class="card">
    <input type="text" id="clientName" placeholder="Client Name">
    <input type="text" id="clientPhone" placeholder="Phone Number">
    <button onclick="saveClient()">Save Client</button>
  </div>
  <div id="clientList"></div>
</section>

<section id="tickets">
  <h2>Ticket System</h2>
  <div class="card">
    <input type="text" id="ticketTitle" placeholder="Issue Title">
    <textarea id="ticketDesc" placeholder="Describe issue"></textarea>
    <button onclick="createTicket()">Create Ticket</button>
  </div>
  <div id="ticketList"></div>
</section>

<section id="reports">
  <h2>Sales Reports</h2>
  <div class="card">
    <button onclick="exportCSV()">Export CSV</button>
    <button onclick="exportPDF()">Export PDF</button>
  </div>
</section>

<section id="whatsapp">
  <h2>WhatsApp Notifications</h2>
  <div class="card">
    <input type="text" id="waNumber" placeholder="Client Number (e.g. 923001234567)">
    <textarea id="waMessage" placeholder="Message"></textarea>
    <button onclick="sendWhatsApp()">Send WhatsApp</button>
  </div>
</section>

<section id="settings">
  <h2>Settings</h2>
  <div class="card">
    <button onclick="switchTheme()">Switch Theme</button>
    <button onclick="alert('User Management Coming Soon')">Manage Users</button>
    <button onclick="alert('API Setup Coming Soon')">API Setup</button>
  </div>
</section>

<div class="fab" onclick="alert('Quick Action!')">+</div>

<nav>
  <a href="#dashboard" onclick="showSection('dashboard')">🏠</a>
  <a href="#voucher" onclick="showSection('voucher')">🎟</a>
  <a href="#clients" onclick="showSection('clients')">👤</a>
  <a href="#tickets" onclick="showSection('tickets')">📩</a>
  <a href="#reports" onclick="showSection('reports')">📊</a>
  <a href="#whatsapp" onclick="showSection('whatsapp')">📱</a>
  <a href="#settings" onclick="showSection('settings')">⚙️</a>
</nav>
<script>
let vouchers = [];
let clients = [];
let tickets = [];

function showSection(id){
  document.querySelectorAll("section").forEach(sec => sec.classList.remove("active"));
  document.getElementById(id).classList.add("active");
}

function createVoucher(){
  let amount = document.getElementById("voucherAmount").value;
  let expiry = document.getElementById("voucherExpiry").value;
  if(amount && expiry){
    let code = "VCH-" + Math.floor(Math.random()*100000);
    vouchers.push({amount, expiry, code});
    let voucherDiv = document.createElement("div");
    voucherDiv.className = "card";
    voucherDiv.innerHTML = `Voucher: ${amount}, Expiry: ${expiry}, Code: ${code}<br>
      <div id="qrcode-${code}"></div>
      <svg id="barcode-${code}"></svg>`;
    document.getElementById("voucherList").appendChild(voucherDiv);

    // QR Code
    new QRCode(document.getElementById("qrcode-"+code), { text: code, width: 100, height: 100 });

    // Barcode
    JsBarcode("#barcode-"+code, code, {format:"CODE128", width:2, height:40});

    document.getElementById("voucherCount").innerText = "Total Vouchers: " + vouchers.length;
  }
}

function saveClient(){
  let name = document.getElementById("clientName").value;
  let phone = document.getElementById("clientPhone").value;
  if(name && phone){
    clients.push({name, phone});
    document.getElementById("clientList").innerHTML += `<div class='card'>${name} - ${phone}</div>`;
    document.getElementById("clientCount").innerText = "Active Clients: " + clients.length;
  }
}

function createTicket(){
  let title = document.getElementById("ticketTitle").value;
  let desc = document.getElementById("ticketDesc").value;
  if(title && desc){
    tickets.push({title, desc});
    document.getElementById("ticketList").innerHTML += `<div class='card'>${title}: ${desc}</div>`;
    document.getElementById("ticketCount").innerText = "Open Tickets: " + tickets.length;
  }
}

function exportCSV(){
  let data = "Voucher Amount,Expiry,Code\n" + vouchers.map(v => `${v.amount},${v.expiry},${v.code}`).join("\n");
  let blob = new Blob([data], {type:"text/csv"});
  let link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = "report.csv";
  link.click();
}

function exportPDF(){
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  doc.text("Voucher Report", 10, 10);
  vouchers.forEach((v, i) => {
    doc.text(`${i+1}. Amount: ${v.amount}, Expiry: ${v.expiry}, Code: ${v.code}`, 10, 20 + i*10);
  });
  doc.save("report.pdf");
}

function sendWhatsApp(){
  let number = document.getElementById("waNumber").value;
  let message = document.getElementById("waMessage").value;
  if(number && message){
    let url = `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
    window.open(url, "_blank");
  }
}

function switchTheme(){
  document.body.style.background = document.body.style.background === "black" ? "#f4f4f4" : "black";
  document.body.style.color = document.body.style.color === "white" ? "black" : "white";
}
</script>
</body>
</html>
