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
        echo "<script>alert('Task Assigned Successfully');</script>";
    }else{
        echo mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TasKo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        *{
            margin: 0;
            box-sizing: border-box;
        }
        #logout-btn{
            text-decoration: none;
            border: 1px solid black;
            border-radius: 6px;
            background-color: black;
            color: white;
            padding: 8px 10px;
            font-size: 15px;
            font-weight: bold;
            box-shadow: 0px 3px 10px 1px rgb(219, 219, 219);
            cursor: pointer;
            transition:.2s;
        }

        #logout-btn:hover{
            background-color:white;
            color: black;
            font-size: 15px;
            padding: 8px 10px;
            transition: 0.2s;
            box-shadow: 0px 3px 10px 1px rgb(206, 204, 204);
            transform:scale(1.04);

        }
        .nav-center li a:hover{
            transition: 0.3s;
            background: #f1f0f0;
            color: blue;
            padding: 5px 10px;
             border-radius: 5px;
             font-weight: medium;
           
        }
        .sidebar{
            margin-top: 50px;
            background: white;
            color:black; 
            border-radius: 10px; 
            box-shadow: 0px 3px 10px 1px rgb(219, 219, 219);
            width: 225px;
            height: 100%;
            position:fixed;
            box-shadow:0px 3px 10px 1px rgb(245, 245, 245);
        }
        #sideoption{
            margin-top: 15px;
        }
        #sideoption a,li{
            text-decoration: none;
            color: black;
            font-size: 14px;
            font-family: Arial, sans-serif;
            font-weight: medium;    
        }
        #sideoption a:hover{
            transition: 0.3s;
            background: #e5ecf7;
            color: blue;
             border-radius: 5px;
        }
        .navbar{
            height: 60px;
        }
        .nav-center li a{
            padding: 5px 10px;
            border-radius: 5px;
            font-size:13px;
            font-weight: normal;
        }
        body{
            background-color: #f8fafc;
        }
        .user-name{
            font-size: 13px;
            margin-right: 10px;
        }
        .page-title{
            font-size:22px;
            font-weight:700;
            color:var(--dark);
        }
        .page-subtitle{
            font-size:13px;
            color:var(--muted);
            margin-top:2px;
        }
        .navbar{
            box-shadow:0px 3px 10px 1px rgb(245, 245, 245);
        }
        .btn{
            border:none;
            background-color: #264eff;
            color: white;
            cursor: pointer;
            padding: 10px 17px;
            border-radius: 9px;
            height: 37px;
            margin-left: 836px;
             transition:.2s;
        }
        .btn:hover{
            background-color: #264eff;
             transform:scale(1.03);
             font-size: 14px;
            box-shadow:0px 3px 10px 1px rgb(238, 238, 238);

        }   
        .btn-newtask{
            border:none;
            background-color: #264eff;
            color: white;
            cursor: pointer;
            padding: 10px 17px;
            border-radius: 9px;
            height: 37px;
            margin-left: 836px;
             transition:.2s;
        }
        .btn-newtask:hover{
            background-color: #264eff;
             transform:scale(1.03);
             font-size: 14px;
            box-shadow:0px 3px 10px 1px rgb(238, 238, 238);

        }   
        .cards{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:15px;
           margin-top: 25px;
           margin-left: 235px;
        }
        .card{
            background:#fff;
            border-radius:12px;
            padding:25px;
            box-shadow:0 4px 10px rgba(201, 201, 201, 0.08);
            text-align:center;
            width: 230px;
            height: 120px;  
            transition:.2s;
            border-top:5px solid #3876fc;
        }
        .card:hover{
            transform:scale(1.03);
            box-shadow:0 4px 10px rgba(121, 121, 121, 0.15);
            color: #ff0000;
        }
        .card h2{
            font-size:27px;
            margin:0;
        }
        .card p{
            margin-top:10px;
            color:#666;
            font-size:17px;
        }
        .add-new-user{
            margin-left: 250px;
            width: 600px;
            height: 400px;
            border: 2px solid grey;
            border-radius: 7px;
            background-color: white;
            box-shadow:0 4px 10px rgba(201, 201, 201, 0.08);
        }
        .label1{
            color: grey;
            font-size: 13px;
        }
        
        .recent-task-card{
            background:#fff;
            border: 1px solid rgba(47, 103, 255, 0.08);
            border-radius:15px;
            padding:25px;
            box-shadow:0 5px 15px rgba(77, 77, 77, 0.08);
            margin-left:235px;
            margin-top:30px;
            width: 1270px;
        }            
        .table-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }
        .view-btn{
            border:1px solid #ddd;
            background:white;
            padding:8px 15px;
            border-radius:20px;
            cursor:pointer;
        }
        table{
            width:100%;
            border-collapse:collapse;
        }
        th{
            text-align:left;
            padding:14px;
            background:#f7f9fc;
            color:#5d6d8a;
            font-size:13px;
        }
        td{
            padding:16px;
            border-bottom:1px solid #eee;
        }
        .user-box{
            display:flex;
            align-items:center;
            gap:10px;
        }
