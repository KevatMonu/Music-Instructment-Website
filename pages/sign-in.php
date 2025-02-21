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
    <div class="sign-in-form-container">
      <form action="" id="sign-in">
        <div class="form-title">
          <h1>Sign in</h1>
          <p>If you have an account with us, please sign in.</p>
        </div>

        <div class="form-group">
          <label for="Email">Email</label>
          <input type="text" name="Email" />
        </div>

        <div class="form-group">
          <label for="Password">Password</label>
          <input type="password" name="Password" id="Password" />
          <i class="fa-solid fa-eye eye-icon" id="togglePassword"></i>
        </div>


        <div class="sign-up-btn">
          <button type="submit">Sign in</button>
        </div>

        <div class="form-last">
          <div class="redirect">
            <p>Don't have an account? <a href="../pages/sign-up.html">Sign Up</a> </p>
          </div>
          <div class="forgot-pass">
            <a href="../pages/forgot-password.html">Forgot your password</a>
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


    </script>
  </body>
</html>
