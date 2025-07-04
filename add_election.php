<?php
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Unauthorized access");
}

$user = $_SESSION['user'];
$successMessage = $errorMessage = '';

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $start = $_POST['start'];
    $end = $_POST['end'];

    if (strtotime($end) <= strtotime($start)) {
        $errorMessage = "End date must be after start date.";
    } elseif (strtotime($start) < strtotime(date("Y-m-d"))) {
        $errorMessage = "Start date cannot be in the past.";
    } else {
        $stmt = $conn->prepare("INSERT INTO elections (title, description, start_date, end_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $desc, $start, $end);
        $stmt->execute();
        $successMessage = "Election added successfully!";
    }
}

$result = $conn->query("SELECT * FROM elections ORDER BY start_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Election - Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="stylesheet/styles.css"/>
  <link rel="icon" type="image/png" href="assets/logo.png">
</head>

<body>
  <!-- Navbar -->
  <header id="custom-header" class="sticky-top">
    <nav class="navbar navbar-expand-lg" style="background-color: #E3E6F3; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.06); padding: 10px 20px;">
      <div class="container-fluid p-0 d-flex align-items-center justify-content-between w-100">
        
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="assets/logo.png" alt="Logo" class="me-3" style="width: 5rem;">
        </a>

        <h1 class="m-0 flex-grow-1 text-center" style="color: #088178; font-weight: bold; font-size: 2 rem;">Online Voting System</h1>

        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
          aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
          <ul class="navbar-nav" id="navbar">
            <li class="nav-item">
              <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="register.php">Register</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="login.php">Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="view_results.php">Results</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#contact">Contact</a>
            </li>
          </ul>
        </div>

      </div>
    </nav>
  </header>

  <!-- Admin Add Election Section -->
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h4 class="mb-4 text-center text-primary"><i class="bi bi-plus-circle"></i> Add New Election</h4>

            <?php if ($errorMessage): ?>
              <div class="alert alert-danger"><?= $errorMessage ?></div>
            <?php endif; ?>

            <form method="POST" id="electionForm">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

              <div class="mb-3">
                <label for="title" class="form-label">Election Title</label>
                <input type="text" name="title" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control" required></textarea>
              </div>

              <div class="mb-3 row">
                <div class="col-md-6">
                  <label for="start" class="form-label">Start Date</label>
                  <input type="date" name="start" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label for="end" class="form-label">End Date</label>
                  <input type="date" name="end" class="form-control" required>
                </div>
              </div>

              <button type="button" class="btn btn-info w-100 mb-3" onclick="previewElection()">Preview Election</button>
              <button type="submit" class="btn btn-success w-100">Add Election</button>
            </form>

            <hr class="my-4">
            <a href="dashboard.php" class="btn btn-secondary w-100"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
          </div>
        </div>

        <!-- Election List -->
        <div class="mt-5">
          <h5 class="text-primary"><i class="bi bi-clock-history"></i> Previous Elections</h5>
          <table class="table table-bordered mt-3">
            <thead class="table-light">
              <tr>
                <th>Title</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['title']) ?></td>
                  <td><?= htmlspecialchars($row['start_date']) ?></td>
                  <td><?= htmlspecialchars($row['end_date']) ?></td>
                  <td>
                    <?php
                    $today = date('Y-m-d');
                    if ($row['start_date'] > $today) echo '<span class="badge bg-warning">Upcoming</span>';
                    elseif ($row['end_date'] < $today) echo '<span class="badge bg-secondary">Completed</span>';
                    else echo '<span class="badge bg-success">Ongoing</span>';
                    ?>
                  </td>
                  <td>
                    <a href="edit_election.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i></a>
                      <form action="delete_election.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this election?');">
                      <input type="hidden" name="id" value="<?= $row['id'] ?>">
                      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                      </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Contact Section -->
  <section id="contact" class="container py-5">
    <h2>Contact Us</h2>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success">
        Thank you for connecting with us. We will contact you.
      </div>
    <?php endif; ?>

    <form action="send_contact.php" method="post">
      <div class="mb-3">
        <label for="name" class="form-label">Name:</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="message" class="form-label">Message:</label>
        <textarea name="message" class="form-control" rows="4" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Send</button>
    </form>
  </section>

  <!-- Preview Modal -->
  <div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Election Preview</h5></div>
        <div class="modal-body" id="previewBody"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Edit</button>
          <button type="submit" form="electionForm" class="btn btn-success">Confirm & Submit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-3 mt-5">
    &copy; <?= date("Y") ?> Online Voting System. All rights reserved.
  </footer>

  <?php if ($successMessage): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="liveToast" class="toast text-bg-success show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
          <?= $successMessage ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Election deleted successfully!</div>
  <?php endif; ?>

  <!-- Javascript Logic -->
  <script>
  function previewElection() {
    const title = document.querySelector('[name="title"]').value;
    const desc = document.querySelector('[name="description"]').value;
    const start = document.querySelector('[name="start"]').value;
    const end = document.querySelector('[name="end"]').value;
    const now = new Date();
    const startDate = new Date(start);
    const endDate = new Date(end);

    if (!title || !desc || !start || !end) {
      alert("Please fill in all fields.");
      return;
    }

    if (startDate < now.setHours(0,0,0,0)) {
      alert("Start date cannot be in the past.");
      return;
    }

    if (endDate <= startDate) {
      alert("End date must be after start date.");
      return;
    }

    document.getElementById('previewBody').innerHTML = `
      <strong>Title:</strong> ${title}<br>
      <strong>Description:</strong><br>${desc}<br>
      <strong>Start Date:</strong> ${start}<br>
      <strong>End Date:</strong> ${end}
    `;
    new bootstrap.Modal(document.getElementById('previewModal')).show();
  }
  </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>