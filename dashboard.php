<?php
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - Online Voting System</title>
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

  <!-- Dashboard Section -->
  <div class="container py-5">
    <div class="row g-4">
      
      <!-- Profile Card -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="card-title">Welcome, <span class="text-primary"><?= htmlspecialchars($user['username']) ?></span></h5>
            <p class="card-text">Role: 
              <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'success' ?>">
                <?= ucfirst($user['role']) ?>
              </span>
            </p>
          </div>
        </div>
      </div>

      <!-- Action Panel -->
      <div class="col-md-8">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h4 class="mb-4"><i class="bi bi-grid"></i> Dashboard Actions</h4>
            <div class="row g-3">
              <?php if ($user['role'] === 'admin'): ?>
                <div class="col-sm-6">
                  <a href="add_election.php" class="btn btn-outline-primary w-100">
                    <i class="bi bi-plus-circle"></i> Add Election
                  </a>
                </div>
                <div class="col-sm-6">
                  <a href="add_candidate.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-person-plus"></i> Add Candidate
                  </a>
                </div>
              <?php else: ?>
                <div class="col-sm-6">
                  <a href="vote.php" class="btn btn-outline-success w-100">
                    <i class="bi bi-check-circle"></i> Vote Now
                  </a>
                </div>
              <?php endif; ?>

              <div class="col-sm-6">
                <a href="view_results.php" class="btn btn-outline-info w-100">
                  <i class="bi bi-bar-chart-line"></i> View Results
                </a>
              </div>
              <div class="col-sm-6">
                <a href="logout.php" class="btn btn-outline-danger w-100">
                  <i class="bi bi-box-arrow-right"></i> Logout
                </a>
              </div>
            </div>
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