.avatar{
    width:35px;
    height:35px;
    border-radius:50%;
    background:#4f46e5;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    font-weight:bold;
}
.status{
    padding:6px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
}
.completed{
    background:#d1fae5;
    color:#10b981;
}
.in-progress{
    background:#dbeafe;
    color:#2563eb;
}
.pending{
    background:#fef3c7;
    color:#d97706;
}
.page{
    display:none;
    animation:fade .35s ease;
}
.page.active{
    display:block;
}
@keyframes fade{
    from{
        opacity:0;
        transform:translateY(5px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

/*ADD USER*/
.add-new-user-form{
    margin-left: 240px;
    width: 650px;
    background: #fff;
    padding: 18px;
    border-radius: 11px;
    margin-top: 10px;

}
.form-group{
    display: flex;
    flex-direction: column;
    margin-bottom: 18px;
}
.form-group label{
    font-size: 13px;
    font-weight: 600;
    color: #5b6b83;
    margin-bottom: 8px;
    letter-spacing: .5px;
    text-transform: uppercase;
}
.form-group input,
.form-group select{
    width: 100%;
    height: 45px;
    border: 1px solid #d9e2ec;
    border-radius: 10px;
    padding: 0 15px;
    font-size: 15px;
    outline: none;
    transition: .3s;
    background: #fff;
}
.form-group input::placeholder{
    color: #9ca3af;
}
.form-group input:focus,
.form-group select:focus{
    border-color: #2f5cff;
    box-shadow: 0 0 8px rgba(47,92,255,.2);
}
.row{
    display: flex;
    gap: 20px;
}
.row .form-group{
    flex: 1;
}
.btn-primary,
.btn-secondary{
    padding: 12px 22px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: .3s;
    margin-top: 10px;
}
.btn-primary{
    background: #2f5cff;
    color: white;
    margin-right: 10px;
}
.btn-primary:hover{
    background: #2148e5;
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(47,92,255,.25);
}
.btn-secondary{
    background: white;
    color: #222;
    border: 1px solid #d9e2ec;
}
.btn-secondary:hover{
    background: #f8fafc;
    transform: translateY(-2px);
    box-shadow: 0 5px 12px rgba(0,0,0,.08);
}
.btn-primary i,
.btn-secondary i{
    margin-right: 8px;
}
/* Responsive */
@media (max-width:768px){
    form{
        width:100%;
    
    .row{
        flex-direction:column;
        gap:0;
    }
    .btn-primary,
    .btn-secondary{
        width:100%;
        margin-right:0;
        margin-bottom:10px;
    }
}
}
.table-container{

background:#fff;
padding:20px;
border-radius:16px;
box-shadow:0 5px 15px rgba(0,0,0,.06);

}

.table-top{

display:flex;
justify-content:space-between;
margin-bottom:20px;

}

.search-box{

width:800px;
display:flex;
align-items:center;
border:1px solid #a3a3a3;
padding:12px;
border-radius:10px;

}

.search-box i{

color:#64748b;
margin-right:10px;

}

.search-box input{

border:none;
outline:none;
width:100%;
font-size:15px;

}

table{

width:100%;
border-collapse:collapse;

}

th{

padding:16px;
background:#f8fafc;
font-size:13px;
text-align:left;
color:#64748b;

}

td{

padding:18px;
border-bottom:1px solid #eef2f7;

}

tr:hover{

background-color: #eaf0f5;

}

.badge{

padding:6px 14px;
border-radius:30px;
font-size:12px;
font-weight:600;

}

.inactive{

background:#fee2e2;
color:#ef4444;

}

.edit-btn{

background:#dbeafe;
color:#2563eb;
padding:6px 12px;
border-radius:6px;
text-decoration:none;
margin-right:8px;

}

.delete-btn{

background:#fee2e2;
color:#ef4444;
padding:6px 10px;
border-radius:6px;
text-decoration:none;

}

.edit-btn:hover{

background:#2563eb;
color:white;

}

.delete-btn:hover{

background:#ef4444;
color:white;

}
.manage-users-container{
    width: 1270px;
    margin-left:240px;
    margin-top: 20px;
    border-radius: 10px;
    border:1px solid #dbe2ea;
    box-shadow: 0 5px 15px rgba(0,0,0,.05);
    background-color: white;
}
.search-form{
    margin-left: 20px;
    width: 650px;
    background: #fff;
    padding: 18px;
    margin-top: 10px;
}
/*Assign Tasks CSS*/
.assign-card{
width:720px;
margin-left: 240px;
margin-top: 20px;
background:#fff;
padding:18px;
border-radius:18px;
border:1px solid #dfe6ef;
box-shadow:0 5px 15px rgba(0,0,0,.04);
}
.form-group{
display:flex;
flex-direction:column;
margin-bottom:18px;
}
.form-group label{
font-size:13px;
font-weight:700;
color:#5d6b82;
margin-bottom:8px;
letter-spacing:.5px;
}
.form-group input,.form-group textarea,.form-group select{
width:100%;
padding:5px;
border:1px solid #d8e1ec;
border-radius:12px;
font-size:13px;
outline:none;
transition:.25s;
}
.form-group textarea{
resize:none;
}
.form-group input:focus,.form-group textarea:focus,.form-group select:focus{
border-color:#2d5cff;
box-shadow:0 0 8px rgba(45,92,255,.18);
}
.row{
display:flex;
gap:18px;
}
.row .form-group{
flex:1;
}
.buttons{
margin-top:20px;
}
.assign-btn{
background:#2d5cff;
color:#fff;
border:none;
padding:12px 22px;
border-radius:10px;
cursor:pointer;
font-size:15px;
font-weight:600;
transition:.25s;
margin-right:10px;
}
.assign-btn:hover{
background:#2149ea;
transform:translateY(-2px);
box-shadow:0 8px 18px rgba(45,92,255,.25);
}
.clear-btn{
background:#fff;
border:1px solid #d8e1ec;
padding:12px 20px;
border-radius:10px;
cursor:pointer;
font-size:15px;
transition:.25s;
}
.clear-btn:hover{
background:#f7f9fc;
transform:translateY(-2px);
}
.assign-btn i,.clear-btn i{
margin-right:6px;
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

        <ul class="nav-center">
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="#">Reviews</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Support</a></li>
            <li><a href="#">About us</a></li>
        </ul>    


        <div class="user-info">
            <span class="user-name">
                <b>Admin:</b> <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
            </span> 
            <button id="logout-btn" onclick="window.location.href='../auth/logout.php'"><i class="fa-solid fa-right-from-bracket" style="margin-right: 8px;"></i>Logout</button>
        </div>
    </nav>
</header>
<div style="display: flex;">
    <div class="sidebar" style="margin-top:50px;">
            <li id="sideoption" onclick="showPage('dashboard-page')"><a href="#"><i class="fa-solid fa-house" style="height:12px; width:12px; margin-right: 10px;"></i>Dashboard</a></li>
            <li id="sideoption" onclick="showPage('add-user-page')"><a href="#"><i class="fa-solid fa-user-plus" style="height:12px; width:12px; margin-right: 10px;"></i>Add User</a></li>
            <li id="sideoption" onclick="showPage('manage-users-page')"><a href="#"><i class="fa-solid fa-users" style="height:12px; width:12px; margin-right: 10px;"></i>Manage Users</a></li>
            <li id="sideoption" onclick="showPage('assign-task-page')"><a href="#"><i class="fa-solid fa-tasks" style="height:12px; width:12px; margin-right: 10px;"></i>Assign Tasks</a></li>
            <li id="sideoption" onclick="showPage(' ')"><a href="#"><i class="fa-solid fa-list" style="height:12px; width:12px; margin-right: 10px;"></i>Manage Tasks</a></li>
            <li id="sideoption" onclick="showPage(' ')"><a href="#"><i class="fa-solid fa-calendar-alt" style="height:12px; width:12px; margin-right: 10px;"></i>Leave Requests</a></li>
            <li id="sideoption" onclick="showPage(' ')"><a href="#"><i class="fa-solid fa-chart-line" style="height:12px; width:12px; margin-right: 10px;"></i>Reports</a></li>
    </div>
</div>
    <section id="dashboard-page" class="page active">
    <div class="page-section" id="dashboard" style="margin-top: 75px; margin-left:250px;">
        <div class="page-header" style="display: flex;">
            <div>
                <div class="page-title">Welcome to TasKo</div>
                <div class="page-subtitle" style="color:#666">Here's what's happening in your company today.</div>
            </div>
            <button class="btn btn-newtask" onclick="showPage('assign-tasks',document.querySelector('.nav-item:nth-child(8)'))">
          <i class="fa-solid fa-plus">  </i>  New Task
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
                <td><b><?php echo $row['title']; ?></b></td>
                <td>
                    <div class="user-box">
                        <div class="avatar">
                            <?php echo $letter; ?>
                        </div>
                        <?php echo $name; ?>
                    </div>
                </td>
                <td>
                    <span class="status <?php echo strtolower(str_replace(' ','-',$status)); ?>">
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
<section id="add-user-page" class="page">
    <div class="page-title" style="margin-top: 75px; margin-left:250px;">Add New User</div>
    <div class="page-subtitle" style="margin-left: 250px; color:#666">Create a new employee account</div>
        <form action="../Backend/add_user.php" method="POST" class="add-new-user-form">

    <div class="form-group">
        <label>FIRST NAME <label style="color: red;">*</label></label>
        <input type="text" name="first_name" placeholder="Enter First Name" required>
    </div>

    <div class="form-group">
        <label>LAST NAME <label style="color: red;">*</label></label>
        <input type="text" name="last_name" placeholder="Enter Last Name" required>
    </div>

    <div class="form-group">
        <label>EMAIL <label style="color: red;">*</label></label>
        <input type="email" name="email" placeholder="example@gmail.com" required>
    </div>

    <div class="form-group">
        <label>PHONE <label style="color: red;">*</label></label>
        <input type="text" name="phone" placeholder="98XXXXXXXX" required>
    </div>

    <div class="row">

        <div class="form-group">
            <label>DEPARTMENT <label style="color: red;">*</label></label>

            <select name="department" required>
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

            <select name="role" required>
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
            <input type="password" name="password" placeholder="Create Password" required>
        </div>

        <div class="form-group">
            <label>STATUS <label style="color: red;">*</label></label>

            <select name="status">
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
</section>

<!--Manage Users page-->
<section id="manage-users-page" class="page">
    <?php
$search = "";

if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn,$_GET['search']);
}
$sql = "SELECT *
        FROM users
        WHERE first_name LIKE '%$search%'
        OR last_name LIKE '%$search%'
        OR email LIKE '%$search%'
        ORDER BY user_id DESC";

$result = mysqli_query($conn,$sql);
?>
<div class="page-section" id="dashboard" style="margin-top: 75px; margin-left:250px;">
        <div class="page-header" style="display: flex;">
            <div>
                <div class="page-title">Manage Users</div>
                <div class="page-subtitle" style="color:#666"><?php echo $totalUsers; ?> Total Users</div>
            </div>
            <button class="btn btn-newtask" onclick="showPage('add-user-page',document.querySelector('.nav-item:nth-child(8)'))" style="margin-left: 975px;">
          <i class="fa-solid fa-plus">  </i>  Add User
        </button>
        </div>
    </div>
 
</div>
<div class="manage-users-container">

    <!-- Search Box -->
    <div class="table-top">
        <form method="GET" class="search-form">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="search"
                    placeholder="Search users..."
                    value="<?php echo htmlspecialchars($search); ?>">
                    
            </div>
        </form>
    </div>
    <!-- Table -->
    <table class="users-table">
        <thead>
            <tr style="background-color: #6fa4d6; border:1px solid #dbe8f5; font-weight:bold;">
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
        <tbody>
        <?php
        if(mysqli_num_rows($result)>0){

            $sn=1;

            while($row=mysqli_fetch_assoc($result)){

                $user_id=$row['user_id'];

                $task=mysqli_query($conn,
                "SELECT COUNT(*) AS total
                FROM tasks
                WHERE user_id='$user_id'");

                $taskCount=mysqli_fetch_assoc($task)['total'];
        ?>
            <tr>
                <td><?php echo $sn++; ?></td>
                <td>
                    <?php
                    echo $row['first_name']." ".$row['last_name'];
                    ?>
                </td>
                <td><?php echo $row['email']; ?></td>
                <td><p style=" border-radius:9px; background-color: #e4ecff; color: #08309e; text-align:center;  "><?php echo $row['department']; ?></p></td>
                <td><p style=" border-radius:9px; background-color: #e4ecff; color: #08309e; text-align:center;  "><?php echo $row['role']; ?></p></td>
                <td><?php echo $taskCount; ?> Tasks</td>
                <td>
                    <?php
                    if($row['status']=="Active"){
                        echo "<span class='badge active' style=' border-radius:9px; background-color: #b4f5c4; color: #008822; text-align:center;'>Active</span>";
                    }else{
                        echo "<span class='badge inactive' style=' border-radius:9px; background-color: #fabcbc; color: #c20909; text-align:center;'>Inactive</span>";
                    }
                    ?>
                </td>
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
        }else{
        ?>
        <tr>
            <td colspan="8" style="text-align:center;">
                No users found.
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!--Assign Task Section-->
</section>
<section id="assign-task-page" class="page">
    <div class="page-title" style="margin-top: 75px; margin-left:250px;">Assign Task</div>
    <div class="page-subtitle" style="margin-left: 250px; color:#666">Create and assign tasks to users</div>
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
<script src="dashboard.js"></script>

</body>
</html>
