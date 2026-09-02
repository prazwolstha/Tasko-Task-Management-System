    <?php

    $conn = mysqli_connect("localhost","root","","tasko");

    if(!$conn){
        die("Connection Failed");
    }

    if(isset($_POST['addUser'])){

        $first_name = $_POST['first_name'];
        $last_name  = $_POST['last_name'];
        $email      = $_POST['email'];
        $phone      = $_POST['phone'];
        $department = $_POST['department'];
        $role       = $_POST['role'];

        // Securely hash the password
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $status = $_POST['status'];

        // Check if email already exists
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$email'");

        if(mysqli_num_rows($check) > 0){

            echo "<script>
                    alert('Email already exists!');
                    history.back();
                </script>";

            exit();
        }

        $sql = "INSERT INTO users
                (first_name,last_name,email,phone,department,role,password,status)
                VALUES
                ('$first_name','$last_name','$email','$phone','$department','$role','$password','$status')";

        if(mysqli_query($conn,$sql)){

            echo "<script>
                    alert('User Added Successfully!');
                    window.location='../Frontend/dashboard.php';
                </script>";

        }else{

            echo "Error : ".mysqli_error($conn);

        }

    }

    ?>