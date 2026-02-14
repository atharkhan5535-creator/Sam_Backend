document.getElementById("loginForm").addEventListener("submit", async function (e) {
  e.preventDefault(); // 🔴 CRITICAL

  const email = document.getElementById("email").value.trim();
  const phone = document.getElementById("mobile").value.trim();
  const password = document.getElementById("password").value;

  if (!email && !phone) {
    alert("Please enter email or mobile number");
    return;
  }

  if (!password) {
    alert("Password is required");
    return;
  }

  const payload = {
    login_type: "CUSTOMER", // 🔴 REQUIRED BY BACKEND
    email: email || null,
    phone: phone || null,
    password: password,
    salon_id: 1 // 🔴 REQUIRED
  };

  try {
    const response = await apiRequest(
      "/api/auth/login",
      "POST",
      payload
    );

    if (response.status === "success") {
      // Store tokens
      localStorage.setItem("access_token", response.data.access_token);
      localStorage.setItem("refresh_token", response.data.refresh_token);
      localStorage.setItem("expires_in", response.data.expires_in);

      alert("Login successful");

      // Redirect (change later)
      window.location.href = "./dashboard.html";
    } else {
      alert(response.message || "Invalid credentials");
    }

  } catch (err) {
    console.error(err);
    alert("Server error");
  }
});
