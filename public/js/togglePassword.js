const passwordInput = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");
const togglePasswordIcon = document.getElementById("togglePasswordIcon");

togglePassword.addEventListener("click", function () {
  const type = passwordInput.type === "password" ? "text" : "password";
  passwordInput.type = type;
  // Ganti icon jika ingin, misal dengan emoji
  togglePasswordIcon.textContent = type === "password" ? "🙈" : "👁️";
});
