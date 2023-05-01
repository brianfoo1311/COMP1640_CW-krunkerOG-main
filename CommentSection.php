<?php
session_start();
require("krunkerideaconn.php");
if (!isset($_SESSION["username"]) && !isset($_SESSION["userid"])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
} else {
    $id = $_GET['id'];
    $count_sql = "SELECT idea_tbl.ViewCount FROM idea_tbl WHERE IdeaId= $id";
    $count_result = mysqli_query($dbconn, $count_sql);
    $viewcount_row = mysqli_fetch_assoc($count_result);
    $Viewcount = $viewcount_row['ViewCount'];
    $Viewcount++;
    $sqlCount = "UPDATE idea_tbl SET ViewCount =$Viewcount WHERE IdeaId = $id";
    $resultCount = mysqli_query($dbconn, $sqlCount);
}
$dbconn = mysqli_connect("localhost", "root", "", "krunkerideadb");
$user_id = $_SESSION["userid"];
$id = $_GET['id']; // get ideaID

$sql = "SELECT idea_tbl.IdeaId, idea_tbl.IdeaTitle, category_tbl.CategoryTitle, user_tbl.Username, idea_tbl.DatePost, idea_tbl.IdeaDescription, idea_tbl.IdeaAnonymous from idea_tbl  
INNER JOIN user_tbl ON idea_tbl.UserId =user_tbl.UserId 
INNER JOIN category_tbl ON idea_tbl.CategoryId= category_tbl.CategoryId 
WHERE IdeaId=$id";
$result = mysqli_query($dbconn, $sql);

