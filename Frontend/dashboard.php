<?php
session_start();

// Disable browser cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Check login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "tasko");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// CURRENT DASHBOARD PAGE


$page = $_GET['page'] ?? 'dashboard';

$allowedPages = [
    'dashboard',
    'add-user',
    'manage-users',
    'assign-tasks',
    'manage-tasks',
    'leave-requests',
    'reports'
];

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}


// Database
$conn = mysqli_connect("localhost", "root", "", "tasko");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
// Total Users
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$totalUsers = mysqli_fetch_assoc($result)['total'];

// Completed Tasks
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE status='Completed'");
$completedTasks = mysqli_fetch_assoc($result)['total'];

// In Progress Tasks
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE status='In Progress'");
$inProgressTasks = mysqli_fetch_assoc($result)['total'];

// Overdue Tasks
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tasks WHERE due_date < CURDATE() AND status!='Completed'");
$overdueTasks = mysqli_fetch_assoc($result)['total'];

// Pending Leave Requests
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM leave_requests WHERE status='Pending'");
$leaveRequests = mysqli_fetch_assoc($result)['total'];

// for Recent Tasks in dashboard
$query = "SELECT
            tasks.task_id,
            tasks.title,
            tasks.status,
            tasks.due_date,
            users.first_name,
            users.last_name
          FROM tasks
          INNER JOIN users
          ON tasks.user_id = users.user_id
          ORDER BY tasks.task_id DESC
          LIMIT 5";

$result = mysqli_query($conn,$query);

if(isset($_POST['assignTask'])){

    $title = mysqli_real_escape_string($conn,$_POST['title']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $user_id = $_POST['user_id'];
    $priority = $_POST['priority'];
    $start_date = $_POST['start_date'];
    $due_date = $_POST['due_date'];
    $category = $_POST['category'];

    $assigned_by = $_SESSION['admin_id'];

    $sql="INSERT INTO tasks
    (
        user_id,
        title,
        description,
        category,
        priority,
        start_date,
        due_date,
        assigned_by
    )

    VALUES
    (
        '$user_id',
        '$title',
        '$description',
        '$category',
        '$priority',
        '$start_date',
        '$due_date',
        '$assigned_by'
    )";
    
    if(mysqli_query($conn,$sql)){

    // Redirect after successful insert
    header("Location: ../Frontend/dashboard.php?task=success");
    exit();

    }else{
    echo mysqli_error($conn);
    }
}
//update-profile
if(isset($_POST['admin_id'])){

$id=$_POST['admin_id'];

$name=mysqli_real_escape_string($conn,$_POST['full_name']);

$email=mysqli_real_escape_string($conn,$_POST['email']);
$phone=mysqli_real_escape_string($conn,$_POST['phone']);

$password=trim($_POST['password']);

if($password==""){

$sql="UPDATE admins
SET
full_name='$name',
email='$email'
WHERE admin_id='$id'";

}else{

$sql="UPDATE admins
SET
full_name='$name',
email='$email',
password='$password'
WHERE admin_id='$id'";

}

if(mysqli_query($conn,$sql)){

header("Location:../Frontend/dashboard.php?profile=updated");

exit();

}

}
//change passsword
if(isset($_POST['changePassword'])){

    $admin_id = $_SESSION['admin_id'];

    $current = trim($_POST['current_password']);
    $new = trim($_POST['new_password']);
    $confirm = trim($_POST['confirm_password']);

    $sql = mysqli_query($conn,"SELECT * FROM admins WHERE admin_id='$admin_id'");
    $admin = mysqli_fetch_assoc($sql);

    if($current != $admin['password']){
        $message = "Current password is incorrect.";

    }
    elseif($new != $confirm){
        $message = "New password and Confirm password do not match.";

    }
    elseif(strlen($new)<6){
        $message = "Password must be at least 6 characters.";

    }
    else{
        mysqli_query($conn,"UPDATE admins
        SET password='$new'
        WHERE admin_id='$admin_id'");
        $message = "Password changed successfully.";
    }
}

// =====================================================
// PROFESSIONAL REPORTS
// =====================================================

// -----------------------------------------------------
// 1. TASK STATUS
// -----------------------------------------------------

$completedResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE status = 'Completed'"
);

$completedTasks =
    (int)mysqli_fetch_assoc($completedResult)['total'];


$inProgressResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE status = 'In Progress'"
);

$inProgressTasks =
    (int)mysqli_fetch_assoc($inProgressResult)['total'];


// Pending tasks that are NOT overdue
$pendingResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE status = 'Pending'
     AND (
         due_date IS NULL
         OR due_date >= CURDATE()
     )"
);

$pendingTasks =
    (int)mysqli_fetch_assoc($pendingResult)['total'];


// Overdue tasks
$overdueResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE due_date < CURDATE()
     AND status != 'Completed'"
);

$overdueTasks =
    (int)mysqli_fetch_assoc($overdueResult)['total'];


// -----------------------------------------------------
// 2. EMPLOYEE PERFORMANCE
// -----------------------------------------------------

$employeePerformanceQuery = "

    SELECT

        users.user_id,

        users.first_name,

        users.last_name,

        COUNT(tasks.task_id) AS assigned_tasks,

        SUM(
            CASE
                WHEN tasks.status = 'Completed'
                THEN 1
                ELSE 0
            END
        ) AS completed_tasks,

        SUM(
            CASE
                WHEN tasks.status = 'Pending'
                AND (
                    tasks.due_date IS NULL
                    OR tasks.due_date >= CURDATE()
                )
                THEN 1
                ELSE 0
            END
        ) AS pending_tasks,

        SUM(
            CASE
                WHEN tasks.due_date < CURDATE()
                AND tasks.status != 'Completed'
                THEN 1
                ELSE 0
            END
        ) AS overdue_tasks

    FROM users

    LEFT JOIN tasks
        ON users.user_id = tasks.user_id

    GROUP BY
        users.user_id,
        users.first_name,
        users.last_name

    ORDER BY completed_tasks DESC

