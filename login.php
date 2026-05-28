<?php
session_start();

if (isset($_SESSION["lecturer"])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if (isset($_POST["login"])) {
    $xml = simplexml_load_file("data.xml");
    if (!$xml) {
        die("Error loading data.");
    }

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $found    = false;

    foreach ($xml->lecturers->lecturer as $lec) {
        if ((string)$lec->username === $username &&
            (string)$lec->password === $password) {

            $_SESSION["lecturer"]    = (string) $lec["id"];
            $_SESSION["lec_name"]    = (string) $lec->name;

            $units = array();
            foreach ($lec->units->unit as $u) {
                $units[] = (string) $u;
            }
            $_SESSION["lec_units"] = $units;

            $found = true;
            header("Location: dashboard.php");
            exit();
        }
    }

    if (!$found) {
        $error = "Invalid username or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lecturer Login</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: sans-serif;
      background: #1a1a2e;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      background: white;
      border-radius: 12px;
      padding: 2.5rem 2rem;
      width: 100%;
      max-width: 380px;
      box-shadow: 0 8px 32px rgba(0,0,0,.3);
    }
    .logo {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    .logo h1 { font-size: 1.5rem; color: #1a1a2e; }
    .logo p  { font-size: .85rem; color: #888; margin-top: .3rem; }
    label {
      display: block;
      font-size: .85rem;
      font-weight: bold;
      color: #333;
      margin-bottom: .3rem;
    }
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: .7rem 1rem;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: .95rem;
      margin-bottom: 1rem;
      outline: none;
      transition: border-color .2s;
    }
    input:focus { border-color: #f5a623; }
    button {
      width: 100%;
      padding: .75rem;
      background: #f5a623;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      margin-top: .5rem;
    }
    button:hover { background: #d4881a; }
    .error {
      background: #fff0f0;
      border: 1px solid #f5c6c6;
      color: #c0392b;
      padding: .7rem 1rem;
      border-radius: 6px;
      font-size: .85rem;
      margin-bottom: 1rem;
    }
    .hint {
      text-align: center;
      font-size: .78rem;
      color: #aaa;
      margin-top: 1.2rem;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="logo">
      <h1>Lecturers Portal</h1>
      <p>Sign in to view student marks</p>
    </div>

    <?php if ($error): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Username</label>
      <input type="text" name="username" required>

      <label>Password</label>
      <input type="password" name="password" placeholder="*********" required>

      <button type="submit" name="login">Sign In</button>
    </form>

    <p class="hint">Demo: username <strong>kasaazi/ojok/nantongo</strong> / password <strong>1234</strong></p>
  </div>
</body>
</html>