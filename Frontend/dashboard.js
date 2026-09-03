function showPage(pageId, navItem) {

    // Hide all pages
    document.querySelectorAll(".page").forEach(function(page) {
        page.classList.remove("active");
    });


    // Show selected page
    const selectedPage = document.getElementById(pageId);

    if (selectedPage) {
        selectedPage.classList.add("active");
    }


    // Remove active from all sidebar items
    document.querySelectorAll(".nav-item").forEach(function(item) {
        item.classList.remove("active");
    });


    // Add active to clicked sidebar item
    if (navItem) {
        navItem.classList.add("active");
    }


    // Resize charts after Reports becomes visible
    if (pageId === "reports-page") {

        setTimeout(function() {

            if (taskStatusChart) {
                taskStatusChart.resize();
            }

            if (employeePerformanceChart) {
                employeePerformanceChart.resize();
            }

            if (completedTrendChart) {
                completedTrendChart.resize();
            }

        }, 100);

    }

}


// PASSWORD
function togglePassword(id, icon) {

    const input = document.getElementById(id);

    if (!input) {
        return;
    }

    if (input.type === "password") {

        input.type = "text";

        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");

    } else {

        input.type = "password";

        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");

    }
}

// ==========================================
// MANAGE USERS SEARCH
// ==========================================
// ==========================================
// MANAGE USERS SEARCH
// ==========================================

function searchUsers(event) {

    // Stop normal form submission
    if (event) {
        event.preventDefault();
    }


    // ------------------------------------------
    // GET ELEMENTS
    // ------------------------------------------

    const searchInput =
        document.getElementById("userSearch");

    const tableBody =
        document.getElementById("usersTableBody");

    const departmentFilter =
        document.getElementById("userDepartment");

    const roleFilter =
        document.getElementById("userRole");


    // ------------------------------------------
    // SAFETY CHECK
    // ------------------------------------------

    if (
        !searchInput ||
        !tableBody ||
        !departmentFilter ||
        !roleFilter
    ) {

        console.error("Search elements not found.");

        return false;
    }


    // ------------------------------------------
    // GET VALUES
    // ------------------------------------------

    const searchValue =
        searchInput.value.trim();

    const department =
        departmentFilter.value;

    const role =
        roleFilter.value;


    // ------------------------------------------
    // SHOW SEARCHING
    // ------------------------------------------

    tableBody.innerHTML = `
        <tr>

            <td
                colspan="8"
                style="
                    text-align:center;
                    padding:30px;
                "
            >
                Searching...
            </td>

        </tr>
    `;


    // ------------------------------------------
    // CREATE URL
    // ------------------------------------------

    const url =
        "../Backend/search_users.php?search=" +
        encodeURIComponent(searchValue) +

        "&department=" +
        encodeURIComponent(department) +

        "&role=" +
        encodeURIComponent(role);


    console.log("Search URL:", url);


    // ------------------------------------------
    // AJAX REQUEST
    // ------------------------------------------

    fetch(url)

        .then(function(response) {

            if (!response.ok) {

                throw new Error(
                    "HTTP Error: " +
                    response.status
                );

            }

            return response.text();

        })


        // --------------------------------------
        // SHOW RESULTS
        // --------------------------------------

        .then(function(html) {

            tableBody.innerHTML = html;

        })


        // --------------------------------------
        // ERROR
        // --------------------------------------

        .catch(function(error) {

            console.error(
                "Search error:",
                error
            );

            tableBody.innerHTML = `
                <tr>

                    <td
                        colspan="8"
                        style="
                            text-align:center;
                            padding:30px;
                            color:red;
                        "
                    >
                        Unable to search users.
                    </td>

                </tr>
            `;

        });


    return false;
}
// ==========================================
// DEPARTMENT FILTER
// ==========================================

const departmentFilter =
    document.getElementById("userDepartment");

if (departmentFilter) {

    departmentFilter.addEventListener(
        "change",
        function() {

            searchUsers();

        }
    );

}


// ==========================================
// ROLE FILTER
// ==========================================

const roleFilter =
    document.getElementById("userRole");