";

$employeePerformance = mysqli_query(
    $conn,
    $employeePerformanceQuery
);

$employeeNames = [];
$employeeCompleted = [];

while ($employee = mysqli_fetch_assoc($employeePerformance)) {

    $employeeNames[] =
        $employee['first_name'] . ' ' . $employee['last_name'];

    $employeeCompleted[] =
        (int)$employee['completed_tasks'];
}

// -----------------------------------------------------
// 3. COMPLETED TASK TREND - LAST 7 DAYS
// -----------------------------------------------------

$trendQuery = "

    SELECT

        DATE(completed_at) AS completion_date,

        COUNT(*) AS completed_count

    FROM tasks

    WHERE status = 'Completed'

    AND completed_at >= DATE_SUB(
        CURDATE(),
        INTERVAL 6 DAY
    )

    GROUP BY DATE(completed_at)

    ORDER BY completion_date ASC

";

$trendResult = mysqli_query(
    $conn,
    $trendQuery
);


// Create arrays for the chart
$trendLabels = [];
$trendValues = [];

while ($trend = mysqli_fetch_assoc($trendResult)) {

    $trendLabels[] = date(
        "D",
        strtotime($trend['completion_date'])
    );

    $trendValues[] =
        (int)$trend['completed_count'];
}

// ==========================================
// ADMIN DASHBOARD STATISTICS
// ==========================================


// ------------------------------------------
// TOTAL USERS
// ------------------------------------------

$totalQuery = "
    SELECT COUNT(*) AS total
    FROM users
";

$totalResult = mysqli_query($conn, $totalQuery);

$totalUsers = 0;

if ($totalResult) {
    $totalData = mysqli_fetch_assoc($totalResult);
    $totalUsers = $totalData['total'];
}


// ------------------------------------------
// ACTIVE USERS
// ------------------------------------------

$activeQuery = "
    SELECT COUNT(*) AS total
    FROM users
    WHERE status = 'Active'
";

$activeResult = mysqli_query($conn, $activeQuery);

$activeUsers = 0;

if ($activeResult) {
    $activeData = mysqli_fetch_assoc($activeResult);
    $activeUsers = $activeData['total'];
}


// ------------------------------------------
// INACTIVE USERS
// ------------------------------------------

$inactiveQuery = "
    SELECT COUNT(*) AS total
    FROM users
    WHERE status = 'Inactive'
";

$inactiveResult = mysqli_query($conn, $inactiveQuery);

$inactiveUsers = 0;

if ($inactiveResult) {
    $inactiveData = mysqli_fetch_assoc($inactiveResult);
    $inactiveUsers = $inactiveData['total'];
}


// ------------------------------------------
// USERS BY DEPARTMENT
// ------------------------------------------

$departmentQuery = "
    SELECT department, COUNT(*) AS total
    FROM users
    WHERE department IS NOT NULL
    AND department != ''
    GROUP BY department
    ORDER BY total DESC
";

$departmentResult = mysqli_query($conn, $departmentQuery);


// ------------------------------------------
// RECENTLY ADDED USERS
// ------------------------------------------

$recentQuery = "
    SELECT
        first_name,
        last_name,
        department,
        role,
        created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 3
";

$recentResult = mysqli_query($conn, $recentQuery);

// =====================================================
// LEAVE REQUESTS
// =====================================================
$leaveQuery = "
    SELECT
        leave_requests.leave_id,
        leave_requests.leave_type,
        leave_requests.start_date,
        leave_requests.end_date,
        leave_requests.reason,
        leave_requests.status,
        leave_requests.created_at,
        users.first_name,
        users.last_name
    FROM leave_requests
    INNER JOIN users
        ON leave_requests.user_id = users.user_id
    ORDER BY leave_requests.created_at DESC
";
$leaveResult = mysqli_query($conn, $leaveQuery);

