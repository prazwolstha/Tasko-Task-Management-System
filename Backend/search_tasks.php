<?php

session_start();


// ==========================================
// CHECK ADMIN LOGIN
// ==========================================

if (!isset($_SESSION['admin_id'])) {

    http_response_code(401);

    echo '
        <tr>
            <td colspan="9"
                style="text-align:center; padding:30px; color:red;">
                Unauthorized access.
            </td>
        </tr>
    ';

    exit();
}


// ==========================================
// DATABASE
// ==========================================

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "tasko"
);


if (!$conn) {

    http_response_code(500);

    echo '
        <tr>
            <td colspan="9"
                style="text-align:center; padding:30px; color:red;">
                Database connection failed.
            </td>
        </tr>
    ';

    exit();
}


// ==========================================
// GET VALUES
// ==========================================

$search = trim($_GET['search'] ?? '');

$priority = trim($_GET['priority'] ?? '');

$status = trim($_GET['status'] ?? '');


// ==========================================
// VALID PRIORITY
// ==========================================

if (
    $priority !== "" &&
    $priority !== "High" &&
    $priority !== "Medium" &&
    $priority !== "Low"
) {

    $priority = "";

}
// ==========================================
// VALID PRIORITY
// ==========================================
if (
    $status !== "" &&
    $status !== "Completed" &&
    $status !== "In Progress" &&
    $status !== "Pending" &&
    $status !== "Overdue"
) {

    $status = "";

}

// ==========================================
// BUILD QUERY
// ==========================================

$sql = "
    SELECT
        tasks.*,
        users.first_name,
        users.last_name
    FROM tasks
    INNER JOIN users
        ON tasks.user_id = users.user_id
    WHERE 1 = 1
";

$params = [];
$types = "";


// ==========================================
// SEARCH
// ==========================================

// If search contains only numbers,
// search exact Task ID
if ($search !== "" && ctype_digit($search)) {

    $sql .= " AND tasks.task_id = ? ";

    $taskId = (int)$search;

    $params[] = $taskId;
    $types .= "i";

}

// Otherwise search task title or employee name
elseif ($search !== "") {

    $sql .= "
        AND (
            tasks.title LIKE ?
            OR users.first_name LIKE ?
            OR users.last_name LIKE ?
            OR CONCAT(
                users.first_name,
                ' ',
                users.last_name
            ) LIKE ?
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
// PRIORITY FILTER
// ==========================================

if ($priority !== "") {

    $sql .= "
        AND tasks.priority = ?
    ";

    $params[] = $priority;

    $types .= "s";
}


// ==========================================
// STATUS FILTER
// ==========================================

if ($status !== "") {

    // Overdue is calculated from due date
    if ($status === "Overdue") {

        $sql .= "
            AND tasks.due_date < CURDATE()
            AND tasks.status != 'Completed'
        ";

    }

    // Normal status
    else {

        $sql .= "
            AND tasks.status = ?
        ";

        $params[] = $status;

        $types .= "s";
    }
}


// ==========================================
// ORDER
// ==========================================

$sql .= "
    ORDER BY tasks.task_id DESC
";


// ==========================================
// PREPARE
// ==========================================

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    echo '
        <tr>
            <td colspan="9"
                style="
                    text-align:center;
                    padding:30px;
                    color:red;
                ">
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

    foreach ($params as $key => &$value) {
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
// NO RESULT
// ==========================================

if (mysqli_num_rows($result) === 0) {

    echo '
        <tr>
            <td colspan="9"
                style="
                    text-align:center;
                    padding:30px;
                ">
                No tasks found.
            </td>
        </tr>
    ';

    exit();
}


// ==========================================
// DISPLAY RESULTS
// ==========================================

$sn = 1;


while ($row = mysqli_fetch_assoc($result)) {

    $name =
        $row['first_name'] .
        " " .
        $row['last_name'];


    $avatar =
        strtoupper(
            substr(
                $row['first_name'],
                0,
                1
            )
        );


    // --------------------------------------
    // PRIORITY
    // --------------------------------------

    if ($row['priority'] === "High") {

        $priorityHtml =
            '<span class="priority high">High</span>';

    } elseif ($row['priority'] === "Medium") {

        $priorityHtml =
            '<span class="priority medium">Medium</span>';

    } else {

        $priorityHtml =
            '<span class="priority low">Low</span>';

    }


    // --------------------------------------
    // STATUS
    // --------------------------------------

    $status = $row['status'];

    if ($status === "Completed") {

        $statusClass = "completed";

    } elseif ($status === "In Progress") {

        $statusClass = "inprogress";

    } else {

        $statusClass = "pending";

    }


    // --------------------------------------
    // DESCRIPTION
    // --------------------------------------

    $description =
        $row['description'] ?? '';

    if (strlen($description) > 40) {

        $description =
            substr($description, 0, 40) . "...";
    }


?>

<tr>

    <!-- ID -->

    <td>
        <?php echo $row['task_id']; ?>
    </td>


    <!-- TASK -->

    <td>

        <div class="task-info">

            <strong>
                <?php
                echo htmlspecialchars(
                    $row['title']
                );
                ?>
            </strong>

            <br>

            <small>
                <?php
                echo htmlspecialchars(
                    $description
                );
                ?>
            </small>

        </div>

    </td>


    <!-- ASSIGNED TO -->

    <td>

        <div class="employee">

            <div class="avatar">

                <?php
                echo $avatar;
                ?>

            </div>

            <div>

                <?php
                echo htmlspecialchars(
                    $name
                );
                ?>

            </div>

        </div>

    </td>


    <!-- PRIORITY -->

    <td>

        <?php
        echo $priorityHtml;
        ?>

    </td>


    <!-- START DATE -->

    <td>

        <?php

        if (!empty($row['start_date'])) {

            echo date(
                "d M Y",
                strtotime(
                    $row['start_date']
                )
            );

        }

        ?>

    </td>


    <!-- DUE DATE -->

    <td>

        <?php

        if (!empty($row['due_date'])) {

            echo date(
                "d M Y",
                strtotime(
                    $row['due_date']
                )
            );

        }

        ?>

    </td>


    <!-- PROGRESS -->

    <td>

        <div class="progress-bar">

            <div
                class="progress-fill"
                style="
                    width:
                    <?php echo (int)$row['progress']; ?>%;
                "
            ></div>

        </div>

        <span>

            <?php
            echo (int)$row['progress'];
            ?>%

        </span>

    </td>


    <!-- STATUS -->

    <td>

        <span class="status <?php
            echo $statusClass;
        ?>">

            <?php
            echo htmlspecialchars(
                $status
            );
            ?>

        </span>

    </td>


    <!-- ACTIONS -->

    <td>

        <a
            href="edit_task.php?id=<?php echo $row['task_id']; ?>"
            class="action-btn edit">

            <i class="fa-solid fa-pen"></i>

        </a>


        <a
            href="../Backend/delete_task_process.php?id=<?php echo $row['task_id']; ?>"
            class="action-btn delete"
            onclick="return confirm('Delete this task?')">

            <i class="fa-solid fa-trash"></i>

        </a>

    </td>

</tr>

<?php

}

?>