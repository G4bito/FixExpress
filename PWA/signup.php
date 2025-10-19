<?php include('./dist/database/loginserver.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signup</title>
<style>
* {margin: 0; padding: 0; box-sizing: border-box;}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  overflow: hidden;
}

.signup-container {
  width: 100%;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  background: linear-gradient(135deg, #1a1a1a 0%, #2a2520 100%);
}

.signup-form-container {
  position: relative;
  z-index: 10;
  background: rgba(40, 40, 40, 0.95);
  padding: 50px 60px;
  border-radius: 20px;
  width: 450px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 30px 60px rgba(0,0,0,0.6);
}

.signup-form-container h1 {
  font-size: 28px;
  text-align: center;
  margin-bottom: 30px;
  font-weight: 300;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #fff;
  padding-bottom: 10px;
  display: inline-block;
  width: 100%;
  color: #fff;
}

/* Autofill fix */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
textarea:-webkit-autofill,
select:-webkit-autofill {
  -webkit-box-shadow: 0 0 0 1000px rgba(40, 40, 40, 0.95) inset !important;
  -webkit-text-fill-color: #ffffff !important;
  caret-color: #ffffff !important;
  transition: background-color 9999s ease-in-out 0s !important;
}

/* Worker toggle */
.worker-toggle { text-align: center; margin-bottom: 30px; }
.worker-toggle button {
  background: transparent;
  border: 2px solid #d97f3e;
  color: #d97f3e;
  padding: 10px 20px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.3s;
  font-weight: 500;
}
.worker-toggle button:hover { background: #d97f3e; color: #fff; }

/* Floating label system */
.form-group {
  position: relative;
  margin-bottom: 30px;
}
.form-group input,
.form-group select,
.form-group textarea {
  background: transparent;
  border: none;
  border-bottom: 1px solid rgba(255,255,255,0.3);
  color: #fff;
  padding: 12px 0 8px 0;
  font-size: 14px;
  width: 100%;
  outline: none;
}
.form-group label {
  position: absolute;
  top: 12px;
  left: 0;
  color: rgba(255,255,255,0.6);
  font-size: 14px;
  pointer-events: none;
  transition: 0.25s ease;
}

/* Move label up on focus or when filled */
.form-group input:focus + label,
.form-group input:not(:placeholder-shown) + label,
.form-group textarea:focus + label,
.form-group textarea:not(:placeholder-shown) + label,
.form-group select:focus + label,
.form-group select:valid + label {
  top: -10px;
  font-size: 11px;
  color: #d97f3e;
}

/* Name group layout */
.name-group {
  display: flex;
  gap: 25px;
  margin-bottom: 30px;
}
.name-group .form-group { flex: 1; margin-bottom: 0; }

/* Dropdown */
.form-group select {
  cursor: pointer;
  appearance: none;
  background: transparent;
}
.form-group select option {
  background: #2a2a2a;
  color: #fff;
}

/* Textarea */
.form-group textarea {
  resize: vertical;
  min-height: 80px;
  border: 1px solid rgba(255,255,255,0.3);
  border-radius: 5px;
}
.form-group textarea:focus { border-color: #d97f3e; }

/* Terms, buttons, etc. */
.terms {
  display: flex;
  align-items: flex-start;
  margin-bottom: 30px;
  font-size: 12px;
  color: rgba(255,255,255,0.7);
}
.terms input[type="checkbox"] {
  width: 16px; height: 16px; margin-right: 10px; margin-top: 2px;
  accent-color: #d97f3e; cursor: pointer;
}
.terms a { color: #4a9eff; text-decoration: none; }
.terms a:hover { text-decoration: underline; }

.btn-create {
  width: 100%;
  padding: 14px;
  background: linear-gradient(to bottom, #d97f3e, #1f1711);
  border: none;
  border-radius: 25px;
  color: white;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  text-transform: capitalize;
}
.btn-create:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(217,127,62,0.4);
  background: linear-gradient(to bottom, #e88d4d, #151010);
}

.login-link {
  text-align: center;
  color: #888;
  font-size: 13px;
  margin-top: 10px;
  margin-bottom: 10px;
}
.login-link a {
  color: #d97f3e;
  text-decoration: none;
  font-weight: 500;
}
.login-link a:hover { text-decoration: underline; }

.hidden {display: none;}
.worker-badge {
  display: inline-block;
  background: #d97f3e;
  color: #fff;
  padding: 3px 10px;
  border-radius: 10px;
  font-size: 11px;
  margin-left: 10px;
  font-weight: 500;
}
.password-message { font-size: 11px; margin-top: 5px; display: block; }
</style>
</head>
<body>
<div id="signupPage" class="signup-container">
  <div class="signup-form-container fade-in">
    <h1 id="formTitle">Create an account</h1>

    <div class="worker-toggle">
      <button type="button" id="toggleWorkerBtn">🔧 Be a Worker</button>
    </div>

    <!-- Regular Signup -->
    <form id="signupForm" method="POST" action="">
      <input type="hidden" name="user_type" value="regular">
      <div class="name-group">
        <div class="form-group">
          <input type="text" name="first_name" id="first_name" placeholder=" " required>
          <label for="first_name">First Name</label>
        </div>
        <div class="form-group">
          <input type="text" name="last_name" id="last_name" placeholder=" " required>
          <label for="last_name">Last Name</label>
        </div>
      </div>
      <div class="form-group">
        <input type="email" name="email" id="email" placeholder=" " required>
        <label for="email">Email</label>
      </div>
      <div class="form-group">
        <input type="text" name="username" id="username" placeholder=" " required>
        <label for="username">Username</label>
      </div>
      <div class="form-group">
        <input type="password" name="password" id="password" placeholder=" " required>
        <label for="password">Password</label>
      </div>
      <div class="form-group">
        <input type="password" name="confirmPassword" id="confirmPassword" placeholder=" " required>
        <label for="confirmPassword">Confirm Password</label>
        <small class="password-message"></small>
      </div>
      <div class="terms">
        <input type="checkbox" name="terms" required>
        <label>I agree to the <a href="">Terms and Privacy</a></label>
      </div>
      <button type="submit" name="signup" class="btn-create">Create Account</button>
    </form>

    <!-- Worker Signup -->
    <form id="workerSignupForm" method="POST" action="" class="hidden">
      <input type="hidden" name="user_type" value="worker">
      <div class="name-group">
        <div class="form-group">
          <input type="text" name="first_name" id="w_first_name" placeholder=" " required>
          <label for="w_first_name">First Name</label>
        </div>
        <div class="form-group">
          <input type="text" name="last_name" id="w_last_name" placeholder=" " required>
          <label for="w_last_name">Last Name</label>
        </div>
      </div>
      <div class="form-group">
        <input type="email" name="email" id="w_email" placeholder=" " required>
        <label for="w_email">Email</label>
      </div>
      <div class="form-group">
        <input type="tel" name="contact" id="w_contact" placeholder=" " required pattern="^09\d{9}$" maxlength="11">
        <label for="w_contact">Contact (e.g. 09123456789)</label>
      </div>
      <div class="form-group">
        <input type="text" name="username" id="w_username" placeholder=" " required>
        <label for="w_username">Username</label>
      </div>
      <div class="form-group">
        <input type="text" name="address" id="w_address" placeholder=" " required>
        <label for="w_address">Address</label>
      </div>
      <div class="form-group"> 
            <select name="service_id" required> 
                <option value="">Select Service Type</option> 
                <option value="1">Plumbing</option> 
                <option value="2">Electrical Repair</option> 
                <option value="3">Auto Repair</option> 
                <option value="4">Wellness</option> 
                <option value="5">Repair and Technical Support</option> 
                <option value="6">Pets</option> 
            </select> 
        </div>
        <div class="form-group"> 
            <textarea name="bio" placeholder="Tell us about your skills and experience..." required maxlength="300"></textarea> 
        </div>
      <div class="form-group">
        <input type="password" name="password" id="w_password" placeholder=" " required>
        <label for="w_password">Password</label>
      </div>
      <div class="form-group">
        <input type="password" name="confirmPassword" id="w_confirmPassword" placeholder=" " required>
        <label for="w_confirmPassword">Confirm Password</label>
        <small class="password-message"></small>
      </div>
      <div class="terms">
        <input type="checkbox" name="terms" required>
        <label>I agree to the <a href="">Terms and Privacy</a></label>
      </div>
      <button type="submit" name="signup_worker" class="btn-create">Apply as Worker</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="login.php">Log in</a>
    </div>
  </div>
</div>

<script>
const toggleBtn = document.getElementById('toggleWorkerBtn');
const regularForm = document.getElementById('signupForm');
const workerForm = document.getElementById('workerSignupForm');
const formTitle = document.getElementById('formTitle');

let isWorkerMode = false;

toggleBtn.addEventListener('click', () => {
  isWorkerMode = !isWorkerMode;
  if (isWorkerMode) {
    regularForm.classList.add('hidden');
    workerForm.classList.remove('hidden');
    formTitle.innerHTML = 'Apply as a Worker <span class="worker-badge">WORKER</span>';
    toggleBtn.textContent = '👤 Regular Signup';
  } else {
    workerForm.classList.add('hidden');
    regularForm.classList.remove('hidden');
    formTitle.textContent = 'Create an account';
    toggleBtn.textContent = '🔧 Be a Worker';
  }
});

// Password matching
function setupPasswordValidation(form) {
  const password = form.querySelector('input[name="password"]');
  const confirmPassword = form.querySelector('input[name="confirmPassword"]');
  const message = form.querySelector('.password-message');

  confirmPassword.addEventListener('input', () => {
    if (confirmPassword.value === "") {
      message.textContent = "";
      confirmPassword.style.borderColor = "";
      return;
    }

    if (password.value === confirmPassword.value) {
      message.textContent = "✅ Passwords match";
      message.style.color = "green";
      confirmPassword.style.borderColor = "green";
    } else {
      message.textContent = "❌ Passwords do not match";
      message.style.color = "red";
      confirmPassword.style.borderColor = "red";
    }
  });
}
setupPasswordValidation(regularForm);
setupPasswordValidation(workerForm);
</script>
</body>
</html>