if (roleFilter) {

    roleFilter.addEventListener(
        "change",
        function() {

            searchUsers();

        }
    );

}
// ==========================================
// MANAGE TASKS AJAX SEARCH
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    const taskSearchForm =
        document.getElementById("taskSearchForm");

    const taskSearchInput =
        document.getElementById("taskSearch");

    const taskPriority =
        document.getElementById("taskPriority");
    
    const taskStatus =
    document.getElementById("taskStatus");

    const tasksTableBody =
        document.getElementById("tasksTableBody");

    
    // Stop if elements don't exist
    if (
        !taskSearchForm ||
        !taskSearchInput ||
        !taskPriority ||
        !taskStatus ||
        !tasksTableBody
    ) {
        return;
    }


    // ======================================
    // SUBMIT WITH ENTER
    // ======================================

    taskSearchForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();

            searchTasks();

        }
    );


    // ======================================
    // PRIORITY CHANGE
    // ======================================

    taskPriority.addEventListener(
        "change",
        function () {
            searchTasks();
        }
    );
    taskStatus.addEventListener(
    "change",
    function () {
        searchTasks();
    }
);

    // ======================================
    // SEARCH FUNCTION
    // ======================================
    function searchTasks() {

        const search =
            taskSearchInput.value.trim();

        const priority =
            taskPriority.value;

        const status =
            taskStatus.value;

        // Show loading
        tasksTableBody.innerHTML = `
            <tr>
                <td
                    colspan="9"
                    style="
                        text-align:center;
                        padding:30px;
                    "
                >
                    Searching...

                </td>

            </tr>
        `;

        // Build URL
        const url =
            "../Backend/search_tasks.php?search=" +
            encodeURIComponent(search) +
            "&priority=" +
            encodeURIComponent(priority) +
             "&status=" +
            encodeURIComponent(status);

        // AJAX request
        fetch(url)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error(
                        "HTTP Error: " +
                        response.status
                    );
                }
                return response.text();
            })

            .then(function (html) {
                tasksTableBody.innerHTML = html;

            })

            .catch(function (error) {
                console.error(
                    "Task search error:",
                    error
                );

                tasksTableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="9"
                            style="
                                text-align:center;
                                padding:30px;
                                color:red;
                            "
                        >
                            Unable to search tasks.
                        </td>
                    </tr>
                `;
            });
    }
});

// ==========================================
// LEAVE REQUEST STATUS FILTER
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    const leaveStatus =
        document.getElementById("leaveStatus");

    const leaveTableBody =
        document.getElementById("leaveTableBody");


    // Stop if Leave Requests page is not available
    if (!leaveStatus || !leaveTableBody) {
        return;
    }


    // ======================================
    // FILTER WHEN STATUS CHANGES
    // ======================================

    leaveStatus.addEventListener(
        "change",
        function () {

            filterLeaveRequests(
                this.value
            );

        }
    );


    // ======================================
    // FILTER FUNCTION
    // ======================================
    function filterLeaveRequests(status) {

        const rows =
            leaveTableBody.querySelectorAll(
                "tr[data-leave-status]"
            );

        let found = false;

        rows.forEach(function (row) {
            const rowStatus =
                row.getAttribute(
                    "data-leave-status"
                );

            // Show all
            if (status === "") {
                row.style.display = "";
                found = true;
            }
            // Show selected status
            else if (rowStatus === status) {
                row.style.display = "";
                found = true;

            }
            // Hide other statuses
            else {
                row.style.display = "none";
            }
        });

        // ==================================
        // REMOVE OLD NO-RESULT MESSAGE
        // ==================================
        const oldMessage =
            document.getElementById(
                "leaveFilterNoResult"
            );

        if (oldMessage) {
            oldMessage.remove();
        }

        // ==================================
        // NO RESULT
        // ==================================
        if (!found) {
            const noResultRow =
                document.createElement("tr");

            noResultRow.id =
                "leaveFilterNoResult";

            noResultRow.innerHTML = `
                <td
                    colspan="8"
                    style="
                        text-align:center;
                        padding:30px;
                    "
                >
                    No ${status.toLowerCase()}
                    leave requests found.
                </td>
            `;

            leaveTableBody.appendChild(
                noResultRow
            );
        }
    }
});