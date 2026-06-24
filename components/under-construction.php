<?php
define('CURRENT_VERSION', 'v1.05');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Under Construction</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f1f5f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.10);
      padding: 56px 48px 48px;
      max-width: 480px;
      width: 100%;
      text-align: center;
    }
    .icon { font-size: 72px; margin-bottom: 20px; line-height: 1; }
    .badge {
      display: inline-block;
      background: #fef3c7;
      color: #92400e;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      border-radius: 999px;
      padding: 4px 14px;
      margin-bottom: 20px;
      border: 1px solid #fde68a;
    }
    h1 {
      font-size: 26px;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 12px;
    }
    p {
      font-size: 15px;
      color: #64748b;
      line-height: 1.6;
      margin-bottom: 32px;
    }
    .btn {
      display: inline-block;
      background: #2563eb;
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      text-decoration: none;
      padding: 12px 32px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn:hover { background: #1d4ed8; }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon">&#x1F477;</div>
    <div class="badge">Current Version: <?= CURRENT_VERSION ?></div>
    <h1>Under Construction</h1>
    <p>This feature is not yet available in the current presentation version.<br>
       It will be unlocked in a future release.</p>
    <a class="btn" href="javascript:history.back()">&#8592; Go Back</a>
  </div>
</body>
</html>
<?php exit; ?>
