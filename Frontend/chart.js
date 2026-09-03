// ==========================================
// TASko REPORT CHARTS
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    // ======================================
    // TASK STATUS DOUGHNUT CHART
    // ======================================

    const taskStatusCanvas =
        document.getElementById("taskStatusChart");

    if (taskStatusCanvas && window.taskReportData) {

        new Chart(taskStatusCanvas, {

            type: "doughnut",

            data: {
                labels: [
                    "Completed",
                    "In Progress",
                    "Pending",
                    "Overdue"
                ],

                datasets: [{
                    data: [
                        window.taskReportData.completed,
                        window.taskReportData.inProgress,
                        window.taskReportData.pending,
                        window.taskReportData.overdue
                    ],

                    borderWidth: 1
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        position: "bottom"
                    }
                }
            }

        });

    }


    // ======================================
    // EMPLOYEE PERFORMANCE BAR CHART
    // ======================================

    const employeeCanvas =
        document.getElementById("employeePerformanceChart");

    if (employeeCanvas && window.employeeReportData) {

        new Chart(employeeCanvas, {

            type: "bar",

            data: {

                labels: window.employeeReportData.names,

                datasets: [{

                    label: "Completed Tasks",

                    data: window.employeeReportData.completed,

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {
                            stepSize: 1
                        }

                    }

                },

                plugins: {

                    legend: {
                        display: true
                    }

                }

            }

        });

    }


    // ======================================
    // COMPLETED TASK TREND
    // ======================================

    const trendCanvas =
        document.getElementById("completedTrendChart");

    if (trendCanvas && window.trendReportData) {

        new Chart(trendCanvas, {

            type: "line",

            data: {

                labels: window.trendReportData.labels,

                datasets: [{

                    label: "Completed Tasks",

                    data: window.trendReportData.values,

                    borderWidth: 2,

                    tension: 0.3,

                    fill: false

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {
                            stepSize: 1
                        }

                    }

                },

                plugins: {

                    legend: {
                        display: true
                    }

                }

            }

        });

    }

});