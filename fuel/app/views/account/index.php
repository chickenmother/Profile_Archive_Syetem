<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Profile Archive System</title>
    <?php echo Asset::css('dashboard.css'); ?>
    <?php echo Asset::css('profile.css'); ?>
    <?php echo Asset::css('account.css'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.js"></script>
</head>
<body>

    <!-- ===== TOP BANNER ===== -->
    <div class="top-banner">
        <h1><i class="fas fa-address-book"></i> Profile Archive System</h1>
        <div style="display: flex; align-items: center; gap: 0.8rem;">
            <div class="top-banner-user">
                <?php if (!empty($current_user['avatar'])): ?>
                <div class="top-banner-avatar" style="background-image: url('<?php echo $current_user['avatar']; ?>');"></div>
                <?php else: ?>
                <div class="top-banner-avatar"><i class="fas fa-user-circle"></i></div>
                <?php endif; ?>
                <span class="top-banner-username"><?php echo $current_user['name']; ?></span>
            </div>
            <button class="hamburger-btn" id="hamburgerBtn" data-bind="click: toggleDropdown">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- ===== HAMBURGER DROPDOWN MENU ===== -->
    <div class="user-profile-menu-dropdown" id="menuDropdown" data-bind="css: { open: dropdownOpen() }">
        <a href="/dashboard" class="dropdown-nav-item">
            <span><i class="fas fa-tachometer-alt"></i> Dashboard</span>
        </a>
        <a href="/profile" class="dropdown-nav-item">
            <span><i class="fas fa-user-circle"></i> My Profile</span>
        </a>
        <a href="/account" class="dropdown-nav-item" style="background-color: #f0f2ff;">
            <span><i class="fas fa-user-cog"></i> Account</span>
        </a>
        <a href="/project" class="dropdown-nav-item">
            <span><i class="fas fa-project-diagram"></i> Project</span>
        </a>
        <?php if ($current_user['admin_level'] >= 5): ?>
        <a href="/db" class="dropdown-nav-item">
            <span><i class="fas fa-database"></i> Database</span>
        </a>
        <?php else: ?>
        <button class="dropdown-nav-item" disabled style="opacity:0.5; cursor:not-allowed;">
            <span><i class="fas fa-database"></i> Database</span>
            <span class="badge-lock"><i class="fas fa-lock"></i></span>
        </button>
        <?php endif; ?>
        <a href="/auth/logout" class="dropdown-nav-item logout-trigger">
            <span><i class="fas fa-sign-out-alt"></i> Logout</span>
        </a>
    </div>

    <!-- ===== MAIN ACCOUNT PAGE ===== -->
    <div class="profile-page-container">

        <!-- ===== PASSWORD GATE ===== -->
        <div class="account-gate-panel" data-bind="visible: !unlocked()">
            <div class="account-gate-icon"><i class="fas fa-lock"></i></div>
            <h2>Confirm Your Password</h2>
            <p>For your security, please re-enter your password to access account settings.</p>
            <input type="password" class="account-gate-input" placeholder="Enter your password"
                   data-bind="textInput: gatePassword, event: { keydown: onGateKeydown }" autofocus>
            <button class="account-gate-btn" data-bind="click: verifyPassword, enable: !verifying()">
                <i class="fas fa-unlock"></i> <span data-bind="text: verifying() ? 'Verifying...' : 'Unlock'"></span>
            </button>
            <p class="account-gate-msg" data-bind="text: gateMsg, css: { 'msg-error': gateMsg() }"></p>
        </div>

        <!-- ===== ACCOUNT SETTINGS (revealed after gate) ===== -->
        <div class="profile-page-panel" data-bind="visible: unlocked()">

            <h2 class="account-page-title"><i class="fas fa-user-cog"></i> Account Settings</h2>

            <!-- Display Name -->
            <div class="profile-page-section">
                <h3 class="profile-section-title"><i class="fas fa-signature"></i> Display Name</h3>
                <input type="text" class="account-text-input" data-bind="value: nameInput" placeholder="Your display name">
                <div class="profile-section-footer">
                    <span></span>
                    <button class="profile-save-btn" data-bind="click: saveName">
                        <i class="fas fa-save"></i> Save Name
                    </button>
                </div>
                <p class="profile-section-msg" data-bind="text: nameMsg, css: { 'msg-error': nameMsgIsError() }"></p>
            </div>

            <!-- Email -->
            <div class="profile-page-section">
                <h3 class="profile-section-title"><i class="fas fa-envelope"></i> Email Address</h3>
                <input type="email" class="account-text-input" data-bind="value: emailInput" placeholder="your.email@example.com">
                <div class="profile-section-footer">
                    <span></span>
                    <button class="profile-save-btn" data-bind="click: saveEmail">
                        <i class="fas fa-save"></i> Save Email
                    </button>
                </div>
                <p class="profile-section-msg" data-bind="text: emailMsg, css: { 'msg-error': emailMsgIsError() }"></p>
            </div>

            <!-- Password -->
            <div class="profile-page-section">
                <h3 class="profile-section-title"><i class="fas fa-key"></i> Change Password</h3>
                <div class="account-password-fields">
                    <input type="password" class="account-text-input" data-bind="value: newPassword" placeholder="New password (min. 8 characters)">
                    <input type="password" class="account-text-input" data-bind="value: confirmPassword" placeholder="Confirm new password">
                </div>
                <div class="profile-section-footer">
                    <span></span>
                    <button class="profile-save-btn" data-bind="click: savePassword">
                        <i class="fas fa-save"></i> Update Password
                    </button>
                </div>
                <p class="profile-section-msg" data-bind="text: passwordMsg, css: { 'msg-error': passwordMsgIsError() }"></p>
            </div>

        </div>
    </div>

<script>
    var csrfToken = '<?php echo Security::fetch_token(); ?>';
    var accountData = <?php echo isset($account_json) ? $account_json : '{}'; ?>;

    function AccountViewModel() {
        var self = this;

        // ===== Hamburger dropdown =====
        self.dropdownOpen = ko.observable(false);
        self.toggleDropdown = function() {
            self.dropdownOpen(!self.dropdownOpen());
        };
        document.addEventListener('click', function(e) {
            var menu = document.getElementById('menuDropdown');
            var btn = document.getElementById('hamburgerBtn');
            if (menu && btn && !menu.contains(e.target) && !btn.contains(e.target)) {
                self.dropdownOpen(false);
            }
        });

        // ===== Password Gate =====
        self.unlocked = ko.observable(false);
        self.gatePassword = ko.observable('');
        self.gateMsg = ko.observable('');
        self.verifying = ko.observable(false);

        self.verifyPassword = function() {
            var password = self.gatePassword();
            if (!password) {
                self.gateMsg('Please enter your password.');
                return;
            }

            self.verifying(true);
            self.gateMsg('');

            var formData = new FormData();
            formData.append('fuel_csrf_token', csrfToken);
            formData.append('password', password);

            fetch('/account/verify_password', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                self.verifying(false);
                if (data.success) {
                    self.unlocked(true);
                } else {
                    self.gateMsg(data.error || 'Incorrect password.');
                    self.gatePassword('');
                }
            })
            .catch(function() {
                self.verifying(false);
                self.gateMsg('Network error. Please try again.');
            });
        };

        self.onGateKeydown = function(data, event) {
            if (event.keyCode === 13) {
                self.verifyPassword();
            }
            return true;
        };

        // ===== Display Name =====
        self.nameInput = ko.observable(accountData.name || '');
        self.nameMsg = ko.observable('');
        self.nameMsgIsError = ko.observable(false);

        self.saveName = function() {
            var name = self.nameInput().trim();
            if (!name) {
                self.nameMsg('Display name cannot be empty.');
                self.nameMsgIsError(true);
                return;
            }

            var formData = new FormData();
            formData.append('fuel_csrf_token', csrfToken);
            formData.append('name', name);

            fetch('/account/update_name', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    self.nameMsgIsError(false);
                    self.nameMsg('Display name updated!');
                    document.querySelector('.top-banner-username').textContent = data.name;
                    setTimeout(function() { self.nameMsg(''); }, 3000);
                } else {
                    self.nameMsgIsError(true);
                    self.nameMsg(data.error || 'Failed to update name.');
                }
            })
            .catch(function() {
                self.nameMsgIsError(true);
                self.nameMsg('Network error. Please try again.');
            });
        };

        // ===== Email =====
        self.emailInput = ko.observable(accountData.email || '');
        self.emailMsg = ko.observable('');
        self.emailMsgIsError = ko.observable(false);

        self.saveEmail = function() {
            var email = self.emailInput().trim();
            if (!email) {
                self.emailMsg('Email cannot be empty.');
                self.emailMsgIsError(true);
                return;
            }

            var formData = new FormData();
            formData.append('fuel_csrf_token', csrfToken);
            formData.append('email', email);

            fetch('/account/update_email', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    self.emailMsgIsError(false);
                    self.emailMsg('Email updated!');
                    setTimeout(function() { self.emailMsg(''); }, 3000);
                } else {
                    self.emailMsgIsError(true);
                    self.emailMsg(data.error || 'Failed to update email.');
                }
            })
            .catch(function() {
                self.emailMsgIsError(true);
                self.emailMsg('Network error. Please try again.');
            });
        };

        // ===== Password =====
        self.newPassword = ko.observable('');
        self.confirmPassword = ko.observable('');
        self.passwordMsg = ko.observable('');
        self.passwordMsgIsError = ko.observable(false);

        self.savePassword = function() {
            var newPass = self.newPassword();
            var confirmPass = self.confirmPassword();

            if (newPass.length < 8) {
                self.passwordMsgIsError(true);
                self.passwordMsg('Password must be at least 8 characters long.');
                return;
            }
            if (newPass !== confirmPass) {
                self.passwordMsgIsError(true);
                self.passwordMsg('Passwords do not match.');
                return;
            }

            var formData = new FormData();
            formData.append('fuel_csrf_token', csrfToken);
            formData.append('new_password', newPass);
            formData.append('confirm_password', confirmPass);

            fetch('/account/update_password', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    self.passwordMsgIsError(false);
                    self.passwordMsg('Password updated successfully!');
                    self.newPassword('');
                    self.confirmPassword('');
                    setTimeout(function() { self.passwordMsg(''); }, 3000);
                } else {
                    self.passwordMsgIsError(true);
                    self.passwordMsg(data.error || 'Failed to update password.');
                }
            })
            .catch(function() {
                self.passwordMsgIsError(true);
                self.passwordMsg('Network error. Please try again.');
            });
        };
    }

    ko.applyBindings(new AccountViewModel());
</script>

</body>
</html>
