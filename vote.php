<?php
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'voter') {
    die("Only voters can vote");
}

$user_id = $_SESSION['user']['id'];
$message = '';

// Process vote
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $election_id = $_POST['election_id'];
    $candidate_id = $_POST['candidate_id'];

    $check = $conn->query("SELECT * FROM votes WHERE user_id=$user_id AND election_id=$election_id");
    if ($check->num_rows > 0) {
        $message = "<div class='alert alert-warning'>You have already voted in this election.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO votes (user_id, candidate_id, election_id, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iii", $user_id, $candidate_id, $election_id);
        $stmt->execute();
        $message = "<div class='alert alert-success'>Vote successfully cast!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vote - Online Voting System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="stylesheet/styles.css"/>
  <link rel="icon" type="image/png" href="assets/logo.png">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var voteForms = document.querySelectorAll('form[data-confirm]');
    voteForms.forEach(form => {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        modal.show();

        document.getElementById('confirmSubmitBtn').onclick = () => {
          form.submit();
        };
      });
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });
  });
  </script>
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

  <!-- Vote Banner -->
  <section class="container my-4 text-center">
    <img src="assets/vote-1.jpg" alt="Vote Now" class="img-fluid rounded shadow" style="max-height: 500px;">
  </section>

  <!-- Voting Section -->
  <div class="container my-5">
    <h2 class="mb-4 text-center">Cast Your Vote</h2>
    <a href="dashboard.php" class="btn btn-secondary mt-3 w-100"><i class="bi bi-arrow-left-circle"></i> Back to Dashboard</a>
    <?= $message ?>

    <?php
    $elections = $conn->query("SELECT * FROM elections");
    while ($election = $elections->fetch_assoc()):
        $election_id = $election['id'];
        $check = $conn->query("SELECT * FROM votes WHERE user_id=$user_id AND election_id=$election_id");
    ?>
      <div class="card mb-4 shadow">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><?= htmlspecialchars($election['title']) ?></h5>
        </div>
        <div class="card-body">
          <?php if (!empty($election['description'])): ?>
            <p class="text-muted"><?= htmlspecialchars($election['description']) ?></p>
          <?php endif; ?>

          <?php if ($check->num_rows > 0): ?>
            <div class="alert alert-info">You have already voted in this election.</div>
            <?php
            $vote_time_result = $conn->query("SELECT created_at FROM votes WHERE user_id=$user_id AND election_id=$election_id");
            if ($vote_time_result && $vote_time_result->num_rows > 0) {
                $vote_time_row = $vote_time_result->fetch_assoc();
                echo "<small class='text-muted'>Voted on: " . date('F j, Y, g:i a', strtotime($vote_time_row['created_at'])) . "</small>";
            }

            $candidate_votes = $conn->query("SELECT candidate_id, COUNT(*) as count FROM votes WHERE election_id=$election_id GROUP BY candidate_id");
            $votes_map = [];
            while ($row = $candidate_votes->fetch_assoc()) {
                $votes_map[$row['candidate_id']] = $row['count'];
            }

            $candidates = $conn->query("SELECT * FROM candidates WHERE election_id=$election_id");
            echo "<ul class='list-group mt-3'>";
            while ($cand = $candidates->fetch_assoc()) {
                $count = $votes_map[$cand['id']] ?? 0;
                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                echo htmlspecialchars($cand['name']) . " (" . htmlspecialchars($cand['party']) . ")";
                echo "<span class='badge bg-primary rounded-pill'>$count votes</span>";
                echo "</li>";
            }
            echo "</ul>";
            ?>
          <?php else: ?>
            <form method="POST" class="px-2" data-confirm>
              <input type="hidden" name="election_id" value="<?= $election_id ?>">
              <?php
                $candidates = $conn->query("SELECT * FROM candidates WHERE election_id=$election_id");
                while ($cand = $candidates->fetch_assoc()):
              ?>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="radio" name="candidate_id" id="cand<?= $cand['id'] ?>" value="<?= $cand['id'] ?>" required>
                  <label class="form-check-label" for="cand<?= $cand['id'] ?>">
                    <?= htmlspecialchars($cand['name']) ?> (<?= htmlspecialchars($cand['party']) ?>)
                  </label>
                </div>
              <?php endwhile; ?>
              <button type="submit" class="btn btn-success mt-3 w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="Make sure you've selected the right candidate">
                <i class="bi bi-check-circle"></i> Submit Vote
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
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

  <!-- Confirmation Modal -->
  <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmModalLabel">Confirm Your Vote</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to submit your vote? This action cannot be undone.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" id="confirmSubmitBtn" class="btn btn-success">Yes, Submit</button>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
