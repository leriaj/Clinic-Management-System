<?php
session_start();

date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Nurse') {
    echo "<script>alert('You do not have permission to access this page.'); window.location.href = '../login.php';</script>";
    exit();
}

$conn = new mysqli("localhost", "root", "", "cms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_comment'])) {
  $user_id = $_SESSION['user_id']; 
  $user_name = $conn->real_escape_string($_SESSION['Uusername']);
  $announcement_id = $conn->real_escape_string($_POST['announcement_id']);
  $comment = $conn->real_escape_string($_POST['comment']);

  $insert_comment_query = "INSERT INTO comments (announcement_id, nurse_id, comment) VALUES ('$announcement_id', '$user_id', '$comment')";
  if (!$conn->query($insert_comment_query)) {
      die("Error inserting comment: " . $conn->error);
  }

  $announcement_query = "SELECT doctor_id, title FROM announcements WHERE id = '$announcement_id'";
  $announcement = $conn->query($announcement_query)->fetch_assoc();

  if ($announcement) {
      $title = $conn->real_escape_string($announcement['title']);
      $announcement_owner_id = $announcement['doctor_id'];

      $owner_query = "SELECT Uusername FROM users WHERE id = '$announcement_owner_id'";
      $owner_result = $conn->query($owner_query);
      $owner = $owner_result->fetch_assoc();
      $announcement_owner_name = $conn->real_escape_string($owner['Uusername']);

      $users_query = "SELECT id, Uusername FROM users WHERE id != '$user_id'";
      $users = $conn->query($users_query);

      while ($user = $users->fetch_assoc()) {
          $notification_user_id = $user['id'];
          $message = $conn->real_escape_string("$user_name commented on $announcement_owner_name's announcement: \"$title\"");

          $notification_query = "INSERT INTO notifications (user_id, announcement_id, message, type) 
                                 VALUES ('$notification_user_id', '$announcement_id', '$message', 'comment')";
          if (!$conn->query($notification_query)) {
              die("Error inserting notification: " . $conn->error);
          }
      }
  }

  $_SESSION['notification'] = "New comment posted!";
  header("Location: pd.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_announcement'])) {
    $announcement_id = $_POST['announcement_id'];
    $announcement = $conn->query("SELECT doctor_id, title FROM announcements WHERE id = '$announcement_id'")->fetch_assoc();
    $nurse_id = $announcement['doctor_id'];
    $title = $announcement['title'];

    $conn->query("UPDATE announcements SET is_approved = 1 WHERE id = '$announcement_id'");

    if ($nurse_id != $_SESSION['user_id']) {
        $conn->query("INSERT INTO notifications (user_id, announcement_id, message, type) VALUES ('$nurse_id', '$announcement_id', 'Your announcement \"$title\" has been approved!', 'approved')");
    }

    $_SESSION['notification'] = "Announcement approved!";
    header("Location: pd.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_announcement'])) {
    $user_id = $_SESSION['user_id']; 
    $user_name = $_SESSION['Uusername'];
    $role = $_SESSION['role']; 
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $is_approved = ($role == 'Doctor') ? 1 : 0;

    $conn->query("INSERT INTO announcements (doctor_id, title, content, is_approved) VALUES ('$user_id', '$title', '$content', '$is_approved')");
    $announcement_id = $conn->insert_id;

    if ($role == 'Nurse') {
        $doctors = $conn->query("SELECT id FROM users WHERE role = 'Doctor'");
        while ($doctor = $doctors->fetch_assoc()) {
            if ($doctor['id'] != $user_id) {
                $conn->query("INSERT INTO notifications (user_id, announcement_id, message, type) VALUES ('{$doctor['id']}', '$announcement_id', 'New announcement pending approval: $title', 'asking_to_post')");
            }
        }
        $_SESSION['notification'] = "Announcement submitted for approval!";
    } else {
        $conn->query("INSERT INTO notifications (user_id, announcement_id, message, type) VALUES ('$user_id', '$announcement_id', '$user_name posted a new announcement: $title', 'posted')");
        $_SESSION['notification'] = "New announcement posted!";
    }

    header("Location: pd.php");
    exit();
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $string = [
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

$announcements = $conn->query("
    SELECT announcements.*, users.Uusername AS user_name, users.role AS user_role 
    FROM announcements 
    JOIN users ON announcements.doctor_id = users.id 
    WHERE is_approved = 1
    ORDER BY announcements.created_at DESC
");

$notifications = $conn->query("
    SELECT notifications.*, announcements.title, announcements.content, users.Uusername AS user_name 
    FROM notifications 
    LEFT JOIN announcements ON notifications.announcement_id = announcements.id 
    LEFT JOIN users ON announcements.doctor_id = users.id 
    WHERE notifications.user_id = '{$_SESSION['user_id']}' 
    ORDER BY notifications.created_at DESC
");

if (!$notifications) {
    die("Error in query: " . $conn->error);
}

$pending_announcements = $conn->query("
    SELECT announcements.*, users.Uusername AS user_name 
    FROM announcements 
    JOIN users ON announcements.doctor_id = users.id 
    WHERE is_approved = 0
    ORDER BY announcements.created_at DESC
");

$filter_query = "
    SELECT announcements.*, users.Uusername AS user_name, users.role AS user_role 
    FROM announcements 
    JOIN users ON announcements.doctor_id = users.id 
    WHERE is_approved = 1
";

if (!empty($_GET['title'])) {
  $title = $conn->real_escape_string($_GET['title']);
  $filter_query .= " AND announcements.title LIKE '%$title%'";
}

if (!empty($_GET['search'])) {
  $search = $conn->real_escape_string($_GET['search']);
  $filter_query .= " AND (announcements.title LIKE '%$search%' OR users.Uusername LIKE '%$search%')";
}

if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
  $start_date = $conn->real_escape_string($_GET['start_date']);
  $end_date = $conn->real_escape_string($_GET['end_date']);
  $filter_query .= " AND DATE(announcements.created_at) BETWEEN '$start_date' AND '$end_date'";
}
$filter_query .= " ORDER BY announcements.created_at DESC";

$filtered_announcements = $conn->query($filter_query);

if (!$filtered_announcements) {
    die("Error in query: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quickcare | Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dd.css">
    <link rel="icon" href="../assets/img/logo.png">
        <style>
        /* di gumagana sa dd.css */
        .notification-dropdown {
            max-height: 350px;   
            overflow-y: auto;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 350px;
            position: absolute;
            right: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            padding: 10px 0;
            display: none;
        }
        .notification-dropdown.open {
            display: block;
        }
        .notification-dropdown ul {
            margin: 0;
            padding: 0 10px;
            list-style: none;
        }
        .notification-dropdown li {
            margin-bottom: 10px;
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
                <li><a href="pd.php" class="nav-link">Home</a></li>
                <li><a href="pd.php#announcement" class="nav-link">Announcements</a></li>
                <li><a href="pdpatientslist.php" class="nav-link">Patients List</a></li>
                <li><a href="pdpprof.php" class="nav-link">Patient's Profile</a></li>
                <li><a href="pdbilling.php" class="nav-link">Payment</a></li>
                <li><a href="../assets/php/signout.php" class="nav-link">Signout</a></li>
            </ul>
        </nav>
    </header>
    <main>
      <section class="profile-navbar">
        <div class="profile-info">
            <h3>Hi, <?php echo $_SESSION['Uusername']; ?> (<?php echo $_SESSION['role']; ?>)</h3>
            <p>
                <?php
                $hour = date('H');
                if ($hour < 12) {
                    echo "Good Morning!";
                } elseif ($hour < 18) {
                    echo "Good Afternoon!";
                } else {
                    echo "Good Evening!";
                }
                ?>
            </p>
        </div>
        <div class="profile-actions">
          <button id="notification-toggle" class="profile-link" style="border: 2px solid #75f15f; border-radius: 5px; background-color: #75f15f; color: white; margin-right: 7 0px;">Notifications</button>
            <div id="notification-dropdown" class="notification-dropdown">
                <ul>
                <?php if ($notifications->num_rows > 0): ?>
                    <?php while ($notification = $notifications->fetch_assoc()): ?>
                    <li>
                        <a href="#announcement-<?php echo $notification['announcement_id']; ?>" class="notification-link">
                            <strong>
                                <?php
                                switch ($notification['type']) {
                                    case 'posted': echo "Posted: "; break;
                                    case 'comment': echo "Commented: "; break;
                                    case 'asking_to_post': echo "Asking to Post: "; break;
                                    case 'approved': echo "Approved: "; break;
                                }
                                ?>
                            </strong>
                            <small><?php echo time_elapsed_string($notification['created_at']); ?></small>
                            <div>
                                <strong><?php echo $notification['title']; ?></strong>
                                <p><?php echo $notification['message']; ?></p>
                            </div>
                        </a>
                    </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li><p>No new notifications</p></li>
                <?php endif; ?>
                </ul>
            </div>
        </div>
      </section>
        <div class="container">
          <form id="filter-form" method="GET" action="">
            <h2>Filter Announcements</h2>
            <input type="text" name="search" placeholder="Search by Title or Announcer Name" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
            <button type="button" id="filter-button">Filter</button>
            </form>
            <div id="filter-overlay" class="overlay">
                <div class="overlay-content">
                    <button id="close-overlay">Close</button>
                    <div id="filter-results">
                        <?php if ($filtered_announcements && $filtered_announcements->num_rows > 0): ?>
                            <?php while ($announcement = $filtered_announcements->fetch_assoc()): ?>
                                <?php if (!empty($announcement['id'])): ?>
                                    <div class="announcement">
                                        <a href="#announcement-<?php echo htmlspecialchars($announcement['id']); ?>" class="filter-result-link">
                                            <h3><?php echo htmlspecialchars($announcement['user_name']); ?> <small>• <?php echo htmlspecialchars($announcement['user_role']); ?></small></h3>
                                            <h4><?php echo htmlspecialchars($announcement['title']); ?></h4>
                                            <p><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                            <small>Posted on <?php echo htmlspecialchars($announcement['created_at']); ?></small>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No announcements found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
          <hr>
          <div class="container">
              <?php if ($_SESSION['role'] == 'Doctor' || $_SESSION['role'] == 'Nurse'): ?>
                  <form action="" method="post">
                      <h2>Post an Announcement</h2>
                      <input type="text" name="title" placeholder="Title" required>
                      <textarea name="content" placeholder="Write your announcement here..." rows="4" required></textarea>
                      <button type="submit" name="post_announcement">Post</button>
                  </form>
              <?php endif; ?>
          </div>
          <hr id="announcement">
          <?php while ($announcement = $announcements->fetch_assoc()): ?>
            <div class="announcement" id="announcement-<?php echo $announcement['id']; ?>">
                <h3><?php echo $announcement['user_name']; ?><small>•<?php echo $announcement['user_role']; ?></small></h3> 
                <h4><?php echo $announcement['title']; ?></h4>
                <p><?php echo $announcement['content']; ?></p>
                <small>
                    Posted by <?php echo $announcement['user_name']; ?> (<?php echo $announcement['user_role']; ?>) 
                    on <?php echo $announcement['created_at']; ?>
                </small>
                <h4>Comments</h4>
                <form action="" method="post">
                    <textarea name="comment" placeholder="Write a comment..." rows="2" required></textarea>
                    <input type="hidden" name="announcement_id" value="<?php echo $announcement['id']; ?>">
                    <button type="submit" name="post_comment">Comment</button>
                </form>
                
                <?php
                $announcement_id = $announcement['id']; 
                $comments = $conn->query("
                    SELECT comments.*, users.Uusername AS user_name, users.role AS user_role 
                    FROM comments 
                    JOIN users ON comments.nurse_id = users.id 
                    WHERE comments.announcement_id = $announcement_id 
                    ORDER BY comments.created_at ASC
                ");
                ?>
                
                    <?php while ($comment = $comments->fetch_assoc()): ?>
                        <div class="comment">
                            <p><?php echo $comment['comment']; ?></p>
                            <small>
                                Commented by <?php echo $comment['user_name']; ?> (<?php echo $comment['user_role']; ?>) 
                                on <?php echo $comment['created_at']; ?>
                            </small>
                        </div>
                    <?php endwhile; ?>
                    <small><a href="pd.php" class="go-to-top">Go Back to Top</a></small>
                </div>
          <?php endwhile; ?>
        </div>
    </main>
    <script src="../assets/js/mobile-navbar.js"></script>
    <script src="../assets/js/notification.js"></script>  
    <script src="../assets/js/filter.js"></script>
</body>
</html>