if (!$leaveResult) {
    die("Leave Request Query Error: " . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TasKo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        .user-info{
    display:flex;
    align-items:center; 
    gap:10px;
    padding:5px ; 
    border-radius:10px;
    margin-left:1040px;
    background: #d5e5ff;
    border:1px solid var(--border);
    cursor:pointer;
  }
  .user-avatar{
    width:34px; height:34px; border-radius:50%;
    background:#1e3a5f;
    display:flex; align-items:center; justify-content:center;
    color:white; font-size:13px; font-weight:700;
  }
  .user-label{font-size:11px;color:var(--muted);}
  .user-name-top{font-size:13px;font-weight:700;color:var(--dark);}

  /*=========================
PROFILE
=========================*/

.profile-card{
background:#fff;
padding:20px;
border-radius:18px;
margin-left:235px;
margin-top:70px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}
.profile-header{
display:flex;
align-items:center;
gap:25px;
margin-bottom:35px;
}
.profile-avatar{
width:95px;
height:95px;
border-radius:50%;
background:#2563eb;
color:#fff;
display:flex;
justify-content:center;
align-items:center;
font-size:38px;
font-weight:bold;
}
.profile-header h2{
margin-bottom:5px;
color:#1e293b;
}
.profile-header p{
color:#64748b;
}
.profile-grid{
display:grid;
grid-template-columns:repeat(2,1fr);
gap:20px;
}
.form-group{
display:flex;
flex-direction:column;
}
.form-group label{
margin-bottom:8px;
font-weight:600;
font-size:14px;
color:#475569;
}
.form-group input{
padding:13px;
border:1px solid #dbe2ea;
border-radius:10px;
font-size:15px;
transition:.3s;
}
.form-group input:focus{
outline:none;
border-color:#2563eb;
box-shadow:0 0 8px rgba(37,99,235,.15);
}
.profile-buttons{
margin-top:30px;
}
.save-btn{
padding:12px 22px;
background:#2563eb;
color:#fff;
border:none;
border-radius:10px;
cursor:pointer;
margin-right:10px;
transition:.3s;
}
.save-btn:hover{
background:#1d4ed8;
transform:translateY(-2px);
}
.cancel-btn{
padding:12px 22px;
background:#f1f5f9;
border:none;
border-radius:10px;
cursor:pointer;
transition:.3s;
}
.cancel-btn:hover{
background:#e2e8f0;
}
@media(max-width:768px){
.profile-grid{
grid-template-columns:1fr;
}
.profile-header{
flex-direction:column;
text-align:center;
}
}

/* =========================
   SIDEBAR
========================= */
.sidebar {
    position: fixed;
    top: 70.7px;
    left: 0;
    width: 230px;
    height: calc(100vh - 50px);
    background: #ffffff;
    padding: 20px 12px;
    box-sizing: border-box;
    border-right: 1px solid #e5e7eb;
    overflow-y: auto;
    z-index: 1000;
}

/* =========================
   SIDEBAR ITEMS
========================= */
.sidebar .nav-item {
    display: block;
    width: 100%;
    margin: 6px 0;
    padding: 0;
    list-style: none;
    box-sizing: border-box;
    border-radius: 8px;
    cursor: pointer;
}

/* =========================
   SIDEBAR LINKS
========================= */
.sidebar .nav-item a {
    display: flex;
    align-items: center;
    width: 100%;
    min-height: 45px;
    padding: 0 15px;
    box-sizing: border-box;
    text-decoration: none;
    color: #555;
    border-radius: 8px;
    font-size: 14px;
}

/* =========================
   ICON
========================= */
.sidebar .nav-item a i {
    width: 20px;
    min-width: 20px;
    margin-right: 12px;
    text-align: center;
    color: #666;
}

/* =========================
   HOVER
========================= */
.sidebar .nav-item:hover {
    background: #f1f5f9;
}

.sidebar .nav-item:hover a {
    color: #2563eb;
}
.sidebar .nav-item:hover a i {
    color: #2563eb;
}

/* =========================
   ACTIVE
========================= */
.sidebar .nav-item.active {
    background: #2563eb;
}
.sidebar .nav-item.active a {
    color: #ffffff;
}
.sidebar .nav-item.active a i {
    color: #ffffff;
}
    </style>    
</head> 
<body>

<header>
    <nav class="navbar">
        <div class="nav-left">
            <img src="../images/tasko.png" alt="Logo" class="logo">
            <h3 class="website-name">TasKo</h3>
        </div>


        <div class="user-info" onclick="showPage('user-info-profile-page')">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['admin_name'],0,1)) ?></div>
        <div>
          <div class="user-label">Admin</div>
          <div class="user-name-top"><?php echo htmlspecialchars($_SESSION['admin_name']); ?>
    </div>
        </div>
      </div>
      <button id="logout-btn" onclick="window.location.href='../auth/logout.php'"><i class="fa-solid fa-right-from-bracket" style="margin-right: 8px; "></i>Logout</button>
    </nav>
</header>
<div style="display: flex;">
    <div class="sidebar">

    <li class="nav-item active" onclick="showPage('dashboard-page', this)">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>
    </li>

    <li class="nav-item" id="addUserNav"
    onclick="showPage('add-user-page', this)">

    <a href="javascript:void(0)">
        <i class="fa-solid fa-user-plus"></i>
        Add User
    </a>

</li>

    <li class="nav-item" onclick="showPage('manage-users-page', this)">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-users"></i>
            Manage Users
        </a>
    </li>

    <li class="nav-item" id="assignTaskNav"
    onclick="showPage('assign-task-page', this)">
    <a href="javascript:void(0)">
        <i class="fa-solid fa-tasks"></i>
        Assign Tasks
    </a>
</li>

    <li class="nav-item" onclick="showPage('manage-tasks-page', this)">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-list"></i>
            Manage Tasks
        </a>
    </li>

    <li class="nav-item" onclick="showPage('leave-request-page', this)">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-calendar-alt"></i>
            Leave Requests
        </a>
    </li>

    <li class="nav-item" onclick="showPage('reports-page', this)">
        <a href="javascript:void(0)">
            <i class="fa-solid fa-chart-line"></i>
            Reports
        </a>
    </li>

</div>
</div>

    <section id="dashboard-page"
    class="page <?php echo ($page === 'dashboard') ? 'active' : ''; ?>">

    <div class="page-section" id="dashboard" style="margin-top: 75px; margin-left:250px; margin-right:45px;">
        <div class="page-header" style="display: flex;">
            <div>
                <div class="page-title">Welcome to TasKo</div>
                <div class="page-subtitle" style="width:350px; color:#666">Here's what's happening in your company today.</div>
            </div>
            
            <button class="btn btn-newtask" onclick="showPage('assign-task-page', document.getElementById('assignTaskNav'))">
                <i class="fa-solid fa-plus"></i>
                New Task
            </button>
        
        </div>
    </div>
    
    
