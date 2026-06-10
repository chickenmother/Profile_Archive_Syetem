<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome - Profile Archive System</title>
    <?php echo Asset::css('login.css'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="top-banner" id="topBanner">
        <div class="top-banner-text" id="topBannerText">
            <h1>Profile Archive System</h1>
        </div>
    </div>
    
    <div class="cookie-banner" id="cookieBanner" style="<?php echo (Cookie::get('cookie_consent') ? 'display: none;' : ''); ?>">
        <div class="cookie-text">
            <h4>We use cookies</h4>
            <p>Please, accept these sweeties to continue enjoyin our site!</p>
        </div>
        <div class="cookie-btns">
            <button class="btn-accept" onclick="acceptCookies()">Accept</button>
            <button onclick="acceptCookies()">Close</button>
        </div>
    </div>

    <div class="login-container">
        <div class="avatar-icon">
            <i class="fa-regular fa-circle-user"></i>
        </div>
        <h1>Welcome!</h1>

        <?php if (Session::get_flash('error')): ?>
            <p style="color: #ff4d4d; font-size: 0.9rem; font-weight: bold;">
                <?php echo Session::get_flash('error'); ?>
            </p>
        <?php endif; ?>

        <form action="/auth/login" method="POST">
            
            <div class="form-group <?php echo Session::get_flash('error') ? 'has-error' : ''; ?>">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-envelope input-icon-left"></i>
                    <input type="email" id="email" name="email" placeholder="info@yourmai.com" required value="<?php echo Input::post('email'); ?>">
                </div>
            </div>

            <div class="form-group <?php echo Session::get_flash('error') ? 'has-error' : ''; ?>">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <?php if (Session::get_flash('error')): ?>
                        <i class="fa-solid fa-circle-info input-icon-right" style="color: #ff4d4d;"></i>
                    <?php else: ?>
                        <i class="fa-regular fa-eye input-icon-right" id="togglePassword"></i>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group remember-me">
                <label>
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <span>Remember Me</span>
                </label>
            </div>

            <button type="submit" class="login-btn">LOGIN</button>
        </form>
    </div>

    <script>
        // Simple password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        if(togglePassword) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye-slash');
            });
        }

        // Cookie banner handling logic
        function closeBanner() {
            document.getElementById('cookieBanner').style.display = 'none';
        }

        function acceptCookies() {
            document.cookie = "cookie_consent=accepted; path=/; max-age=" + (365 * 24 * 60 * 60);
            closeBanner();
        }
    </script>
</body>
</html>