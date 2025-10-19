<?php include('./dist/database/loginserver.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <style>
        * { 
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
        /* Fix Chrome white background on autofill */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        textarea:-webkit-autofill,
        select:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px rgba(40, 40, 40, 0.95) inset !important; /* matches .signup-form-container background */
            -webkit-text-fill-color: #ffffff !important; /* make text white */
            caret-color: #ffffff !important;
            transition: background-color 9999s ease-in-out 0s !important; /* prevent white flash */
}

        .worker-toggle {
            text-align: center;
            margin-bottom: 30px;
        }

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

        .worker-toggle button:hover {
            background: #d97f3e;
            color: #fff;
        }

        .name-group {
            display: flex;
            gap: 25px;
            margin-bottom: 30px;
        }

        .name-group .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .name-group input {
            background: transparent;
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            padding: 10px 0;
            font-size: 14px;
            width: 100%;
            outline: none;
            transition: border-color 0.3s;
        }

        .name-group input:focus {
            border-bottom-color: #d97f3e;
        }

        .name-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
        }
        
        .form-group {
            margin-bottom: 30px;
            position: relative;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            background: transparent;
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            padding: 10px 30px 10px 0;
            font-size: 14px;
            width: 100%;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-group select {
            cursor: pointer;
            padding-right: 20px;
        }

        .form-group select option {
            background: #2a2a2a;
            color: #fff;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-bottom-color: #d97f3e;
        }

        .form-group textarea:focus {
            border-color: #d97f3e;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
        }

        .form-group i {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }

        .terms {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }

        .terms input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-right: 10px;
            margin-top: 2px;
            accent-color: #d97f3e;
            cursor: pointer;
        }

        .terms a {
            color: #4a9eff;
            text-decoration: none;
        }

        .terms a:hover {
            text-decoration: underline;
        }

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
            box-shadow: 0 10px 25px rgba(217, 127, 62, 0.4);
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

        .login-link a:hover {
            text-decoration: underline;
        }

        .icon {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: rgba(255, 255, 255, 0.5);
        }

        .error-message {
            color: #ff6b6b;
            font-size: 11px;
            margin-top: 5px;
            display: block;
        }

        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hidden {
            display: none;
        }

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
        
    </style>
</head>
<body>
    <div id="signupPage" class="signup-container">
        <div class="signup-form-container fade-in">
            <h1 id="formTitle">Create an account</h1>
            
            <div class="worker-toggle">
                <button type="button" id="toggleWorkerBtn">🔧 Be a Worker</button>
            </div>

            <!-- Regular User Signup Form -->
            <form id="signupForm" class="signup-form" method="POST" action="">
    <input type="hidden" name="user_type" value="regular">

    <div class="name-group">
        <div class="form-group">
            <input type="text" name="first_name" placeholder="First Name" required maxlength="30" pattern="[A-Za-z\s]+" title="Letters only">
        </div>
        <div class="form-group">
            <input type="text" name="last_name" placeholder="Last Name" required maxlength="30" pattern="[A-Za-z\s]+" title="Letters only">
        </div>
    </div>

    <div class="form-group">
        <input type="email" name="email" placeholder="Email" required maxlength="50">
    </div>

    <div class="form-group">
        <input type="text" name="username" placeholder="Username" required maxlength="20" pattern="[A-Za-z0-9_]+" title="Letters, numbers, and underscores only">
    </div>

    <div class="form-group">
        <input type="password" name="password" placeholder="Password" required minlength="8" maxlength="30">
    </div>

    <div class="form-group">
        <input type="password" name="confirmPassword" placeholder="Confirm Password" required minlength="8" maxlength="30">
        <small class="password-message"></small>
    </div>

    <div class="terms">
        <input type="checkbox" name="terms" required>
        <label>I agree to the <a href="">Terms and Privacy</a></label>
    </div>
    
    <button type="submit" name="signup" class="btn-create">Create Account</button>
</form>


            <!-- Worker Signup Form -->
            <form id="workerSignupForm" class="signup-form hidden" method="POST" action="">
    <input type="hidden" name="user_type" value="worker">

    <div class="name-group">
        <div class="form-group">
            <input type="text" name="first_name" placeholder="First Name" required maxlength="30" pattern="[A-Za-z\s]+" title="Letters only">
        </div>
        <div class="form-group">
            <input type="text" name="last_name" placeholder="Last Name" required maxlength="30" pattern="[A-Za-z\s]+" title="Letters only">
        </div>
    </div>

    <div class="form-group">
        <input type="email" name="email" placeholder="Email" required maxlength="50">
    </div>

    <div class="form-group">
        <input type="tel" name="contact" placeholder="Contact (e.g. 09123456789)" required pattern="^09\d{9}$" maxlength="11" title="Must start with 09 and be 11 digits long">
    </div>

    <div class="form-group">
        <input type="text" name="username" placeholder="Username" required maxlength="20" pattern="[A-Za-z0-9_]+" title="Letters, numbers, and underscores only">
    </div>

    <div class="form-group">
        <input type="text" name="address" placeholder="Address" required maxlength="100">
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
        <input type="password" name="password" placeholder="Password" required minlength="8" maxlength="30">
    </div>

    <div class="form-group">
        <input type="password" name="confirmPassword" placeholder="Confirm Password" required minlength="8" maxlength="30">
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

        // Password matching validation for both forms
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