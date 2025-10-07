const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");
const createAccBtn = document.getElementById("createAccountBtn");


document.getElementById("showLogin").addEventListener("click", function(i){
    i.preventDefault();
    registerForm.style.display = "none";
    loginForm.style.display = "block";
});


document.getElementById("showRegister").addEventListener("click", function(i){
    i.preventDefault();
    registerForm.style.display = "block";
    loginForm.style.display = "none";
});


