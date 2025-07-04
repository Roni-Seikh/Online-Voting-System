<?php
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Unauthorized access");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid candidate ID.");
}

$candidate_id = intval($_GET['id']);
$message = "";

// Fetch candidate
$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();

if (!$candidate) {
    die("Candidate not found.");
}

// Fetch elections for dropdown
$elections = $conn->query("SELECT * FROM elections");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $party = trim($_POST['party']);
    $election_id = intval($_POST['election_id']);

    if ($name && $party && $election_id) {
        $stmt = $conn->prepare("UPDATE candidates SET name = ?, party = ?, election_id = ? WHERE id = ?");
        $stmt->bind_param("ssii", $name, $party, $election_id, $candidate_id);
        if ($stmt->execute()) {
            header("Location: add_candidate.php?msg=Candidate+updated+successfully");
            exit;
        } else {
            $message = "Update failed: " . $stmt->error;
        }
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Edit Candidate</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="stylesheet/styles.css"/>
  <link rel="icon" type="image/png" href="assets/logo.png">
</head>

<body class="bg-light">
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

  <!-- Edit Candidate -->
  <div class="container py-5">
    <div class="row">
      <div class="col-lg-6 mx-auto">
        <div class="card shadow">
          <div class="card-body">
            <h4 class="mb-4 text-center"><i class="bi bi-pencil-square"></i> Edit Candidate</h4>
            <?php if ($message): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Candidate Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($candidate['name']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Party</label>
                <input type="text" name="party" class="form-control" value="<?= htmlspecialchars($candidate['party']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Election</label>
                <select name="election_id" class="form-select" required>
                  <option value="">-- Select Election --</option>
                  <?php while ($e = $elections->fetch_assoc()): ?>
                    <option value="<?= $e['id'] ?>" <?= $e['id'] == $candidate['election_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($e['title']) ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-save"></i> Save Changes
              </button>
              <a href="add_candidate.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
            </form>
          </div>
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

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-3 mt-5">
    &copy; <?= date("Y") ?> Online Voting System. All rights reserved.
  </footer>
  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
