<?php 
date_default_timezone_set('Asia/Manila');
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cms";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$conn->close();

$feedbacks = [];
$filePath = '../assets/txt/feedbacks.txt';
if (file_exists($filePath)) {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $feedback = json_decode($line, true);
        if ($feedback) {
            $feedbacks[] = $feedback;
        }
    }
    $feedbacks = array_reverse($feedbacks);
} else {
    echo "<script>console.error('Feedback file not found: $filePath');</script>";
}

$filteredFeedbacks = $feedbacks;
if (isset($_GET['rating']) && $_GET['rating'] != 0) {
    $ratingFilter = intval($_GET['rating']);
    if ($ratingFilter >= 1 && $ratingFilter <= 5) {
        $filteredFeedbacks = array_filter($feedbacks, function($feedback) use ($ratingFilter) {
            return $feedback['rating'] == $ratingFilter;
        });
    }
}

$totalRatings = count($feedbacks);
$ratingSum = 0;

foreach ($feedbacks as $feedback) {
    $ratingSum += $feedback['rating'];
}

if (isset($_GET['start_date']) || isset($_GET['end_date'])) {
  $startDate = isset($_GET['start_date']) ? strtotime($_GET['start_date'] . ' 00:00:00') : null;
  $endDate = isset($_GET['end_date']) ? strtotime($_GET['end_date'] . ' 23:59:59') : null;

  $filteredFeedbacks = array_filter($filteredFeedbacks, function ($feedback) use ($startDate, $endDate) {
      $timestamp = $feedback['timestamp'];
      if ($startDate && $timestamp < $startDate) {
          return false;
      }
      if ($endDate && $timestamp > $endDate) {
          return false;
      }
      return true;
  });
}

