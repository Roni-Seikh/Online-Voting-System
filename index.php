<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Online Voting System</title>
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
              <a class="nav-link active" href="index.php">Home</a>
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
              <a class="nav-link" href="login.php">Results</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#contact">Contact</a>
            </li>
          </ul>
        </div>

      </div>
    </nav>
  </header>

  <!-- Hero image section -->
  <section id="hero">
    <h4>Every vote counts</h4>
    <h2>Your vote is your power</h2>
    <h1>The power is in your hands</h1>
    <p>Democracy is not a spectator sport. Get out and vote!</p>

    <a href="login.php">
      <button>Vote now!</button>
    </a>
  </section>

  <!-- Images slidebar -->
  <section id="carousel-section" class="container my-5">
    <div id="votingCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#votingCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#votingCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#votingCarousel" data-bs-slide-to="2"></button>
        <button type="button" data-bs-target="#votingCarousel" data-bs-slide-to="3"></button>
      </div>
      <div class="carousel-inner rounded">
        <div class="carousel-item active">
          <img src="assets/vote-1.jpg" class="d-block w-100" alt="Vote 1">
        </div>
        <div class="carousel-item">
          <img src="assets/vote-2.jpg" class="d-block w-100" alt="Vote 2">
        </div>
        <div class="carousel-item">
          <img src="assets/vote-3.jpg" class="d-block w-100" alt="Vote 3">
        </div>
        <div class="carousel-item">
          <img src="assets/vote-4.jpg" class="d-block w-100" alt="Vote 4">
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#votingCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#votingCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </section>

  <!-- Platform highlights -->
  <section id="stats" class="text-center py-5" style="background-color: #E3E6F3;">
    <div class="container">
      <h2 class="mb-4" style="color: #088178;">Platform Highlights</h2>
      <div class="row">
        <div class="col-md-4">
          <h3 class="counter" data-target="5000">0</h3>
          <p>Registered Voters</p>
        </div>
        <div class="col-md-4">
          <h3 class="counter" data-target="1200">0</h3>
          <p>Votes Cast</p>
        </div>
        <div class="col-md-4">
          <h3 class="counter" data-target="50">0</h3>
          <p>Contests Held</p>
        </div>
      </div>
    </div>
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

  <!-- Javascript for logic -->
  <script>
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
      counter.innerText = '0';
      const updateCounter = () => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText;
        const increment = target / 200;

        if (count < target) {
          counter.innerText = Math.ceil(count + increment);
          setTimeout(updateCounter, 10);
        } else {
          counter.innerText = target;
        }
      };
      updateCounter();
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>