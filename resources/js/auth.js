const API_BASE = "http://localhost:8000"

document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");

    if(loginForm) {

        loginForm.addEventListener("submit", async (e) => {

            e.preventDefault();

            const payload = {
                email: document.getElementById("email").value,
                password: document.getElementById("password").value
            };
            
            const response = await fetch(`${API_BASE}/api/login`,{

                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if(response.ok) {

                alert("Login successful! 🎉");
                // You can redirect the user or perform other actions here
                window.location.href = "/"; // Example redirect
            } else {

                alert("Login failed: " + data.message);

            }
        });
    }
})

document.addEventListener("DOMContentLoaded", () => {

    const signupForm = document.getElementById("signupForm");

    if(signupForm) {

        signupForm.addEventListener("submit", async (e) => {

            e.preventDefault();

            const payload = {
                name: document.getElementById("name").value,
                email: document.getElementById("email").value,
                password: document.getElementById("password").value
            };

            const response = await fetch(`${API_BASE}/api/register`,{

                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if(response.ok) {

                alert("Sign Up successful! 🎉");
                // You can redirect the user or perform other actions here
                window.location.href = "/sign-up"; // Example redirect
            } else {

                alert("Sign Up Failed: " + data.message);

            }
        });
    }
})