<div class="cards">
        <div class="card users">
            <h2><?php echo $totalUsers; ?></h2> 
                <p>Total Users</p>
        </div>

        <div class="card complete">
        <h2><?php echo $completedTasks; ?></h2>
        <p>Completed Tasks</p>
    </div>

    <div class="card progress">
        <h2><?php echo $inProgressTasks; ?></h2>
        <p>In Progress Tasks</p>
    </div>

    <div class="card overdue">
        <h2><?php echo $overdueTasks; ?></h2>
        <p>Overdue Tasks</p>
    </div>

    <div class="card leave">
        <h2><?php echo $leaveRequests; ?></h2>
        <p>Pending Leave Requests</p>
    </div>
    </div>

    <div class="recent-task-card">
    <div class="table-header">
        <h3>Recent Tasks</h3>
        <button class="view-btn">View All</button>
    </div>
    <table>
        <thead>
            <tr>
                 <th>TASK</th>
                 <th>ASSIGNED TO</th>
                 <th>STATUS</th>
                 <th>DUE</th>
            </tr>
        </thead>
        <tbody>
            <?php
                 while($row=mysqli_fetch_assoc($result)){
                    $name=$row['first_name']." ".$row['last_name'];
                    $letter=strtoupper(substr($row['first_name'],0,1));
                    $status=$row['status'];
            ?>
        
            <tr>
                <td><p><?php echo $row['title']; ?></p></td>
                <td>
                    <div class="user-box">
                        <div class="avatar">
                            <?php echo $letter; ?>
                        </div>
                        <?php echo $name; ?>
                    </div>
                </td>
                <td>
                    <span  class="status <?php echo strtolower(str_replace(' ','-',$status)); ?>">
                        <?php echo $status; ?>
                    </span>
                </td>
                <td>
                    <?php echo date("M j",strtotime($row['due_date'])); ?>
                </td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    </div>
</section>
<!-- add-user page -->
<section id="add-user-page" class="page">
    <div style="display: flex; margin-left:280px;">
    <div>
    <div class="page-title" style="margin-top: 75px; margin-left:250px; font-weight: bold; ">Add New User</div>
    <div class="page-subtitle" style="margin-left: 250px; color:#666">Create a new employee account</div>
        <form action="../Backend/add_user.php" method="POST" class="add-new-user-form" id="addUserForm">

    <div class="form-group">
        <label>FIRST NAME <label style="color: red;">*</label></label>
        <input type="text" name="first_name" id="first_name" placeholder="Enter First Name" >
    </div>

    <div class="form-group">
        <label>LAST NAME <label style="color: red;">*</label></label>
        <input type="text" name="last_name" id="last_name" placeholder="Enter Last Name" >
    </div>

    <div class="form-group">
        <label>EMAIL <label style="color: red;">*</label></label>   
        <input type="email" name="email" id="email" placeholder="example@gmail.com" >
    </div>

    <div class="form-group">
        <label>PHONE <label style="color: red;">*</label></label>
        <input type="text" name="phone" id="phone" placeholder="98XXXXXXXX" >
    </div>

    <div class="row">

        <div class="form-group">
            <label>DEPARTMENT <label style="color: red;">*</label></label>

            <select name="department" id="department" >
                <option value="">Select Department</option>
                <option>Development</option>
                <option>UI/UX Design</option>
                <option>Quality Assurance</option>
                <option>Marketing</option>
                <option>Human Resources</option>
            </select>
        </div>

        <div class="form-group">
            <label>ROLE <label style="color: red;">*</label></label>

            <select name="role" id="role" >
                <option value="">Select Role</option>
                <option>Frontend Developer</option>
                <option>Backend Developer</option>
                <option>Full Stack Developer</option>
                <option>Designer</option>
                <option>Tester</option>
            </select>
        </div>

    </div>

    <div class="row">

        <div class="form-group">
            <label>PASSWORD <label style="color: red;">*</label></label>
            <input type="password" name="password" id="password" placeholder="Create Password" >
        </div>

        <div class="form-group">
            <label>STATUS <label style="color: red;">*</label></label>

            <select name="status" id="status" >
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>

        </div>

    </div>

    <button type="submit" name="addUser" class="btn-primary">
        <i class="fa-solid fa-user-plus"></i>
        Add User
    </button>

    <button type="reset" class="btn-secondary">
        <i class="fa-solid fa-rotate-left"></i>
        Reset
    </button>

</form>
</div>


</div>
</section>

<!--Manage Users page-->
<!-- =========================================
     MANAGE USERS
========================================= -->
<section id="manage-users-page"
    class="page <?php echo ($page === 'manage-users') ? 'active' : ''; ?>">
<?php

// ==========================================
// LOAD ALL USERS FOR INITIAL DISPLAY
// ==========================================
$sql = "SELECT *
        FROM users
        ORDER BY user_id DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Users Query Failed: " . mysqli_error($conn));
}
?>

<!-- =========================================
     PAGE HEADER
========================================= -->
<div class="page-section"
     style="margin-top:75px; margin-left:250px; margin-right:45px;">

    <div class="page-header"
         style="display:flex;">

        <div>
            <div class="page-title">Manage Users</div>

            <div class="page-subtitle"
                 style="color:#666;">
                <?php echo $totalUsers; ?> Total Users
            </div>
        </div>

        <!-- ADD USER BUTTON -->
        <button
            class="btn btn-newtask"
            onclick="showPage(
                'add-user-page',
                document.getElementById('addUserNav')
            )"
            style="margin-right:45px;">

            <i class="fa-solid fa-plus"></i>
            Add User
        </button>
    </div>
</div>

<!-- =========================================
     USERS TABLE CONTAINER
