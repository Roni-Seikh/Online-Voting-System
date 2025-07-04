<?php
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Unauthorized access");
}

$user = $_SESSION['user'];
$elections = $conn->query("SELECT * FROM elections");
$message = "";

// Handle POST request to add candidate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_candidate'])) {
    $name = trim($_POST['name']);
    $party = trim($_POST['party']);
    $election_id = intval($_POST['election_id']);

    if ($name && $party && $election_id) {
        $stmt = $conn->prepare("INSERT INTO candidates (election_id, name, party) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $election_id, $name, $party);

        if ($stmt->execute()) {
            $message = "Candidate added successfully!";
        } else {
            $message = "Error adding candidate: " . $stmt->error;
        }
    } else {
        $message = "All fields are required.";
    }
}

// Fetch existing candidates
$candidates = $conn->query("
    SELECT c.id, c.name, c.party, e.title AS election_title
    FROM candidates c
    JOIN elections e ON c.election_id = e.id
    ORDER BY c.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Add Candidate - Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="stylesheet/styles.css">
  <link rel="icon" type="image/png" href="assets/logo.png">
  <style>
    .toast-container {
      position: fixed;
      top: 1rem;
      right: 1rem;
      z-index: 1055;
    }
  </style>
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

  <!-- Add Candidate Section -->
  <div class="container py-5">
    <div class="row">
      <div class="col-lg-6 mx-auto">
        <div class="card shadow border-0">
          <div class="card-body">
            <h4 class="mb-4 text-center"><i class="bi bi-person-plus"></i> Add New Candidate</h4>
            <form method="POST">
              <input type="hidden" name="add_candidate" value="1">
              <div class="mb-3">
                <label class="form-label">Candidate Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Party</label>
                <input type="text" name="party" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Select Election</label>
                <select name="election_id" class="form-select" required>
                  <option value="">-- Select Election --</option>
                  <?php while ($e = $elections->fetch_assoc()): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['title']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="mb-3">
              <a href="dashboard.php" class="btn btn-secondary w-100">
                  <i class="bi bi-arrow-left"></i> Back to Dashboard
              </a>
              </div>

              <button type="submit" class="btn btn-success w-100">
              <i class="bi bi-plus-circle"></i> Add Candidate
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Existing Candidates Table -->
    <div class="row mt-5">
      <div class="col-12">
        <h4 class="text-center mb-3"><i class="bi bi-list-task"></i> Existing Candidates</h4>
        <div class="table-responsive">
          <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Party</th>
                <th>Election</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($candidates && $candidates->num_rows > 0): ?>
                <?php $sn = 1; while ($row = $candidates->fetch_assoc()): ?>
                  <tr>
                    <td><?= $sn++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['party']) ?></td>
                    <td><?= htmlspecialchars($row['election_title']) ?></td>
                    <td>
                      <a href="edit_candidate.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                      </a>
                      <button type="button" class="btn btn-sm btn-danger" 
                          data-bs-toggle="modal" 
                          data-bs-target="#deleteModal" 
                          data-id="<?= $row['id'] ?>">
                          <i class="bi bi-trash"></i> Delete
                      </button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center">No candidates found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" action="delete_candidate.php" class="modal-content">
        <input type="hidden" name="id" id="delete_id">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this candidate?
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Yes, Delete</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast for Feedback -->
  <?php if (!empty($message)): ?>
    <div class="toast-container">
      <div class="toast align-items-center text-white bg-primary show" role="alert">
        <div class="d-flex">
          <div class="toast-body">
            <?= htmlspecialchars($message) ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Contact -->
  <section id="contact" class="container py-5">
    <h2>Contact Us</h2>
    <form action="send_contact.php" method="post">
      <div class="mb-3">
        <label class="form-label">Name:</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email:</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Message:</label>
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
<script>
  const deleteModal = document.getElementById('deleteModal');
  deleteModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const candidateId = button.getAttribute('data-id');
    const input = deleteModal.querySelector('#delete_id');
    input.value = candidateId;
  });
</script>
</body>
</html>