$averageRating = $totalRatings > 0 ? $ratingSum / $totalRatings : 0;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>QuickCare | Landing Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/main.css" />
    <link rel="icon" href="../assets/img/logo.png">
    <style>
      *{
        scroll-behavior: smooth;
      }
      .c1{
        text-align: center;
        margin-top: 5%
      }
      h1{
        color: black;
      }
    </style>
  </head>
  <body>
    <header>
      <nav>
          <a class="logo" href="#"><img src="../assets/img/ogol.png" style="width: 120px; height: 80px; margin-left: 20px;" alt="Logo"></a>
          <div class="mobile-menu">
              <div class="line1"></div>
              <div class="line2"></div>
              <div class="line3"></div>
          </div>
          <ul class="nav-list">
              <li><a href="index.php" class="nav-link">Home</a></li>
              <li><a href="#aboutus" class="nav-link">About Us</a></li>
              <li><a href="#conta" class="nav-link">Contacts</a></li>
              <li><a href="#faqs" class="nav-link">FAQs</a></li>
              <li><a href="#feedback" class="nav-link">Feedback</a></li>
              <li><a href="#team" class="nav-link">Team</a></li>
          </ul>
      </nav>
    </header>
    
    <main id="main-content">
      <section class="hero" id="aboutus">
        <div class="hero-content">
          <h1>We Offer Medical Health Services for All Ages</h1>
          <p>Providing quality healthcare services with modern facilities and expert staff.</p>
          <div class="hero-message">
            <p>Please take care of your health. If you feel unwell, try to see a doctor. <a href="appointment.php" class="btn">Admission</a> </p>
          </div>
        </div>
      </section>
      <section class="services">
        <h2>The Advantages of Being Healthy</h2>
        <div class="service-cards">
          <div class="card">
            <h3>Improved Quality of Life</h3>
            <p>Being healthy allows you to enjoy life to the fullest and engage in activities you love.</p>
          </div>
          <div class="card">
            <h3>Increased Energy Levels</h3>
            <p>Good health boosts your energy, helping you stay productive and active throughout the day.</p>
          </div>
          <div class="card">
            <h3>Reduced Risk of Illness</h3>
            <p>Maintaining a healthy lifestyle reduces the risk of chronic diseases and infections.</p>
          </div>
          <div class="card">
            <h3>Better Mental Health</h3>
            <p>Physical health positively impacts mental well-being, reducing stress and improving mood.</p>
          </div>
        </div>
      </section>
      <section id="contact">
        <h2 class="section-title" id="conta">Contact Us</h2>
          <div class="contact-container">
            <div class="contact-item">
              <h3>Phone</h3>
              <p>Call us at:</p>
              <a href="tel:+">+</a>
            </div>
            <div class="contact-item">
              <h3>Email</h3>
              <p>Send us an email:</p>
              <a href="mailto:@gmail.com">@gmail.com</a>
            </div>
            <div class="contact-item">
              <h3>Location</h3>
              <p>Philippines</p>
            </div>
            <div class="contact-item">
              <h3>Working Hours</h3>
              <p>Monday - Saturday:</p>
              <p>8:00 AM - 6:00 PM</p>
            </div>
          </div>          
      </section>
      <section class="faqs" id="faqs">
        <h2 class="section-title">Frequently Asked Questions</h2>
          <div class="faq-container">
            <div class="faq-item">
              <button class="faq-question">
                What services does the clinic offer?
                <span class="faq-icon">+</span>
              </button>
              <div class="faq-answer">
                <p>Our clinic provides general consultations, basic diagnostics, vaccinations, and minor treatments.</p>
              </div>
            </div>
            <div class="faq-item">
              <button class="faq-question">
                How can I book an appointment?
                <span class="faq-icon">+</span>
              </button>
              <div class="faq-answer">
                <p>You can book an appointment through our website or by calling our reception desk. <a href="#contact">contact</a></p>
              </div>
            </div>
            <div class="faq-item">
              <button class="faq-question">
                When clinic is open?
                <span class="faq-icon">+</span>
              </button>
              <div class="faq-answer">
                <p>Our clinic is open from 8:00 AM to 6:00 PM, Monday to Saturday.</p>
              </div>
            </div>
          </div>
      </section>      
      <section class="feedback" id="feedback">
        <h2 class="section-title">Feedback</h2>
        <div class="average-rating">
          <h3>Average Rating: <span id="average-rating"><?= round($averageRating, 1) ?></span> / 5</h3>
          <div id="average-stars" class="stars">
            <?php
            $fullStars = floor($averageRating); 
            $fraction = $averageRating - $fullStars; 
            $emptyStars = 5 - ceil($averageRating); 

            for ($i = 1; $i <= $fullStars; $i++) {
                echo '<span class="star full">★</span>';
            }

            if ($fraction > 0) {
                $percentage = $fraction * 100; 
                echo '<span class="star partial" style="--fill: ' . $percentage . '%;">★</span>';
            }

            for ($i = 1; $i <= $emptyStars; $i++) {
                echo '<span class="star empty">★</span>';
            }
            ?>
          </div>
        </div>
        <div class="feedback-form">
          <h3 class="form-title">Submit a Feedback</h3>
          <h3 class="form-title"> We would like to hear your thoughts and suggestion!</h4>
          <form method="POST" action="../assets/php/save_feedback.php">
            <input type="text" name="name" class="form-input" placeholder="Customer Name" required />
            <textarea name="comment" class="form-textarea" rows="3" placeholder="Comment" required></textarea>
            <div class="stars rate-stars">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <span onclick="setRating(<?= $i ?>)">★</span>
              <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="rating" value="5">
            <button type="submit" class="btn btn-submit">Submit</button>
          </form>
        </div>
        <div class="feedback-list">
          <h3 id="feedbacks" class="list-title">Feedbacks</h3>
          <div>
            <label for="rating-filter" class="filter-label">Filter by Rating:</label>
            <select id="rating-filter" class="filter-select" onchange="filterFeedback(this.value)">
              <option value="0">All Ratings</option>
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>" <?= isset($ratingFilter) && $ratingFilter == $i ? 'selected' : '' ?>><?= $i ?> Star(s)</option>
              <?php endfor; ?>
            </select>
            <label for="" class="filter-label">Filter by Date:</label>
            <label for="start-date" class="filter-label">Start Date:</label>
            <input type="date" id="start-date" class="filter-date" onchange="filterFeedbackByDate()">
            <label for="end-date" class="filter-label">End Date:</label>
            <input type="date" id="end-date" class="filter-date" onchange="filterFeedbackByDate()">
          </div>
          <div class="feedback-scroll-container">
            <?php if (!empty($filteredFeedbacks)): ?>
                <?php foreach ($filteredFeedbacks as $fb): ?>
                    <div class="feedback-item">
                        <div class="rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span style="color: <?= $i <= $fb['rating'] ? 'orange' : '#ccc' ?>;">★</span>
                            <?php endfor; ?>
                        </div>
                        <strong class="feedback-name"><?= htmlspecialchars($fb['name']) ?></strong><br/>
                        <p class="feedback-comment"><?= htmlspecialchars($fb['comment']) ?></p>
                        <small class="feedback-timestamp"><?= date('Y-m-d H:i', $fb['timestamp']) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No feedbacks available.</p>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <section class="team" id="team">
        <h2 class="section-title">Meet Our Team</h2>
        <div class="team-cards">
          <div class="team-card">
            <img src="../assets/img/team photos/member1.png" alt="" class="team-photo">
            <h3 class="team-name">1</h3>
            <p class="team-position">Full Stack Developer</p>
            <div class="team-socials">
              <a href="" target="_blank" class="team-social">
                <i class="fab fa-facebook"></i>
              </a>
              <a href="" target="_blank" class="team-social">
                <i class="fas fa-envelope"></i>
              </a>
              <a href="tel:" target="_blank" class="team-social">
                <i class="fas fa-phone-alt contact-icon"></i>
              </a>
            </div>
          </div>
          <div class="team-card">
            <img src="../assets/img/team photos/member2.png" alt="" class="team-photo">
            <h3 class="team-name">2</h3>
            <p class="team-position">Front End Developer</p>
            <div class="team-socials">
              <a href="" target="_blank" class="team-social">
                <i class="fab fa-facebook"></i>
              </a>
              <a href="" target="_blank" class="team-social">
                <i class="fas fa-envelope"></i>
              </a>
              <a href="tel:" target="_blank" class="team-social">
                <i class="fas fa-phone-alt contact-icon"></i>
              </a>
            </div>
          </div>
          <div class="team-card">
            <img src="../assets/img/team photos/member3.png" alt="" class="team-photo">
            <h3 class="team-name">3</h3>
            <p class="team-position">Back End Developer</p>
            <div class="team-socials">
              <a href="" target="_blank" class="team-social">
                <i class="fab fa-facebook"></i>
              </a>
              <a href="mailto: " target="_blank" class="team-social">
                <i class="fas fa-envelope"></i>
              </a>
              <a href="tel: " target="_blank" class="team-social">
                <i class="fas fa-phone-alt contact-icon"></i>
              </a>
            </div>
          </div>
          <div class="team-card">
            <img src="../assets/img/team photos/member4.png" alt="" class="team-photo">
            <h3 class="team-name">4</h3>
            <p class="team-position">UI/UX Designer</p>
            <div class="team-socials">
              <a href="" target="_blank" class="team-social">
                <i class="fab fa-facebook"></i>
              </a>
              <a href="mailto:" target="_blank" class="team-social">
                <i class="fas fa-envelope"></i>
              </a>
              <a href="tel:" target="_blank" class="team-social">
                <i class="fas fa-phone-alt contact-icon"></i>
              </a>
            </div>
          </div>
          <div class="team-card">
            <img src="../assets/img/team photos/member5.png" alt="" class="team-photo">
            <h3 class="team-name">5</h3>
            <p class="team-position">QA Engineer</p>
            <div class="team-socials">
              <a href="" target="_blank" class="team-social">
                <i class="fab fa-facebook"></i>
              </a>
              <a href="mailto:" target="_blank" class="team-social">
                <i class="fas fa-envelope"></i>
              </a>
              <a href="tel:" target="_blank" class="team-social">
                <i class="fas fa-phone-alt contact-icon"></i>
              </a>
            </div>
          </div>
        </div>
      </section>
    </main>
    <script src="../assets/js/animation.js"></script>
    <script src="../assets/js/mobile-navbar.js"></script>
    <script src="../assets/js/feedback.js"></script>
    <script src="../assets/js/faqs.js"></script>
  </body>
</html>