function togglePassword(id, icon){

    let input = document.getElementById(id);

    if(input.type==="password"){

        input.type="text";

        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");

    }else{

        input.type="password";

        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");

    }

}

const newPassword=document.getElementById("newPassword");
const confirmPassword=document.getElementById("confirmPassword");

confirmPassword.addEventListener("keyup",function(){

    if(confirmPassword.value==""){

        confirmPassword.style.borderColor="#ccc";

    }
    else if(newPassword.value==confirmPassword.value){

        confirmPassword.style.borderColor="green";

    }
    else{

        confirmPassword.style.borderColor="red";

    }

});