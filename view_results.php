<?php
include 'config.php';

// Function to fetch candidates and vote counts per election
function getElectionResults($conn, $election_id) {
    $stmt = $conn->prepare("
        SELECT candidates.name, COUNT(votes.id) AS total_votes
        FROM candidates
        LEFT JOIN votes ON votes.candidate_id = candidates.id
        WHERE candidates.election_id = ?
        GROUP BY candidates.id
    ");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    return $stmt->get_result();
}

$elections = $conn->query("SELECT * FROM elections");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Result - Dashboard - Online Voting System</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"/>
  <link rel="stylesheet" href="stylesheet/styles.css"/>
  <link rel="icon" type="image/png" href="assets/logo.png">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
              <a class="nav-link" href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="view_results.php">Results</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#contact">Contact</a>
            </li>
          </ul>
        </div>

      </div>
    </nav>
  </header>

  <!-- Dashboard Results Section -->
  <section class="container py-5">
    <h2 class="mb-4">Election Results</h2>

    <?php while ($election = $elections->fetch_assoc()):
      $election_id = $election['id'];
      $results = getElectionResults($conn, $election_id);

      $total_votes = 0;
      $candidates = [];
      while ($row = $results->fetch_assoc()) {
          $total_votes += $row['total_votes'];
          $candidates[] = $row;
      }
    ?>

    <div class="card mb-5">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><?= htmlspecialchars($election['title']) ?></h5>
      </div>
      <div class="card-body">
        <?php if (count($candidates) > 0): ?>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Candidate</th>
                <th>Votes</th>
                <th>Percentage</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $candidateNames = [];
                $voteCounts = [];

                foreach ($candidates as $candidate): 
                  $percentage = $total_votes > 0 ? ($candidate['total_votes'] / $total_votes) * 100 : 0;
                  $candidateNames[] = htmlspecialchars($candidate['name']);
                  $voteCounts[] = $candidate['total_votes'];
              ?>
                <tr>
                  <td><?= htmlspecialchars($candidate['name']) ?></td>
                  <td><?= $candidate['total_votes'] ?></td>
                  <td><?= number_format($percentage, 2) ?>%</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <!-- Pie Chart -->
          <div class="text-center my-4">
            <canvas id="chart_<?= $election_id ?>" width="300" height="300"></canvas>
          </div>

          <script>
            const ctx<?= $election_id ?> = document.getElementById("chart_<?= $election_id ?>").getContext("2d");
            new Chart(ctx<?= $election_id ?>, {
              type: "pie",
              data: {
                labels: <?= json_encode($candidateNames) ?>,
                datasets: [{
                  label: "Votes",
                  data: <?= json_encode($voteCounts) ?>,
                  backgroundColor: [
                    "rgba(255, 99, 132, 0.7)",
                    "rgba(54, 162, 235, 0.7)",
                    "rgba(255, 206, 86, 0.7)",
                    "rgba(75, 192, 192, 0.7)",
                    "rgba(153, 102, 255, 0.7)",
                    "rgba(255, 159, 64, 0.7)"
                  ],
                  borderColor: "#fff",
                  borderWidth: 2
                }]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: { position: 'bottom' },
                  tooltip: { enabled: true }
                }
              }
            });
          </script>
            <hr class="my-4">
            <a href="dashboard.php" class="btn btn-secondary w-100"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        <?php else: ?>
          <p class="text-muted">No candidates or votes recorded.</p>
        <?php endif; ?>
      </div>
    </div>
    <?php endwhile; ?>
  </section>

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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>