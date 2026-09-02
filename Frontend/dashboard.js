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

