const API_BASE = "http://localhost:8000";

document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");

    if(loginForm) {
        loginForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const payload = {
                email: document.getElementById("email").value,
                password: document.getElementById("password").value
            };
            
            try {
                const response = await fetch(`${API_BASE}/api/login`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if(response.ok && data.success) {
                    // Store token if your API returns one
                    if(data.token) {
                        localStorage.setItem('auth_token', data.token);
                    }
                    
                    alert("Login successful! 🎉");
                    window.location.href = "/sign-in"; // Redirect to dashboard
                } else {
                    alert("Login failed: " + (data.message || "Invalid credentials"));
                }
            } catch (error) {
                alert("Error: " + error.message);
            }
        });
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const signupForm = document.getElementById("signupForm");

    if(signupForm) {
        signupForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const payload = {
                name: document.getElementById("name").value,
                email: document.getElementById("email").value,
                password: document.getElementById("password").value,
            };

            try {
                const response = await fetch(`${API_BASE}/api/signup`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if(response.ok && data.success) {
                    if(data.token) {
                        localStorage.setItem('auth_token', data.token);
                    }
                    
                    alert("Sign Up successful! 🎉");
                    window.location.href = "/"; // Redirect to dashboard
                } else {
                    alert("Sign Up Failed: " + (data.message || "Please check your details"));
                }
            } catch (error) {
                alert("Error: " + error.message);
            }
        });
    }
});