========================================= -->
<div class="manage-users-container">

    <!-- =====================================
         SEARCH BOX
    ====================================== -->
    <div class="table-top">

    <form
        id="userSearchForm"
        class="user-search-form"
        onsubmit="searchUsers(event)"
    >

        <!-- SEARCH -->

        <div class="search-box">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                id="userSearch"
                placeholder="Search by ID, name or email..."
                autocomplete="off"
            >

        </div>


        <!-- DEPARTMENT -->

        <select
            id="userDepartment"
            class="user-filter-select" style="margin-left: 590px;"
        >

            <option value="">
                All Department
            </option>

            <?php

            $departmentQuery = "
                SELECT DISTINCT department
                FROM users
                WHERE department IS NOT NULL
                AND department != ''
                ORDER BY department ASC
            ";

            $departmentResult =
                mysqli_query(
                    $conn,
                    $departmentQuery
                );

            while (
                $department =
                mysqli_fetch_assoc(
                    $departmentResult
                )
            ) {

            ?>

                <option value="<?php
                    echo htmlspecialchars(
                        $department['department']
                    );
                ?>">

                    <?php
                    echo htmlspecialchars(
                        $department['department']
                    );
                    ?>

                </option>

            <?php } ?>

        </select>


        <!-- ROLE -->

        <select
            id="userRole"
            class="user-filter-select" style="margin-left: 1px;"
        >

            <option value="">
                All Role
            </option>

            <?php

            $roleQuery = "
                SELECT DISTINCT role
                FROM users
                WHERE role IS NOT NULL
                AND role != ''
                ORDER BY role ASC
            ";

            $roleResult =
                mysqli_query(
                    $conn,
                    $roleQuery
                );

            while (
                $role =
                mysqli_fetch_assoc(
                    $roleResult
                )
            ) {

            ?>

                <option value="<?php
                    echo htmlspecialchars(
                        $role['role']
                    );
                ?>">

                    <?php
                    echo htmlspecialchars(
                        $role['role']
                    );
                    ?>

                </option>

            <?php } ?>

        </select>

    </form>