$error = "";
if (isset($_POST["submit_comment_post"])) {

    $user_id = $_SESSION["userid"];
    $usercomment = $_POST["CommentDetails"];
    $comment = $usercomment;
    $anonymous = isset($_POST["anonymous"]);

    $getClosure = "SELECT DateFinal from user_tbl WHERE UserId = $user_id";
    $finalclosureResult = mysqli_query($dbconn, $getClosure);
    $closureRow = mysqli_fetch_assoc($finalclosureResult);
    $finalclosureDate = $closureRow['DateFinal'];
    if (date('Y-m-d') >= $finalclosureDate) {
        echo "<script>alert('Sorry, comments are temporarily closed.')</script>";
    } else {
        try {
            if (!empty($comment)) {
                mysqli_query($dbconn, "INSERT INTO comment_tbl (UserId, CommentDetails, CommentAnonymous, IdeaId) 
                              VALUES ('$user_id','$comment','$anonymous', '$id')");
                              
                    // find author email
                    $sqlID =  mysqli_query($dbconn, "SELECT UserId from idea_tbl WHERE IdeaId = $id");
                    $strID = $sqlID->fetch_array()[0] ?? ''; //get single value n convert to string 
                    $sqlauthorEmail = mysqli_query($dbconn, "SELECT UserEmail from user_tbl WHERE UserId = $strID");
                    $strresultEmail = $sqlauthorEmail->fetch_array()[0] ?? ''; //conver email to single value
                    $sqlUsername = mysqli_query($dbconn, "SELECT Username from user_tbl WHERE UserId= $user_id");
                    $strUserName = $sqlUsername->fetch_array()[0] ?? '';

                    $to      = $strresultEmail;
                    $subject = 'New comment on your idea';
                    $message = "Hello, \r\n\n";
                    $message .= "You got a new message from Krunker Idea Portal : \r\n\n";
                    $message .= "$strUserName commented on your idea. \r\n\n\n";
                    $message .= "Warm regards, \r\n\n";
                    $message .= "Krunker Idea Portal \r\n";
                    $headers = 'From:caleb@gmail.com' . "\r\n" .
                        'Reply-To: krunkerog6@gmail.com' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();

                    mail($to, $subject, $message, $headers);

                header("Location:CommentSection.php?id=" . $id);
                exit();
            } else {
                $error = "Don't leave your comment empty.";
            }
        } catch (Exception) {
            $error = "⚠️ Something wrong with your input ⚠️";
        }
    }
}
$show = "SELECT user_tbl.UserId, comment_tbl.CommentId, comment_tbl.IdeaId, comment_tbl.CommentDetails, comment_tbl.DateComment, comment_tbl.CommentAnonymous, comment_tbl.comment_hidden, user_tbl.Username from comment_tbl
INNER JOIN user_tbl ON comment_tbl.UserId = user_tbl.UserId
WHERE IdeaId=$id AND comment_hidden=0";
$showComment = mysqli_query($dbconn, $show);
?>

<?php
  $user_id = $_SESSION["userid"];
  $select_sql = "SELECT * FROM user_tbl WHERE UserId = $user_id";
  $result_User = mysqli_query($dbconn, $select_sql);  
  $row_User = mysqli_fetch_assoc($result_User);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Krunker Idea Portal 2023</title>
    <meta content="" name="description">
    <meta content="" name="keywords">


    <!-- Font Awesome Kit CSS -->
    <link rel="stylesheet" href="https://kit.fontawesome.com/bb8f73d07f.css" crossorigin="anonymous">
    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        .pagination {
            text-align: center;
            display: inline;
            letter-spacing: 10px;
        }
    </style>
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">Krunker Idea Portal</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo htmlentities($row_User['Username']) ?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo htmlentities($row_User['Username']) ?></h6>
              <span><?php echo $row_User['UserRoleName'] ?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
                <a class="dropdown-item d-flex align-items-center" href="staff_profile.php">
                  <i class="bi bi-person"></i>
                  <span>My Profile</span>
                </a>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

        <ul class="sidebar-nav" id="sidebar-nav">

            <li class="nav-item">
              <a class="nav-link collapsed" href="index.php">
                  <i class="bi bi-grid"></i><span>Idea</span>
              </a>
            </li><!-- End Idea Nav -->

            <?php
              echo '<li class="nav-item">';
              echo '<a href="EditIdea.php?id=' .$user_id.'" class="nav-link collapsed" data-bs-target="#statistics-nav;">';
              echo '<i class="bi bi-pencil"></i><span>Edit Idea</span>';
              echo '</a>';
              echo '</li>';
            ?>

            <?php
                if($_SESSION['role'] == "Admin"){ //staff cannot see this
                  echo'<li class="nav-heading">Pages</li>';

                  echo'<li class="nav-item">';
                      echo '<a class="nav-link collapsed" href="ManageUser_admin.php">';
                          echo '<i class="bi bi-people"></i>';
                          echo '<span>Manage User</span>';
                      echo '</a>';
                  echo '</li><!-- End Manage User Page Nav -->';

                  echo '<li class="nav-item">';
                      echo '<a class="nav-link collapsed" href="ManageIdea_admin.php">';
                          echo '<i class="bi bi-chat-left-text"></i>';
                          echo '<span>Manage Idea</span>';
                      echo '</a>';
                  echo '</li><!-- End Manage Idea Page Nav -->';
                  
                  echo '<li class="nav-item">';
                  echo '<a class="nav-link collapsed" href="closure_date.php">';
                  echo '<i class="bi bi-calendar4-week"></i><span>Closure Dates</span>';
                  echo '</a>';
                  echo '</li>';
                  
                  echo '<li class="nav-item">';
                    echo '<a class="nav-link collapsed" href="ManageComment_admin.php">';
                      echo '<i class="bi bi-chat-left-text"></i>';
                      echo '<span>Manage Comment</span>';
                    echo '</a>';
                  echo '</li>';
                }
            ?>

            <?php
              if($_SESSION['role'] == "QA Coordinator"){
                echo'<li class="nav-heading">Pages</li>';

                echo'<li class="nav-item">';
                    echo '<a class="nav-link collapsed" href="staff_details.php">';
                        echo '<i class="bi bi-person-check"></i>';
                        echo '<span>Staff Details</span>';
                    echo '</a>';
                echo '</li><!-- End Staff Details Nav -->';
              }
            ?>

            <?php
              if($_SESSION['role'] == "QA Manager"){
                echo'<li class="nav-heading">Pages</li>';

                echo'<li class="nav-item">';
                    echo '<a class="nav-link collapsed" href="ManageCategory_manager.php">';
                        echo '<i class="bi bi-grid"></i>';
                        echo '<span>Add a new Category</span>';
                    echo '</a>';
                echo '</li><!-- End Category Page Nav -->';

                echo'<li class="nav-item">';
                    echo '<a class="nav-link collapsed" href="report_manager.php">';
                        echo '<i class="bi bi-bar-chart-line"></i>';
                        echo '<span>Reports</span>';
                    echo '</a>';
                echo '</li><!-- End Report Page Nav -->';
              }
            ?>
            
        </ul>

    </aside><!-- End Sidebar-->

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Idea</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index_admin.php">Idea</a></li>
                    <!-- <li class="breadcrumb-item"><a href="CommentSection.php">Comments</a></li> -->
                </ol>
            </nav>
        </div><!-- End Page Title -->
        <div class="container">
            <?php
            if ($error){
                echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                '.$error.'
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>
                </div>';
            }
            ?>
        </div>
        <section class="section dashboard">
            <!-- <div class="container">
                <div class="row justify-content-md-center">
                    <div class="col-lg-10">
                        <div class="card">
                            <div class="card-body">
                                    <div class="media-mt-10">
                                        <img class="align-self-start mr-3" src="assets/img/profile-img-64x64.jpg" alt="Generic placeholder image" />
                                        <div class="media-body">
                                            <h5 class="mt-0">User</h5>
                                            <p class="" float="right">#Facilites</p>
                                        </div>
                                    </div>
                                <p>Cleaness</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <div class="container">
                <?php

                while ($row = mysqli_fetch_assoc($result)) {


                ?>
                    <div class="row">
                        <div class="col-12 col-md-8 col-lg-9 col-xl-9">
                            <div class="card border-0 mb-4">
                                <div class="card-body">
                                    <div class="row align-items-center mb-3">
                                        <div class="col">
                                            <?= '<h1 class="card-title">' . htmlentities($row['IdeaTitle']) . '</h1>'; ?>
                                        </div>
                                        <div class="col-auto">
                                            <a href="index.php"><i class="fa-regular fa-x fa-2x"></i></a>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-3">
                                        <div class="col-auto">
                                            <figure class="rounded pill">
                                                <img src="assets/img/profile-img-64x64.jpg" alt="">
                                            </figure>
                                        </div>
                                        <div class="col px-0">
                                            <p class="small text-secondary mb-0">Posted by</p>
                                            <?= '<h5 class="card-author">' . htmlentities($row['Username']) . '</h5>'; ?>
                                            <!-- <p class="mb-0">User <small class="text-secondary">1 hr ago</small></p> -->
                                        </div>
                                        <div class="col-auto text-end">
                                            <p class="small text-secondary mb-0">Posted at</p>
                                            <?= '<h5 class="card-author">' . $row['DatePost'] . '</h5>'; ?>
                                        </div>
                                    </div>
                                    <?= '<p class="card-text">' . htmlentities($row['IdeaDescription']) . '</p>'; ?>
                                    <!-- Gallery -->
                                    <?php
                                    $ideaid = $row['IdeaId'];
                                    $imageidea_query = "SELECT IdeaImage FROM ideamedia_tbl WHERE IdeaId=$ideaid";
                                    $imageidea_result = mysqli_query($dbconn, $imageidea_query);
                                    $imageidea_count = mysqli_num_rows($imageidea_result);
                                    if ($imageidea_count > 0) {
                                        echo '<section class="pb-4">';
                                        echo '    <div class="bg-white border rounded-5">';
                                        echo '        <section class="p-4 d-flex justify-content-center text-center w-100">';
                                        echo '            <div class="lightbox" data-mdb-zoom-level="0.25" data-id="lightbox-8e0in48hs">';
                                        echo '                <div class="row">';
                                        while ($imageidea_row = mysqli_fetch_assoc($imageidea_result)) {
                                            $imageidea_path = '' . $imageidea_row['IdeaImage'];
                                            if (file_exists($imageidea_path)) {
                                                echo '   <div class="col-md-6 mb-4">';
                                                echo '         <img src="' . $imageidea_path . '"  alt="idea image" class="shadow-1-strong rounded mb-4" style="width: 150px; height: 150px; object-fit: contain;">';
                                                echo '   </div>';
                                            }
                                        }
                                        echo '               </div>';
                                        echo '            </div>';
                                        echo '        </section>';
                                        echo '    </div>';
                                        echo '</section>';
                                    }

                                    ?>
                                    <?= '<h5 class="btn btn-primary rounded-pill">' . $row['CategoryTitle'] . '</h5>'; ?>

                                    <form action="" method="post">
                                        <!-- Gallery -->
                                        <p>
                                            <!-- <span class="btn btn-primary rounded-pill">Support</span> -->

                                            <hr>
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" id="anonymous" name="anonymous" class="form-check-input" value="1">
                                            <label for="anonymous" class="form-check-label">Comment anonymously</label>
                                        </div>
                                        </p>
                                </div>


                                <div class="card-footer">
                                    <div class="input-group">
                                        <input type="text" required name="CommentDetails" class="form-control border" placeholder="Your comment here...">
                                        <button class="btn btn-light border" type="submit" name="submit_comment_post">Comment</button>

                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>

                    <?php
                }
                    ?>

                    <h5 class="title">Comments</h5>
                    <?php
                    while ($shoCom = mysqli_fetch_assoc($showComment)) {
                        if ($shoCom['comment_hidden'] == 0) {
                    ?>
                            <div class="col-12 col-md-8 col-lg-9 col-xl-9">
                                <div class="card border-0 mb-4">
                                    <div class="card-body">
                                        <br>
                                        <div class="row align-items-center mb-3">
                                            <div class="col-auto">
                                                <figure class="rounded pill">
                                                    <img src="assets/img/profile-img-64x64.jpg" alt="">
                                                </figure>
                                            </div>
                                            <div class="col px-0">
                                                <p class="small text-secondary mb-0">Commented by</p>
                                                <?php if ($shoCom['CommentAnonymous'] == 0) {

                                                    echo '<p class="mb-0">' . htmlentities($shoCom['Username']) . '</p>';
                                                } else if ($shoCom['CommentAnonymous'] == 1) {
                                                    echo '<p class="mb-0">Anonymous</h5>';
                                                }
                                                ?>

                                            </div>
                                            <div class="col-auto text-end">
                                                <?php
                                                $commentid =  $shoCom['CommentId'];
                                                if ($shoCom['UserId'] == $user_id) {
                                                ?>
                                                    <form action="" method="post">
                                                        <button class="bg-transparent border-0" type="submit" name="delete_comment">

                                                            <i class="fa-regular fa-x fa-1x btn btn-primary position-absolute top-0 end-0 ms-3 " style="margin:10px 15px 0 0;"></i>
                                                        </button>
                                                        <?= '<input type="hidden" name="usercommentid" value="' . $commentid . '"> ' ?>
                                                    </form>
                                                    <br>
                                                <?php
                                                }
                                                ?>
                                                <p class="small text-secondary mb-0">Commented on</p>
                                                <?= '<p class="mb-0">' . $shoCom['DateComment'] . '</p>'; ?>
                                            </div>
                                        </div>
                                        <?= '<p class="mb-0" style="margin-left:10px;">' . htmlentities($shoCom['CommentDetails']) . '</p>'; ?>
                                    </div>
                                </div>
                            </div>

                    <?php
                        }
                    }

                    if (isset($_POST["delete_comment"])) {
                        $usercommentid = $_POST['usercommentid'];
                        $sql_updatelikedislike = "UPDATE comment_tbl SET comment_hidden = 1
                                                WHERE CommentId= $usercommentid;";
                        mysqli_query($dbconn, $sql_updatelikedislike);
                        echo "<script>window.location.href='CommentSection.php?id=" . $id . "';</script>";
                        exit();
                    }

                    ?>

        </section>

    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span>Krunker Idea Portal 2023</span></strong>. All Rights Reserved
        </div>
        <div class="credits">

        </div>
    </footer><!-- End Footer -->




    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/chart.js/chart.umd.js"></script>
    <script src="assets/vendor/echarts/echarts.min.js"></script>
    <script src="assets/vendor/quill/quill.min.js"></script>
    <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>
    <!-- Font Awesome Kit script -->
    <script src="https://kit.fontawesome.com/bb8f73d07f.js" crossorigin="anonymous"></script>

</body>

</html>