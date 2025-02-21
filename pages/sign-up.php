<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <link rel="stylesheet" href="../css/sign-in-up.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    />
  </head>
  <body>
    <div class="sign-up-form-container">
      <form action="" id="sign-up">
        
        <div class="form-title">
          <h1>Create Account</h1>
          <p>Please fill in the fields below</p>
        </div>

        <div class="flex-group">
          <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" />
          </div>

          <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" />
          </div>
        </div>

        <div class="flex-group">
          <div class="form-group">
            <label for="Phone Number">Phone Number</label>
            <input type="text" name="Phone_No" />
          </div>
          <div class="form-group">
            <label for="Email">Email</label>
            <input type="text" name="Email" />
          </div>
        </div>

        <div class="form-group">
          <label for="Password">Password</label>
          <input type="password" name="Password" id="Password" />
          <i class="fa-solid fa-eye eye-icon" id="togglePassword"></i>
        </div>

        <div class="form-group">
          <label for="Password_1">Confirm Password</label>
          <input type="password" name="Password_1" id="Password_1" />
          <i class="fa-solid fa-eye eye-icon" id="togglePassword_1"></i>
        </div>

        <div class="sign-up-btn">
          <button type="submit">Sign Up</button>
        </div>

        <div class="form-last">
          <div class="redirect">
            <p>Already have an account? <a href="sign-in.html">Sign Up</a></p>
          </div>
        </div>
      </form>
    </div>

    <script>
      document
        .getElementById("togglePassword")
        .addEventListener("click", function () {
          var passwordField = document.getElementById("Password");
          var icon = this;

          if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
          } else {
            passwordField.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
          }
        });

      document
        .getElementById("togglePassword_1")
        .addEventListener("click", function () {
          var passwordField = document.getElementById("Password_1");
          var icon = this;

          if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
          } else {
            passwordField.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
          }
        });
    </script>
  </body>
</html>
