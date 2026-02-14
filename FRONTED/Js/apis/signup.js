document.getElementById("signupForm").addEventListener("submit", async function (e) {
  e.preventDefault(); // 🔴 THIS FIXES EVERYTHING

  const name = document.getElementById("fullname").value.trim();
  const phone = document.getElementById("mobile").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  if (password !== confirmPassword) {
    document.getElementById("confirmPasswordError").innerText =
      "Passwords do not match";
    return;
  }

  const payload = {
    name,
    phone,
    email: email || null,
    password,
    salon_id: 1
  };

  try {
    const response = await apiRequest(
      "/api/customers/register",
      "POST",
      payload
    );

    if (response.status === "success") {
      alert("Registration successful");
      window.location.href = "./login.html";
    } else {
      alert(response.message || "Registration failed");
    }

  } catch (err) {
    console.error(err);
    alert("Server error");
  }
});
