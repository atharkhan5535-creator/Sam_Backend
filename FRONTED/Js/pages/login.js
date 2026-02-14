const form = document.getElementById("loginForm");
const emailInput = document.getElementById("email");
const mobileInput = document.getElementById("mobile");

let attempts = 0;
const maxAttempts = 3;

form.addEventListener("submit", function (e) {
  e.preventDefault();

  const email = document.getElementById("email").value.trim();
  const mobile = document.getElementById("mobile").value.trim();
  const password = document.getElementById("password").value;

  const identifier = email || mobile;
// http://localhost/sam_management/
  fetch("http://localhost/sam_api/login.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      identifier,
      password
    })
  })
  .then(res => res.json())
  .then(data => {

    if (data.status === "success") {
      alert("Login successful!");
      window.location.href = "../index.html";
    } else {
      attempts++;
      alert(data.message);
      if (attempts >= maxAttempts) {
        alert("Too many failed attempts!");
        form.querySelector("button").disabled = true;
      }
    }

  })
  .catch(err => console.error(err));
});

// CLINT SIDE VALIDATIONS
emailInput.addEventListener("input", function () {
  if (this.value.length > 0) {
    mobileInput.disabled = true;
  } else {
    mobileInput.disabled = false;
  }
});

mobileInput.addEventListener("input", function () {
  if (this.value.length > 0) {
    emailInput.disabled = true;
  } else {
    emailInput.disabled = false;
  }
});

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