</div>


    <!-- =====================================
         TABLE
    ====================================== -->
    <table class="users-table">
        <thead>
            <tr
                style="
                    background-color:#6fa4d6;
                    border:1px solid #dbe8f5;
                    font-weight:bold;
                ">
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Role</th>
                <th>Tasks</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <!-- IMPORTANT -->
        <tbody id="usersTableBody">

        <?php
        if (mysqli_num_rows($result) > 0) {
            $sn = 1;

            while ($row = mysqli_fetch_assoc($result)) {
                $user_id = $row['user_id'];

                // Count tasks
                $task = mysqli_query(
                    $conn,
                    "SELECT COUNT(*) AS total
                     FROM tasks
                     WHERE user_id='$user_id'"
                );

                $taskCount = 0;
                if ($task) {
                    $taskData =
                        mysqli_fetch_assoc($task);

                    $taskCount =
                        $taskData['total'];
                }

                $name =
                    $row['first_name'] .
                    " " .
                    $row['last_name'];
        ?>
            <tr>
                <!-- ID -->
                <td><?php echo $row['user_id']; ?></td>

                <!-- NAME -->
                <td><?php echo htmlspecialchars($name); ?></td>

                <!-- EMAIL -->
                <td><?php echo htmlspecialchars($row['email']); ?></td>

                <!-- DEPARTMENT -->
                <td>
                    <p style="
                        border-radius:9px;
                        background-color:#e4ecff;
                        color:#08309e;
                        text-align:center;
                        padding:5px;
                        margin:0;
                    ">
                        <?php
                        echo htmlspecialchars(
                            $row['department']
                        );
                        ?>
                    </p>
                </td>

                <!-- ROLE -->
                <td>
                    <p style="
                        border-radius:9px;
                        background-color:#e4ecff;
                        color:#08309e;
                        text-align:center;
                        padding:5px;
                        margin:0;
                    ">
                        <?php
                        echo htmlspecialchars(
                            $row['role']
                        );
                        ?>
                    </p>
                </td>

                <!-- TASKS -->
                <td><?php echo $taskCount; ?> Tasks </td>

                <!-- STATUS -->
                <td>
                    <?php
                    if ($row['status'] == "Active") {
                        echo "
                            <span
                                class='badge active'
                                style='
                                    border-radius:9px;
                                    background-color:#b4f5c4;
                                    color:#008822;
                                    padding:5px 10px;
                                '>
                                Active
                            </span>
                        ";
                    } else {
                        echo "
                            <span
                                class='badge inactive'
                                style='
                                    border-radius:9px;
                                    background-color:#fabcbc;
                                    color:#c20909;
                                    padding:5px 10px;
                                '>
                                Inactive
                            </span>
                        ";
                    }
                    ?>
                </td>

                <!-- ACTIONS -->
                <td>
                    <a
                        href="edit_user.php?id=<?php echo $row['user_id']; ?>"
                        class="edit-btn">
                        <i class="fa-solid fa-pen-to-square"></i>
                        Edit
                    </a>
                    <a
                        href="../Backend/delete_user.php?id=<?php echo $row['user_id']; ?>"
                        class="delete-btn"
                        onclick="return confirm('Delete this user?')">
                        <i class="fa-solid fa-trash"></i>
                        Delete
                    </a>
                </td>
            </tr>
        <?php
            }
        } else {
        ?>
            <tr>
                <td
                    colspan="8"
                    style="
                        text-align:center;
                        padding:30px;
                    ">
                    No users found.
                </td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
</section>

<!--Assign Task Section-->
<section id="assign-task-page" class="page" style="margin-left: 250px;">
    <div class="page-title" style="margin-top: 75px; margin-left:536px; font-size:20px;"><b>Assign Task</b></div>
    <div class="page-subtitle" style="margin-left: 478px; color:#666">Create and assign tasks to users</div>
    <div class="assign-card">
<form method="POST">
<div class="form-group">
<label>TASK TITLE <label style="color: red;">*</label></label>

<input type="text" name="title" placeholder="e.g. Design the login page" required>

</div>
<div class="form-group">
<label>DESCRIPTION</label>
<textarea name="description" rows="5" placeholder="Describe what needs to be done..."></textarea>
</div>
<div class="row">
<div class="form-group">
<label>ASSIGN TO <label style="color: red;">*</label></label>
<select name="user_id" required>
<option value="">Select Employee</option>

<?php
$users=mysqli_query($conn,
"SELECT user_id,first_name,last_name
FROM users
WHERE status='Active'");

while($user=mysqli_fetch_assoc($users)){
?>

<option value="<?= $user['user_id']; ?>">
<?= $user['first_name']." ".$user['last_name']; ?>
</option>

<?php } ?>
</select>
</div>
<div class="form-group">
<label>PRIORITY <label style="color: red;">*</label></label>

<select name="priority">
<option>Low</option>
<option selected>Medium</option>
<option>High</option>
</select>

</div>
</div>
<div class="form-group">
<label>CATEGORY</label>

<select name="category" t>
<option>Development</option>
<option>Design</option>
<option>Marketing</option>
<option>Testing</option>
<option>Human Resource</option>
</select>

</div>
<div class="row">
<div class="form-group">
<label>START DATE <label style="color: red;">*</label></label>

<input type="date" name="start_date" required>
</div>
<div class="form-group">
<label>DUE DATE <label style="color: red;">*</label></label>

<input type="date" name="due_date" required>
</div>
</div>

<div class="buttons">
<button type="submit" name="assignTask" class="assign-btn"><i class="fa-solid fa-paper-plane"></i>Assign Task</button>
<button type="reset" class="clear-btn"><i class="fa-solid fa-rotate-left"></i>Clear</button>
</div>

</form>
</div>
</section>

<!--Manage Tasks Section-->
<!-- ==========================
        MANAGE TASKS
=========================== -->

    <section id="manage-tasks-page" class="page">
        <div class="manage-tasks-container" style="margin-left:240px;">

    <?php
    $sql = "SELECT
                tasks.*,
                users.first_name,
                users.last_name
            FROM tasks
            INNER JOIN users
                ON tasks.user_id = users.user_id
            ORDER BY tasks.task_id DESC";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Tasks Query Failed: " . mysqli_error($conn));
    }
    ?>

    <div class="page-header">
        <div>
            <h2>Manage Tasks</h2>
            <p>Manage and monitor all assigned tasks.</p>
        </div>
    </div>
    <div style="border: 1px solid #dfe6ef; border-radius:10px;">
    <div class="table-top" >
        <form id="taskSearchForm" class="task-search-form">
        
            <!-- Search -->
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="taskSearch" placeholder="Search by Task ID, task or employee..." autocomplete="off">
            </div>

            <!-- Priority -->
            <select id="taskPriority" class="task-priority-select">
                <option value="">All Priority</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>

             <!-- STATUS -->
        <select id="taskStatus" class="task-status-select">
            <option value="">All Status</option>
            <option value="Completed">Completed</option>
            <option value="In Progress">In Progress</option>
            <option value="Pending">Pending</option>
            <option value="Overdue">Overdue</option>
        </select>
        </form>
    </div>

    <div class="table-responsive">
    <table class="task-table">

    <thead>
        <tr>
            <th>Task ID</th>
            <th>Task</th>
            <th>Assigned To</th>
            <th>Priority</th>
            <th>Start Date</th>
            <th>Due Date</th>
            <th>Progress</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="tasksTableBody">

    <?php
    if(mysqli_num_rows($result)>0){
    $sn=1;
    while($row=mysqli_fetch_assoc($result)){
    $name=$row['first_name']." ".$row['last_name'];
    $avatar=strtoupper(substr($row['first_name'],0,1));
    ?>

    <tr>
        <td><?php echo $row['task_id']; ?></td>
        <td>
            <div class="task-info">
                <strong><?php echo $row['title']; ?></strong><br>
                <small><?php echo substr($row['description'],0,40);?>...</small>
            </div>
        </td>
        <td>
            <div class="employee">
                <div class="avatar">
                    <?php echo $avatar; ?>
                </div>
                <div>
                    <?php echo $name; ?>
                </div>
            </div>
        </td>
        <td>
            <?php 
                if($row['priority']=="High"){
                    echo "<span class='priority high'>High</span>";
                    }
                    elseif($row['priority']=="Medium"){
                        echo "<span class='priority medium'>Medium</span>";
                    }
                    else{
                        echo "<span class='priority low'>Low</span>";
                    }
                ?>
        </td>
        <td>
            <?php
                echo date("d M Y",strtotime($row['start_date']));
            ?>
        </td>
        <td>
            <?php
                echo date("d M Y",strtotime($row['due_date']));
            ?>
        </td>
        <td>
            <div class="progress-bar">
                <div class="progress-fill" style="width:<?php echo $row['progress']; ?>%;"></div>
            </div>
            <span>
                <?php echo $row['progress']; ?>%
            </span>
        </td>
        <td>
            <?php
                $status=$row['status'];
                $class="";
                
                if($status=="Completed"){
                    $class="completed";
                }
                elseif($status=="In Progress"){
                    $class="inprogress";
                }
                else{
                    $class="pending";
                }
            ?>
    <span class="status <?php echo $class; ?>"><?php echo $status; ?></span>
        </td>
            <td>
                <a href="edit_task.php?id=<?php echo $row['task_id']; ?>" class="action-btn edit">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <a href="../Backend/delete_task_process.php?id=<?php echo $row['task_id']; ?>" class="action-btn delete" onclick="return confirm('Delete this task?')">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </td>
    </tr>
    <?php
    }
    }else{
    ?>

    <tr>
        <td colspan="9" style="text-align:center;padding:25px;">No Tasks Found</td>
    </tr>

    <?php } ?>
    </tbody>
    </table>
    </div>
    <!-- Pagination (Ready for Future) -->
    <div class="pagination">
    <button disabled>Previous</button>
    <span>Page 1</span>
    <button disabled>Next</button>
    </div>
    </div>
    </div>
    </section>

<!--User Info Profile Page-->
<section id="user-info-profile-page" class="page">

<?php
$admin_id=$_SESSION['admin_id'];
$sql=mysqli_query($conn,
"SELECT * FROM admins WHERE admin_id='$admin_id'");
$admin=mysqli_fetch_assoc($sql);

?>
<div class="profile-card">
<div class="profile-header">
    
