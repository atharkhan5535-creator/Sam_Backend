// bridge between frontend & backend.

const API_BASE_URL = "http://localhost/Sam-Backend/BACKEND/public/index.php";

async function apiRequest(endpoint, method = "GET", data = null) {
  const options = {
    method,
    headers: {
      "Content-Type": "application/json"
    }
  };

  if (data) {
    options.body = JSON.stringify(data);
  }

  const response = await fetch(API_BASE_URL + endpoint, options);
  return response.json();
}