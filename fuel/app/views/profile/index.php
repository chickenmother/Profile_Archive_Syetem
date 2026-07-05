<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Profile Archive System</title>
    <?php echo Asset::css('dashboard.css'); ?>
    <?php echo Asset::css('profile.css'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.js"></script>
</head>
<body>

    <!-- ===== TOP BANNER ===== -->
    <div class="top-banner">
        <h1><i class="fas fa-address-book"></i> Profile Archive System</h1>
        <div style="display: flex; align-items: center; gap: 0.8rem;">
            <a href="/dashboard" class="back-to-dashboard-btn">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- ===== MAIN PROFILE PAGE ===== -->
    <div class="profile-page-container" data-bind="with: employee">

        <div class="profile-page-panel">

            <!-- ① Header: Avatar + Identity -->
            <div class="profile-page-header">
                <div class="avatar-upload-wrapper">
                    <div class="profile-page-avatar" data-bind="style: { backgroundImage: avatarUrl() ? 'url(' + avatarUrl() + ')' : 'none' }">
                        <span data-bind="text: avatarUrl() ? '' : initials"></span>
                    </div>
                    <label class="avatar-upload-btn" for="avatarFileInput" title="Change avatar">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="avatarFileInput" accept="image/*" style="display:none;" data-bind="event: { change: $parent.uploadAvatar }">
                </div>
                <div class="profile-page-identity">
                    <h2 data-bind="text: name"></h2>
                    <p class="profile-page-position" data-bind="text: position_name"></p>
                    <p class="profile-page-department">
                        <i class="fas fa-building"></i> <span data-bind="text: department_name"></span>
                    </p>
                    <p class="profile-page-hire-date">
                        <i class="fas fa-calendar-alt"></i> Joined: <span data-bind="text: hire_date"></span>
                    </p>
                </div>
                <p class="avatar-upload-msg" data-bind="text: $parent.avatarMsg"></p>
            </div>

            <!-- ② Self-Introduction (Editable) -->
            <div class="profile-page-section">
                <h3 class="profile-section-title"><i class="fas fa-user"></i> About Me</h3>
                <textarea class="profile-intro-textarea" rows="4" maxlength="1000" data-bind="value: introduction, event: { input: $parent.onIntroInput }"></textarea>
                <div class="profile-section-footer">
                    <span class="profile-char-count" data-bind="text: $parent.introCharCount() + ' / 1000'"></span>
                    <button class="profile-save-btn" data-bind="click: $parent.saveIntroduction">
                        <i class="fas fa-save"></i> Save Introduction
                    </button>
                </div>
                <p class="profile-section-msg" data-bind="text: $parent.introMsg"></p>
            </div>

            <!-- ③ Skills (Editable) -->
            <div class="profile-page-section">
                <h3 class="profile-section-title"><i class="fas fa-code"></i> Skills</h3>
                <div class="profile-badge-row">
                    <!-- ko foreach: skills -->
                    <span class="skill-badge" data-bind="css: { 'badge-expert': level === 'expert', 'badge-intermediate': level === 'intermediate', 'badge-beginner': level === 'beginner' }">
                        <span data-bind="text: skillsConfig[skill_id] || 'Unknown'"></span>
                        <span class="badge-level" data-bind="text: '— ' + level"></span>
                        <button class="badge-remove-btn" data-bind="click: function() { $root.removeSkill(row_id) }" title="Remove"><i class="fas fa-times"></i></button>
                    </span>
                    <!-- /ko -->
                    <!-- ko if: skills.length === 0 -->
                    <span class="profile-empty-note">No skills added yet.</span>
                    <!-- /ko -->
                </div>

                <!-- Add Skill Form -->
                <div class="profile-add-form">
                    <select class="profile-add-select" data-bind="options: $parent.skillsList, optionsText: 'name', optionsValue: 'id', optionsCaption: 'Select a skill...', value: $parent.newSkillId"></select>
                    <select class="profile-add-select" data-bind="value: $parent.newSkillLevel">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="expert">Expert</option>
                    </select>
                    <button class="profile-add-btn" data-bind="click: $parent.addSkill">
                        <i class="fas fa-plus"></i> Add Skill
                    </button>
                </div>
                <p class="profile-section-msg" data-bind="text: $parent.skillMsg"></p>
            </div>

            <!-- ④ Certificates (Editable) -->
            <div class="profile-page-section">
                <h3 class="profile-section-title"><i class="fas fa-certificate"></i> Certificates</h3>
                <div class="profile-badge-row">
                    <!-- ko foreach: certificates -->
                    <span class="cert-badge badge-expert">
                        <span data-bind="text: certsConfig[certificate_id] || 'Unknown'"></span>
                        <span class="cert-scale" data-bind="text: '(' + scale + ')'"></span>
                        <span class="badge-level" data-bind="text: '— ' + level"></span>
                        <button class="badge-remove-btn" data-bind="click: function() { $root.removeCertificate(row_id) }" title="Remove"><i class="fas fa-times"></i></button>
                    </span>
                    <!-- /ko -->
                    <!-- ko if: certificates.length === 0 -->
                    <span class="profile-empty-note">No certificates added yet.</span>
                    <!-- /ko -->
                </div>

                <!-- Add Certificate Form -->
                <div class="profile-add-form">
                    <select class="profile-add-select" data-bind="options: $parent.certificatesList, optionsText: 'name', optionsValue: 'id', optionsCaption: 'Select a certificate...', value: $parent.newCertId"></select>
                    <input type="text" class="profile-add-input" placeholder="Level (e.g. N1, 合格, 800+)" data-bind="value: $parent.newCertLevel">
                    <select class="profile-add-select" data-bind="value: $parent.newCertScale">
                        <option value="local">Local</option>
                        <option value="national">National</option>
                        <option value="international">International</option>
                    </select>
                    <button class="profile-add-btn" data-bind="click: $parent.addCertificate">
                        <i class="fas fa-plus"></i> Add Certificate
                    </button>
                </div>
                <p class="profile-section-msg" data-bind="text: $parent.certMsg"></p>
            </div>

            <!-- ⑤ Projects (View Only) -->
            <div class="profile-page-section">
                <h3 class="profile-section-title"><i class="fas fa-project-diagram"></i> Projects <span class="section-subtitle">(managed by project leaders)</span></h3>
                <!-- ko if: projects && projects.length > 0 -->
                <div class="profile-project-list">
                    <!-- ko foreach: projects -->
                    <div class="profile-project-row">
                        <div class="project-info">
                            <span class="project-name" data-bind="text: name"></span>
                            <span class="project-leader">Led by <span data-bind="text: leader_name"></span></span>
                            <span class="project-dates" data-bind="text: start_date + ' → ' + end_date || 'Ongoing'"></span>
                        </div>
                        <span class="project-status-chip" data-bind="text: status, css: { 'status-ongoing': status === 'ongoing', 'status-completed': status === 'completed', 'status-planning': status === 'planning' }"></span>
                    </div>
                    <!-- /ko -->
                </div>
                <!-- /ko -->
                <!-- ko if: !projects || projects.length === 0 -->
                <p class="profile-empty-note">Not currently assigned to any projects.</p>
                <!-- /ko -->
            </div>

            <!-- ⑥ Comments Received (View Only) -->
            <div class="profile-page-section">
                <h3 class="profile-section-title"><i class="fas fa-comments"></i> Colleague Comments</h3>
                <!-- ko if: comments && comments.length > 0 -->
                <div class="modal-comments-list">
                    <!-- ko foreach: comments -->
                    <div class="modal-comment-block">
                        <p class="comment-content" data-bind="text: content"></p>
                        <div class="comment-meta">
                            <span class="comment-author"><i class="fas fa-user-circle"></i> <span data-bind="text: author_name"></span></span>
                            <span class="comment-date" data-bind="text: create_time"></span>
                        </div>
                    </div>
                    <!-- /ko -->
                </div>
                <!-- /ko -->
                <!-- ko if: !comments || comments.length === 0 -->
                <p class="profile-empty-note">No comments received yet.</p>
                <!-- /ko -->
            </div>

        </div>
    </div>

<script>
    var employeeData      = <?php echo isset($employee_json) ? $employee_json : '{}'; ?>;
    var skillsConfig       = <?php echo isset($skills_json) ? $skills_json : '{}'; ?>;
    var certsConfig        = <?php echo isset($certificates_json) ? $certificates_json : '{}'; ?>;

    // Enrich the employee object with safe defaults + computed helpers
    employeeData.name            = employeeData.name || 'Unknown Employee';
    employeeData.position_name   = employeeData.position_name || 'Staff Member';
    employeeData.department_name = employeeData.department_name || 'Unknown Department';
    employeeData.introduction    = employeeData.introduction || '';
    employeeData.skills          = employeeData.skills || [];
    employeeData.certificates    = employeeData.certificates || [];
    employeeData.projects        = employeeData.projects || [];
    employeeData.comments        = employeeData.comments || [];

    var parts = employeeData.name.trim().split(' ');
    employeeData.initials = parts.length >= 2
        ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
        : parts[0].substring(0, 2).toUpperCase();

    function ProfileViewModel() {
        var self = this;

        // ===== Employee data as a "with" target (needs observable wrapper for avatar) =====
        employeeData.avatarUrl = ko.observable(employeeData.avatar ? '/assets/uploads/avatars/' + employeeData.avatar : null);
        employeeData.introduction = ko.observable(employeeData.introduction);
        employeeData.skills = ko.observableArray(employeeData.skills);
        employeeData.certificates = ko.observableArray(employeeData.certificates);

        self.employee = ko.observable(employeeData);

        // ===== Avatar Upload =====
        self.avatarMsg = ko.observable('');

        self.uploadAvatar = function(data, event) {
            var file = event.target.files[0];
            if (!file) return;

            var formData = new FormData();
            formData.append('avatar', file);

            self.avatarMsg('Uploading...');

            fetch('/profile/upload_avatar', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    self.employee().avatarUrl(data.avatar_url);
                    self.avatarMsg('Avatar updated!');
                    setTimeout(function() { self.avatarMsg(''); }, 3000);
                } else {
                    self.avatarMsg(data.error || 'Upload failed.');
                }
            })
            .catch(function() { self.avatarMsg('Network error. Please try again.'); });
        };

        // ===== Self-Introduction =====
        self.introMsg = ko.observable('');
        self.introCharCount = ko.observable(employeeData.introduction().length);

        self.onIntroInput = function(data, event) {
            self.introCharCount(event.target.value.length);
        };

        self.saveIntroduction = function() {
            var content = self.employee().introduction();

            var formData = new FormData();
            formData.append('introduction', content);

            fetch('/profile/update_introduction', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    self.introMsg('Introduction saved!');
                    setTimeout(function() { self.introMsg(''); }, 3000);
                } else {
                    self.introMsg(data.error || 'Failed to save.');
                }
            })
            .catch(function() { self.introMsg('Network error. Please try again.'); });
        };

        // ===== Skills =====
        self.skillsList = Object.keys(skillsConfig).map(function(id) {
            return { id: id, name: skillsConfig[id] };
        });
        self.newSkillId = ko.observable('');
        self.newSkillLevel = ko.observable('beginner');
        self.skillMsg = ko.observable('');

        self.addSkill = function() {
            var skillId = self.newSkillId();
            var level   = self.newSkillLevel();

            if (!skillId) {
                self.skillMsg('Please select a skill.');
                return;
            }

            var formData = new FormData();
            formData.append('skill_id', skillId);
            formData.append('level', level);

            fetch('/profile/add_skill', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    self.employee().skills.push(data.skill);
                    self.newSkillId('');
                    self.newSkillLevel('beginner');
                    self.skillMsg('');
                } else {
                    self.skillMsg(data.error || 'Failed to add skill.');
                }
            })
            .catch(function() { self.skillMsg('Network error. Please try again.'); });
        };

        self.removeSkill = function(rowId) {
            var formData = new FormData();
            formData.append('row_id', rowId);

            fetch('/profile/remove_skill', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    self.employee().skills.remove(function(s) { return s.row_id == rowId; });
                } else {
                    alert(data.error || 'Failed to remove skill.');
                }
            })
            .catch(function() { alert('Network error. Please try again.'); });
        };

        // ===== Certificates =====
        self.certificatesList = Object.keys(certsConfig).map(function(id) {
            return { id: id, name: certsConfig[id] };
        });
        self.newCertId = ko.observable('');
        self.newCertLevel = ko.observable('');
        self.newCertScale = ko.observable('local');
        self.certMsg = ko.observable('');

        self.addCertificate = function() {
            var certId = self.newCertId();
            var level  = self.newCertLevel().trim();
            var scale  = self.newCertScale();

            if (!certId) {
                self.certMsg('Please select a certificate.');
                return;
            }
            if (!level) {
                self.certMsg('Please enter a level (e.g. N1, 合格, 800+).');
                return;
            }

            var formData = new FormData();
            formData.append('certificate_id', certId);
            formData.append('level', level);
            formData.append('scale', scale);

            fetch('/profile/add_certificate', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    self.employee().certificates.push(data.certificate);
                    self.newCertId('');
                    self.newCertLevel('');
                    self.newCertScale('local');
                    self.certMsg('');
                } else {
                    self.certMsg(data.error || 'Failed to add certificate.');
                }
            })
            .catch(function() { self.certMsg('Network error. Please try again.'); });
        };

        self.removeCertificate = function(rowId) {
            var formData = new FormData();
            formData.append('row_id', rowId);

            fetch('/profile/remove_certificate', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    self.employee().certificates.remove(function(c) { return c.row_id == rowId; });
                } else {
                    alert(data.error || 'Failed to remove certificate.');
                }
            })
            .catch(function() { alert('Network error. Please try again.'); });
        };
    }

    ko.applyBindings(new ProfileViewModel());
</script>

</body>
</html>
