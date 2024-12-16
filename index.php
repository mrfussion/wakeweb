<?php
/**
 * Proyect Name: wake web
 * 
 * Description: Web interface to remotely wake up a computer via Wake-on-LAN.
 * Allows users to select a computer, verify password, and send the WOL packet to wake the machine.
 * 
 * Author: Maximiliano Garcia Silva
 * Email: mgsilva@gmail.com
 * 
 * Created: 2024-12-15
 * Last Modified: 2024-12-15
 * Version: 1.0
 * License: Apache License 2.0
 * Repository: https://github.com/mrfussion/wakeweb
 * 
 * Copyright (c) 2024, Maximiliano Garcia Silva. All rights reserved.
 * 
 * This code is provided "as is" without warranty of any kind, either express or implied, 
 * including but not limited to the warranties of merchantability, fitness for a particular purpose, 
 * or noninfringement. In no event shall the authors or copyright holders be liable for any claim, 
 * damages, or other liability, whether in an action of contract, tort, or otherwise, arising from, 
 * out of, or in connection with the software or the use or other dealings in the software.
 */

$config = include('config.php');
$wakeOnLan = '/usr/bin/wakeonlan';

function wakeExec($wol, $mac) {
  $result = shell_exec($wol . " " . $mac . " " . ">/dev/null 2>&1; echo $?");
  if ($result == 0) {
    return true;
  } else {
    return "Failed: <pre>" . $result . '</pre>';
  }
}

function checkOnline($host, $port, $timeout=1) {
  return @fsockopen($host, $port,$errno, $errstr, $timeout) ? true : false;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'])) {
    $response = [];

    if ($_GET['action'] == 'wake' && isset($_GET['computer'])) {
        $pcSelected = $_GET['computer'];
        $pcConfig = $config[$pcSelected];
        
        if ($pcConfig) {
            $status = wakeExec($wakeOnLan, $pcConfig['mac']) ? "Sended" : "Failed";
            $response['status'] = $status;
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'computer not found']);
        }
    } elseif ($_GET['action'] == 'status' && isset($_GET['computer'])) {
        $pcSelected = $_GET['computer'];
        $pcConfig = $config[$pcSelected];

        if($pcConfig && checkOnline($pcSelected, $pcConfig['pingPort'])) {
            echo json_encode(['status' => 'online']);
        } else {
            echo json_encode(['status' => 'offline']);
        }
    } else {
        echo json_encode(['error' => 'Invalid action or missing parameters']);
    }
    exit;
}

$pcSelected = isset($_GET['computer']) ? $_GET['computer'] : array_key_first($config);
$pcConfig = $config[$pcSelected];

if (isset($_POST['wake']) && isset($_POST['pwd'])) {
    $pcSelected = isset($_GET['computer']) ? $_GET['computer'] : array_key_first($config);
    $pcConfig = $config[$pcSelected];

    if (password_verify($_POST['pwd'], $pcConfig['password'])) {
        $status = wakeExec($wakeOnLan, $pcConfig['mac']) ? "Sended" : "Failed";
    } else {
        $status = 'Incorrect password';
    }
}

$online = checkOnline($pcConfig['host'], $pcConfig['pingPort']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wakeweb</title>

  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #1a1a2e;
      color: #f8f9fa;
      transition: all 0.5s ease;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    body.light-mode {
      background-color: #f8f9fa;
      color: #212529;
    }

    .card {
      border-radius: 1rem;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      padding: 1.5rem;
      max-width: 400px;
      width: 100%;
      transition: background-color 0.5s ease, color 0.5s ease;
    }

    .card.light-mode {
      background-color: #ffffff;
      color: #212529;
    }

    .card.dark-mode {
      background-color: #343a40;
      color: #f8f9fa;
    }

    .form-switch .form-check-input {
      width: 2.5em;
      height: 1.25em;
    }

    h4 {
      font-weight: bold;
      text-align: center;
      margin-bottom: 1rem;
    }

    .btn {
      border-radius: 2rem;
      font-weight: 500;
    }

    p {
      margin: 0;
      cursor: pointer;
      font-size: 1rem;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="card dark-mode" id="theme-card">
    <div class="d-flex justify-content-end mb-3">
      <!-- Theme toggle switch -->
      <div class="form-switch">
        <input class="form-check-input" type="checkbox" id="theme-switch">
        <label for="theme-switch" class="ms-2">🌙 / ☀️</label>
      </div>
    </div>

    <h4>Wakeweb</h4>
    <form action="" method="post">
      <!-- Select computer -->
      <div class="mb-3">
        <label for="pc-select" class="form-label">Select computer</label>
        <select id="pc-select" class="form-select" name="computer" onchange="pcSelected()">
          <?php
            foreach ($config as $key => $value) {
              echo '<option value="' . $key . '" ' . ($key === $pcSelected ? "selected" : "") . '>' . $value["pcName"] . '</option>';
            }
          ?>
        </select>
      </div>

      <!-- Password -->
      <div class="mb-3">
        <label for="pwd" class="form-label">Password</label>
        <input type="password" class="form-control" id="pwd" name="pwd" placeholder="Password">
      </div>

      <!-- State Online/Offline -->
      <div class="text-center mb-3">
        <?php
          if ($online) {
            echo '<p class="text-success" onclick="reload()">Online</p>';
          } else {
            echo '<p class="text-danger" onclick="reload()">Offline</p>';
          }
        ?>
      </div>

      <!-- Wake Up Button -->
      <div class="d-grid">
        <button class="btn btn-primary" type="submit" id="wake-btn" name="wake" value="wake" <?= $online ? "disabled" : "" ?>>Wake up</button>
      </div>
    </form>

    <!-- Show Status -->
    <div class="text-center mt-3">
      <?= $status ?>
    </div>
  </div>

  <!-- Bootstrap 5.3 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const themeSwitch = document.getElementById('theme-switch');
    const body = document.body;
    const themeCard = document.getElementById('theme-card');

    // Load theme from localStorage
    if (localStorage.getItem('theme') === 'light') {
      body.classList.add('light-mode');
      themeCard.classList.remove('dark-mode');
      themeCard.classList.add('light-mode');
      themeSwitch.checked = true;
    }

    // Toggle between light and dark modes
    themeSwitch.addEventListener('change', () => {
      body.classList.toggle('light-mode');
      themeCard.classList.toggle('dark-mode');
      themeCard.classList.toggle('light-mode');
      const theme = body.classList.contains('light-mode') ? 'light' : 'dark';
      localStorage.setItem('theme', theme);
    });

    // Action to select computer
    function pcSelected() {
      const selected = document.getElementById("pc-select").value;
      const url = new URL(window.location);
      url.searchParams.set('computer', selected);
      window.open(url, "_self");
    }

    // Reload the page
    function reload() {
      window.location.reload();
    }
  </script>
</body>
</html>
