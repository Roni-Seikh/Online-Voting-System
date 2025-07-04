<?php
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Unauthorized access");
}

if (!isset($_GET['id'])) {
    die("Election ID is missing");
}

$id = intval($_GET['id']);

// CSRF token setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch existing election
$stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Election not found.");
}

$election = $result->fetch_assoc();
$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start'];
    $end_date = $_POST['end'];

    if (strtotime($end_date) <= strtotime($start_date)) {
        $errorMessage = "End date must be after start date.";
    } elseif (strtotime($start_date) < strtotime(date("Y-m-d"))) {
        $errorMessage = "Start date cannot be in the past.";
    } else {
        $updateStmt = $conn->prepare("UPDATE elections SET title = ?, description = ?, start_date = ?, end_date = ? WHERE id = ?");
        $updateStmt->bind_param("ssssi", $title, $description, $start_date, $end_date, $id);
        $updateStmt->execute();
        header("Location: add_election.php?updated=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Election - Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
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

  <!-- Edit Election -->
  <div class="container mt-5">
    <h3 class="mb-4">Edit Election</h3>

    <?php if ($errorMessage): ?>
      <div class="alert alert-danger"><?= $errorMessage ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($election['title']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" rows="4" class="form-control" required><?= htmlspecialchars($election['description']) ?></textarea>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Start Date</label>
          <input type="date" name="start" class="form-control" value="<?= $election['start_date'] ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">End Date</label>
          <input type="date" name="end" class="form-control" value="<?= $election['end_date'] ?>" required>
        </div>
      </div>

      <div class="d-flex justify-content-between">
        <a href="add_election.php" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>

  <!-- Contact Section -->
  <hr class="my-4">
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

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-3 mt-5">
    &copy; <?= date("Y") ?> Online Voting System. All rights reserved.
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
