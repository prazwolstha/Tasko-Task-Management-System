document.addEventListener("DOMContentLoaded", function () {

    console.log("add_user.js loaded");

    const form = document.getElementById("addUserForm");

    if (!form) {
        console.log("Form not found!");
        return;
    }

    form.addEventListener("submit", function (e) {

        let valid = true;

        const fields = [
            "first_name",
            "last_name",
            "email",
            "phone",
            "department",
            "role",
            "password",
            "status"
        ];

        fields.forEach(function (id) {

            const field = document.getElementById(id);

            if (!field) {
                console.log(id + " not found");
                return;
            }

            const error = field.parentElement.querySelector(".error");

            field.classList.remove("input-error");
            field.classList.remove("input-success");

            if (error) error.innerHTML = "";

            if (field.value.trim() === "") {

                valid = false;

                field.classList.add("input-error");

                if (error) {
                    error.innerHTML = "This field is required.";
                }

            } else {

                field.classList.add("input-success");

            }

        });

        if (!valid) {
            e.preventDefault();
            alert("Please fill all required fields.");
        }

    });

});