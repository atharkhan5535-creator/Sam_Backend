const form = document.getElementById("signupForm");
const mobileInput = document.getElementById("mobile");
const mobileError = document.getElementById("mobileError");
const nameError = document.getElementById("nameError");
const passwordError = document.getElementById("passwordError")

const strongPattern =  /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/;

form.addEventListener("submit", function (e) {
e.preventDefault(); 

  const fullname = document.getElementById("fullname").value.trim();
  const mobile = document.getElementById("mobile").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  let isValid = true;

  if (password !== confirmPassword) {
    alert("Passwords do not match!");
    isValid = false;
  }
  if (mobile.length !== 10) {
    mobileError.style.display = "block";
    mobileError.textContent = "Mobile number must be exactly 10 digits.";
    setTimeout(function () {mobileError.style.display = "none"}, 3000);
    isValid = false;
  }
  else {
    mobileError.textContent = "";
  }

  if (fullname.length < 3) {
    nameError.style.display = "block";
    nameError.textContent = "Name must be at least 3 characters.";
    setTimeout(function () {nameError.style.display = "none"}, 3000);
    isValid = false;
  }
  else {
   nameError.textContent = "";
  }

  if (!strongPattern.test(password)) {
    passwordError.style.display = "block";
    passwordError.textContent =
      "Password must be 8+ chars with uppercase, number & symbol.";
      setTimeout(function () {passwordError.style.display = "none"}, 3000);
     isValid = false;
  }else {
  passwordError.textContent = "";
  }


  if (!isValid) return;
  console.log("Form is valid. Ready for API.");

  const userData = {
    fullname,
    mobile,
    email,
    password
  };

  console.log("User Data:", userData);

fetch("http://localhost/sam_api/register.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json"
  },
  body: JSON.stringify(userData)
})
.then(response => response.json())
.then(data => {
  console.log(data);

  if (data.status === "success") {

    // show success message briefly
    alert("Registration successful! Redirecting to home page...");

    form.reset();

    // Redirect after 1 second
    setTimeout(() => {
      window.location.href = "../index.html"; 
      // change path if needed
    }, 1000);

  } else {
    alert(data.message); // show backend error
  }
})

});

// CLINT SIDE VALIDATIONS
// mobile Restrict to numbers only
mobileInput.addEventListener("input", function () {
  this.value = this.value.replace(/\D/g, "");

  if (this.value.length > 10) {
    this.value = this.value.slice(0, 10);
  }
});

function togglePassword(id) {
  const input = document.getElementById(id);
  input.type = input.type === "password" ? "text" : "password";
  console.log("password is :", input.value)
}