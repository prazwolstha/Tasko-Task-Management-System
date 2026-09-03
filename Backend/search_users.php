<?php
session_start();
// ==========================================
// CHECK ADMIN LOGIN
// ==========================================

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo '
        <tr>
            <td colspan="8"
                style="text-align:center; padding:30px; color:red;">
                Unauthorized access.
            </td>
        </tr>
    ';
    exit();
}

// ==========================================
// DATABASE CONNECTION
// ==========================================
$conn = mysqli_connect("localhost","root", "","tasko");

if (!$conn) {
    http_response_code(500);
    echo '
        <tr>
            <td colspan="8"
                style="text-align:center; padding:30px; color:red;">
                Database connection failed.
            </td>
        </tr>
    ';
    exit();
}
// ==========================================
// GET SEARCH VALUE
// ==========================================

$search = trim($_GET['search'] ?? '');

$department = trim($_GET['department'] ?? '');

$role = trim($_GET['role'] ?? '');


// ==========================================
// BUILD QUERY
// ==========================================

$sql = "
    SELECT
        user_id,
        first_name,
        last_name,
        email,
        department,
        role,
        status

    FROM users

    WHERE 1=1
";


$params = [];

$types = "";


// ==========================================
// SEARCH
// ==========================================

// Numeric value = exact User ID
if ($search !== '' && ctype_digit($search)) {

    $sql .= "
        AND user_id = ?
    ";

    $userId = (int)$search;

    $params[] = $userId;

    $types .= "i";
}


// Text = name or email
elseif ($search !== '') {

    $sql .= "
        AND (
            first_name LIKE ?
            OR last_name LIKE ?
            OR CONCAT(
                first_name,
                ' ',
                last_name
            ) LIKE ?
            OR email LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ssss";
}


// ==========================================
// DEPARTMENT FILTER
// ==========================================

if ($department !== '') {

    $sql .= "
        AND department = ?
    ";

    $params[] = $department;

    $types .= "s";
}


// ==========================================
// ROLE FILTER
// ==========================================

if ($role !== '') {

    $sql .= "
        AND role = ?
    ";

    $params[] = $role;

    $types .= "s";
}


// ==========================================
// ORDER
// ==========================================

$sql .= "
    ORDER BY user_id DESC
";


// ==========================================
// PREPARE
// ==========================================

$stmt = mysqli_prepare(
    $conn,
    $sql
);

if (!$stmt) {

    echo '
        <tr>
            <td
                colspan="8"
                style="
                    text-align:center;
                    padding:30px;
                    color:red;
                "
            >
                Search preparation failed.
            </td>
        </tr>
    ';

    exit();
}


// ==========================================
// BIND PARAMETERS
// ==========================================

if (!empty($params)) {

    $bindValues = [];

    $bindValues[] = $types;

    foreach ($params as &$value) {

        $bindValues[] = &$value;

    }

    call_user_func_array(
        [$stmt, 'bind_param'],
        $bindValues
    );
}


// ==========================================
// EXECUTE
// ==========================================

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
// ==========================================
// EXECUTE
// ==========================================

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);

// ==========================================
// EXECUTE
// ==========================================

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

// ==========================================
// NO USERS FOUND
// ==========================================

if (mysqli_num_rows($result) == 0) {

    echo '
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
    ';

    exit();
}


// ==========================================
// DISPLAY USERS
// ==========================================

$sn = 1;


while ($row = mysqli_fetch_assoc($result)) {


    // --------------------------------------
    // USER NAME
    // --------------------------------------

    $name =
        $row['first_name'] .
        " " .
        $row['last_name'];


    // --------------------------------------
    // TASK COUNT
    // --------------------------------------

    $userId = (int)$row['user_id'];


    $taskQuery = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM tasks
         WHERE user_id='$userId'"
    );


    $taskCount = 0;


    if ($taskQuery) {

        $taskData =
            mysqli_fetch_assoc($taskQuery);

        $taskCount =
            (int)$taskData['total'];

    }


    // --------------------------------------
    // STATUS
    // --------------------------------------

    if ($row['status'] === "Active") {

        $statusHtml = '
            <span
                class="badge active"
                style="
                    border-radius:9px;
                    background-color:#b4f5c4;
                    color:#008822;
                    padding:5px 10px;
                ">
                Active
            </span>
        ';

    } else {

        $statusHtml = '
            <span
                class="badge inactive"
                style="
                    border-radius:9px;
                    background-color:#fabcbc;
                    color:#c20909;
                    padding:5px 10px;
                ">
                Inactive
            </span>
        ';

    }

    // --------------------------------------
    // OUTPUT ROW
    // --------------------------------------

?>

<tr>
    <!-- ID -->
    <td>
        <?php echo $row['user_id']; ?>
    </td>

    <!-- NAME -->
     <td>
        <?php echo htmlspecialchars($name); ?>
    </td>

    <!-- EMAIL -->
    <td>
        <?php echo htmlspecialchars($row['email']); ?>
    </td>

    <!-- DEPARTMENT -->
    <td>
        <p style="border-radius:9px;background-color:#e4ecff;color:#08309e;text-align:center;padding:5px;margin:0;">
            <?php echo htmlspecialchars( 
                $row['department']
            );
            ?>
        </p>
    </td>

    <!-- ROLE -->
    <td>
        <p style="border-radius:9px;background-color:#e4ecff;color:#08309e;text-align:center;padding:5px;margin:0;">
            <?php echo htmlspecialchars(
                $row['role']
            );
            ?>
        </p>
    </td>

    <!-- TASKS -->
    <td>
        <?php echo $taskCount; ?> Tasks
    </td>

    <!-- STATUS -->
    <td>
        <?php echo $statusHtml; ?>
    </td>

    <!-- ACTIONS -->
    <td>
        <a href="../Frontend/edit_user.php?id=<?php echo $row['user_id']; ?>"class="edit-btn">
            <i class="fa-solid fa-pen-to-square"></i>
            Edit
        </a>

        <a href="../Backend/delete_user.php?id=<?php echo $row['user_id']; ?>"class="delete-btn" onclick="return confirm('Delete this user?')">
            <i class="fa-solid fa-trash"></i>
            Delete
        </a>
    </td>
</tr>

<?php
}
?>