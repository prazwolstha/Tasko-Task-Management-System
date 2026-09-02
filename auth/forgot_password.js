const emailInput = document.querySelector("input[type='email']");

emailInput.addEventListener("focus",function(){

    this.style.boxShadow="0 0 8px rgba(37,99,235,.25)";

});

emailInput.addEventListener("blur",function(){

    this.style.boxShadow="none";

});