<div class="profile-avatar">
<?php
echo strtoupper(substr($admin['full_name'],0,1));
?>
</div>

<div>
<h2>
<?php
echo $admin['full_name'];
?>
</h2>

<p>Admin</p>

</div>
<button  class="save-btn" onclick="showPage('change-password-page')" style="margin-left:750px; background-color:black; color:white;">
<i class="fa-solid fa-floppy-disk"></i>
Change Password
</button>
</div>

<form action="../Backend/update_profile.php" method="POST">
<input
type="hidden"
name="admin_id"
value="<?php echo $admin['admin_id']; ?>">

<div class="profile-grid">
<div class="form-group">
<label>Full Name</label>
<input type="text" name="full_name" value="<?php echo $admin['full_name']; ?>">
</div>

<div class="form-group">
<label>Email</label>
<input
type="email"
name="email"
value="<?php echo $admin['email']; ?>">
</div>

<div class="form-group">
<label>Phone Number</label>
<input
type="number"
name="phone"
value="<?php echo $admin['phone']; ?>">
</div>


<div class="form-group">
<label>Joined</label>
<input
type="text"
value="<?php echo date("d M Y",strtotime($admin['created_at'])); ?>"
readonly>
</div>
</div>

<div class="profile-buttons">
<button type="submit" class="save-btn">
<i class="fa-solid fa-floppy-disk"></i>
Save Changes
</button>


<button
type="reset"
class="cancel-btn">
<i class="fa-solid fa-rotate-left"></i>
Reset
</button>
</div>
</form>
</div>
</section>

<!--Change Password Page-->
<section id="change-password-page" class="page">

<?php
$message = "";
?>

<div class="change-password-page" style="margin-top: 75px; margin-left:250px; margin-right:45px;">
<div class="change-password-container">

<div class="page-title">
<h2>Change Password</h2>
<p>Update your account password.</p>
</div>

<?php
if($message!=""){
?>
<div class="success-message">
    <?php echo $message; ?>
</div>
<?php } ?>

<form method="POST" action="">
<div class="password-group">
<label>Current Password</label>
<div class="password-box">

<input type="password" id="currentPassword" name="current_password" required>
<i class="fa-solid fa-eye" onclick="togglePassword('currentPassword',this)"> </i>

</div>
</div>

<div class="password-group">
<label>New Password</label>
<div class="password-box">

<input type="password" id="newPassword" name="new_password" required>
<i class="fa-solid fa-eye" onclick="togglePassword('newPassword',this)"> </i>

</div>
</div>

<div class="password-group">
<label>Confirm Password</label>
<div class="password-box">

<input type="password" id="confirmPassword" name="confirm_password" required>

<i class="fa-solid fa-eye" onclick="togglePassword('confirmPassword',this)"> </i>

</div>
</div>

<div class="password-buttons">
<button type="submit" class="btn-primary"
name="changePassword">

<i class="fa-solid fa-key"></i>

Update Password

</button>

<button
type="reset"
class="btn-secondary">

<i class="fa-solid fa-rotate-left"></i>

Clear

</button>

</div>

</form>

</div>
</div>  
</section>

<!-- =========================================
     LEAVE REQUESTS
