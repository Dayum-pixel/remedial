<?php
session_start();

if (!isset($_SESSION["lecturer"])) {
    header("Location: login.php");
    exit();
}

$xml = simplexml_load_file("data.xml");
if (!$xml) {
    die("Error loading data.");
}

$lec_name  = $_SESSION["lec_name"];
$lec_units = $_SESSION["lec_units"];

$unit_names = array();
foreach ($xml->units->unit as $u) {
    $unit_names[(string)$u["id"]] = (string)$u->name;
}

$selected_unit = isset($_GET["unit"]) ? $_GET["unit"] : "all";

function getGrade($total) {
    if ($total >= 80) return "A";
    if ($total >= 70) return "B";
    if ($total >= 60) return "C";
    if ($total >= 50) return "D";
    return "F";
}

function getStatus($total) {
    return $total >= 50 ? "Pass" : "Fail";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: sans-serif; background: #f0f2f5; min-height: 100vh; }
    nav {
      background: #1a1a2e;
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    nav h1 { font-size: 1.1rem; }
    nav span { font-size: .85rem; color: #aaa; }
    .logout {
      background: #f5a623;
      color: white;
      border: none;
      padding: .4rem .9rem;
      border-radius: 6px;
      font-size: .85rem;
      text-decoration: none;
    }
    .logout:hover { background: #d4881a; }
    .container { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem; }
    .unit-tabs {
      display: flex;
      flex-wrap: wrap;
      gap: .5rem;
      margin-bottom: 1.5rem;
    }
    .unit-tab {
      padding: .45rem 1rem;
      border-radius: 6px;
      background: white;
      border: 1px solid #ddd;
      font-size: .82rem;
      text-decoration: none;
      color: #333;
      transition: all .2s;
    }
    .unit-tab:hover  { border-color: #f5a623; color: #f5a623; }
    .unit-tab.active { background: #f5a623; color: white; border-color: #f5a623; }
    .unit-section { margin-bottom: 2.5rem; }
    .unit-title {
      font-size: 1rem;
      font-weight: bold;
      color: #1a1a2e;
      margin-bottom: .8rem;
      padding-bottom: .4rem;
      border-bottom: 3px solid #f5a623;
    }
    .unit-title span {
      font-size: .8rem;
      font-weight: normal;
      color: #888;
      margin-left: .5rem;
    }
    table { width: 100%; border-collapse: collapse; background: white;
            border-radius: 8px; overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,.07); }
    th { background: #1a1a2e; color: white; padding: .65rem 1rem;
         text-align: left; font-size: .82rem; }
    td { padding: .6rem 1rem; border-bottom: 1px solid #eee; font-size: .85rem; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #fffbf3; }
    .badge {
      display: inline-block;
      padding: .15rem .55rem;
      border-radius: 99px;
      font-size: .75rem;
      font-weight: bold;
    }
    .pass    { background: #e6f9ec; color: #1e7e34; }
    .fail    { background: #fdecea; color: #c0392b; }
    .grade-A { background: #e3f2fd; color: #1565c0; }
    .grade-B { background: #e8f5e9; color: #2e7d32; }
    .grade-C { background: #fff8e1; color: #f57f17; }
    .grade-D { background: #fff3e0; color: #e65100; }
    .grade-F { background: #fdecea; color: #c0392b; }
    .sub-yes { color: #1e7e34; font-weight: bold; }
    .sub-no  { color: #c0392b; font-weight: bold; }
    .summary {
      display: flex;
      gap: 1rem;
      margin-bottom: .8rem;
      flex-wrap: wrap;
    }
    .summary-card {
      background: white;
      border-radius: 8px;
      padding: .6rem 1rem;
      font-size: .8rem;
      box-shadow: 0 1px 4px rgba(0,0,0,.07);
      border-left: 3px solid #f5a623;
    }
    .summary-card strong { display: block; font-size: 1.1rem; color: #1a1a2e; }
  </style>
</head>
<body>

<nav>
  <h1>Lecturer's Portal</h1>
  <span>Welcome, <?php echo htmlspecialchars($lec_name); ?></span>
  <a href="logout.php" class="logout">Logout</a>
</nav>

<div class="container">

  <div class="unit-tabs">
    <a href="dashboard.php" class="unit-tab <?php echo $selected_unit === 'all' ? 'active' : ''; ?>">
      All Units
    </a>
    <?php foreach ($lec_units as $uid): ?>
      <a href="dashboard.php?unit=<?php echo $uid; ?>"
         class="unit-tab <?php echo $selected_unit === $uid ? 'active' : ''; ?>">
        <?php echo $uid . " - " . htmlspecialchars($unit_names[$uid]); ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php foreach ($lec_units as $uid): ?>
    <?php if ($selected_unit !== "all" && $selected_unit !== $uid) continue; ?>

    <?php
    $rows        = array();
    $total_marks = 0;
    $pass_count  = 0;
    $fail_count  = 0;

    foreach ($xml->students->student as $stu) {
        foreach ($stu->marks->unit as $m) {
            if ((string)$m["id"] !== $uid) continue;

            $exam       = (int) $m["exam"];
            $assignment = (int) $m["assignment"];
            $submitted  = (string) $m["submitted"];
            $total      = round(($exam * 0.6) + ($assignment * 0.4));
            $grade      = getGrade($total);
            $status     = getStatus($total);

            $total_marks += $total;
            if ($status === "Pass") {
                $pass_count++;
            } else {
                $fail_count++;
            }

            $rows[] = array(
                "id"         => (string) $stu["id"],
                "name"       => (string) $stu->name,
                "Reg. No."    => (string) $stu["reg"],
                "exam"       => $exam,
                "assignment" => $assignment,
                "submitted"  => $submitted,
                "total"      => $total,
                "grade"      => $grade,
                "status"     => $status,
            );
        }
    }

    $student_count = count($rows);
    $avg = $student_count > 0 ? round($total_marks / $student_count, 1) : 0;

    if ($student_count > 0) {
        $pass_rate = round(($pass_count / $student_count) * 100);
    } else {
        $pass_rate = 0;
    }
    ?>

  <div class="unit-section">
    <div class="unit-title">
      <?php echo $uid . " - " . htmlspecialchars($unit_names[$uid]); ?>
      <span><?php echo $student_count; ?> students</span>
    </div>

    <div class="summary">
      <div class="summary-card">
        <strong><?php echo $avg; ?>%</strong>Class Average
      </div>
      <div class="summary-card">
        <strong style="color:#1e7e34"><?php echo $pass_count; ?></strong>Passed
      </div>
      <div class="summary-card">
        <strong style="color:#c0392b"><?php echo $fail_count; ?></strong>Failed
      </div>
      <div class="summary-card">
        <strong><?php echo $pass_rate; ?>%</strong>Pass Rate
      </div>
    </div>

    <table>
      <tr>
        <th>ID</th>
        <th>Student Name</th>
        <th>Reg. No.</th>
        <th>Exam (60%)</th>
        <th>Assignment (40%)</th>
        <th>Assignment Submitted</th>
        <th>Total</th>
        <th>Grade</th>
        <th>Status</th>
      </tr>
      <?php foreach ($rows as $row): ?>
      <tr>
        <td><?php echo $row["id"]; ?></td>
        <td><?php echo htmlspecialchars($row["name"]); ?></td>
        <td><?php echo htmlspecialchars($row["Reg. No."]); ?></td>
        <td><?php echo $row["exam"]; ?></td>
        <td><?php echo $row["assignment"]; ?></td>
        <td>
          <span class="<?php echo $row["submitted"] === "Yes" ? "sub-yes" : "sub-no"; ?>">
            <?php echo $row["submitted"]; ?>
          </span>
        </td>
        <td><?php echo $row["total"]; ?>%</td>
        <td>
          <span class="badge grade-<?php echo $row["grade"]; ?>">
            <?php echo $row["grade"]; ?>
          </span>
        </td>
        <td>
          <span class="badge <?php echo strtolower($row["status"]); ?>">
            <?php echo $row["status"]; ?>
          </span>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <?php endforeach; ?>

</div>
</body>
</html>