========================================= -->
<section id="leave-request-page" class="page <?php echo ($page === 'leave-requests') ? 'active' : ''; ?>">
    <div class="leave-page-container">

        <!-- HEADER -->
        <div class="leave-page-header">
            <div>
                <h1>Leave Requests</h1>
                <p>Review and manage employee leave requests.</p>
            </div>
        </div>

        <!-- ==============================
             STAT CARDS
        =============================== -->
        <div class="leave-summary">
            <div class="leave-summary-card">
                <div class="leave-summary-icon pending-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <span>Pending</span>
                    <strong>
                        <?php
                        $pendingLeaveCount = mysqli_fetch_assoc(
                            mysqli_query(
                                $conn,
                                "SELECT COUNT(*) AS total
                                 FROM leave_requests
                                 WHERE status='Pending'"
                            )
                        )['total'];
                        echo $pendingLeaveCount;
                        ?>
                    </strong>
                </div>
            </div>

            <div class="leave-summary-card">
                <div class="leave-summary-icon approved-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <span>Approved</span>
                    <strong>
                        <?php
                        $approvedLeaveCount = mysqli_fetch_assoc(
                            mysqli_query(
                                $conn,
                                "SELECT COUNT(*) AS total
                                 FROM leave_requests
                                 WHERE status='Approved'"
                            )
                        )['total'];
                        echo $approvedLeaveCount;
                        ?>
                    </strong>
                </div>
            </div>

            <div class="leave-summary-card">
                <div class="leave-summary-icon rejected-icon">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <div>
                    <span>Rejected</span>
                    <strong>
                        <?php
                        $rejectedLeaveCount = mysqli_fetch_assoc(
                            mysqli_query(
                                $conn,
                                "SELECT COUNT(*) AS total
                                 FROM leave_requests
                                 WHERE status='Rejected'"
                            )
                        )['total'];
                        echo $rejectedLeaveCount;
                        ?>
                    </strong>
                </div>
            </div>
        </div>

        <!-- ==============================
             REQUEST TABLE
        =============================== -->
        <div class="leave-table-card">
            <div class="leave-table-top">
                <div>
                    <h2>All Leave Requests</h2>
                    <p>Review employee leave applications.</p>
                </div>

                <select
        id="leaveStatus"
        class="leave-status-select"
    >
        <option value="">
            All Status
        </option>
        <option value="Pending">
            Pending
        </option>
        <option value="Approved">
            Approved
        </option>
        <option value="Rejected">
            Rejected
        </option>
    </select>

            </div>
            <div class="leave-table-wrapper">
                <table class="leave-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>EMPLOYEE</th>
                            <th>LEAVE TYPE</th>
                            <th>START DATE</th>
                            <th>END DATE</th>
                            <th>REASON</th>
                            <th>STATUS</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="leaveTableBody">
                    <?php
                    if ($leaveResult && mysqli_num_rows($leaveResult) > 0) {
                        while ($leave = mysqli_fetch_assoc($leaveResult)) {
                            $employeeName =
                                $leave['first_name'] . " " .
                                $leave['last_name'];

                            $initial =
                                strtoupper(
                                    substr(
                                        $leave['first_name'],
                                        0,
                                        1
                                    )
                                );

                            $status =
                                $leave['status'];

                            $statusClass =
                                strtolower(
                                    str_replace(
                                        ' ',
                                        '-',
                                        $status
                                    )
                                );
                    ?>
                        <tr data-leave-status="<?php echo htmlspecialchars($leave['status']); ?>">
                            <!-- ID -->
                            <td><?php echo $leave['leave_id']; ?></td>
                            <!-- EMPLOYEE -->
                            <td>
                                <div class="leave-employee">
                                    <div class="leave-avatar">
                                        <?php echo $initial; ?>
                                    </div>
                                    <span>
                                        <?php
                                        echo htmlspecialchars(
                                            $employeeName
                                        );
                                        ?>
                                    </span>
                                </div>
                            </td>

                            <!-- TYPE -->
                            <td>
                                <span class="leave-type">
                                    <?php
                                    echo htmlspecialchars(
                                        $leave['leave_type']
                                    );
                                    ?>
                                </span>
                            </td>

                            <!-- START -->
                            <td>
                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $leave['start_date']
                                    )
                                );
                                ?>
                            </td>

                            <!-- END -->
                            <td>
                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $leave['end_date']
                                    )
                                );
                                ?>
                            </td>

                            <!-- REASON -->
                            <td>
                                <div class="leave-reason">
                                    <?php
                                    $reason =
                                        $leave['reason'];

                                    if (strlen($reason) > 35) {
                                        echo htmlspecialchars(
                                            substr(
                                                $reason,
                                                0,
                                                35
                                            )
                                        ) . "...";
                                    } else {
                                        echo htmlspecialchars(
                                            $reason
                                        );
                                    }
                                    ?>
                                </div>
                            </td>

                            <!-- STATUS -->
                            <td>
                                <span class="leave-status <?php
                                    echo $statusClass;
                                ?>">
                                    <?php
                                    echo htmlspecialchars(
                                        $status
                                    );
                                    ?>
                                </span>
                            </td>

                            <!-- ACTION -->
                            <td>
                            <?php
                            if ($status == "Pending") {
                            ?>
                                <div class="leave-actions">
                                    <a
                                        href="../Backend/update_leave_status.php?id=<?php echo $leave['leave_id']; ?>&status=Approved"
                                        class="leave-approve"
                                        onclick="return confirm('Approve this leave request?')" style="background-color: #264eff; color: white;"
                                    >
                                        <i class="fa-solid fa-check"></i>
                                        Approve
                                    </a>

                                    <a
                                        href="../Backend/update_leave_status.php?id=<?php echo $leave['leave_id']; ?>&status=Rejected"
                                        class="leave-reject"
                                        onclick="return confirm('Reject this leave request?')" style="background-color: #ff4d4d; color: white;"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                        Reject
                                    </a>
                                </div>
                            <?php
                            } else {
                            ?>
                                <span class="action-completed">
                                    No Action
                                </span>
                            <?php
                            }
                            ?>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td
                                colspan="8"
                                class="leave-no-data"
                            >
                                <i class="fa-solid fa-calendar-check"></i>
                                <p>
                                    No leave requests found.
                                </p>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section id="reports-page" class="page <?php echo ($page === 'reports') ? 'active' : ''; ?>">
    <div class="reports-container">
        <div class="reports-header">
            <div>
                <h1>Reports</h1>
                <p>Task and employee performance overview</p>
            </div>
        </div>

        <!-- ============================= -->
        <!-- TASK STATUS -->
        <!-- ============================= -->
        <div class="report-card task-status-card">
            <div class="report-card-header">
                <h2>Task Status</h2>
                <span>Overall task distribution</span>
            </div>
            <div class="chart-container doughnut-container">
                <canvas id="taskStatusChart"></canvas>
            </div>
        </div>

        <!-- ============================= -->
        <!-- EMPLOYEE PERFORMANCE -->
        <!-- ============================= -->
        <div class="report-card employee-performance-card">
            <div class="report-card-header">
                <h2>Employee Performance</h2>
                <span>Completed tasks by employee</span>
            </div>
            <div class="chart-container">
                <canvas id="employeePerformanceChart"></canvas>
            </div>
        </div>

        <!-- ============================= -->
        <!-- COMPLETED TASK TREND -->
        <!-- ============================= -->
        <div class="report-card completed-trend-card">
            <div class="report-card-header">
                <h2>Completed Task Trend</h2>
                <span>Last 7 days</span>
            </div>
            <div class="chart-container">
                <canvas id="completedTrendChart"></canvas>
            </div>
        </div>
    </div>
</section>

<script>
window.taskReportData = {
    completed: <?php echo $completedTasks; ?>,
    inProgress: <?php echo $inProgressTasks; ?>,
    pending: <?php echo $pendingTasks; ?>,
    overdue: <?php echo $overdueTasks; ?>
};

window.employeeReportData = {
    names: <?php echo json_encode($employeeNames); ?>,
    completed: <?php echo json_encode($employeeCompleted); ?>
};

window.trendReportData = {
    labels: <?php echo json_encode($trendLabels); ?>,
    values: <?php echo json_encode($trendValues); ?>
};
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="chart.js"></script>
<script src="../Frontend/dashboard.js"></script>

<script src="../Frontend/add_user.js"></script>
</body>
</html>
