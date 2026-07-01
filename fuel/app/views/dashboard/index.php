<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Profile Archive System</title>
    <?php echo Asset::css('dashboard.css'); ?>
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
            <button class="hamburger-btn" id="hamburgerBtn" onclick="toggleDropdown()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- ===== HAMBURGER DROPDOWN MENU ===== -->
    <div class="user-profile-menu-dropdown" id="menuDropdown">
        <a href="/profile" class="dropdown-nav-item">
            <span><i class="fas fa-user-circle"></i> My Profile</span>
        </a>
        <?php if ($current_user['admin_level'] >= 3): ?>
        <button class="dropdown-nav-item" onclick="alert('Project Management - coming soon!')">
            <span><i class="fas fa-project-diagram"></i> Project</span>
        </button>
        <?php else: ?>
        <button class="dropdown-nav-item" disabled style="opacity:0.5; cursor:not-allowed;">
            <span><i class="fas fa-project-diagram"></i> Project</span>
            <span class="badge-lock"><i class="fas fa-lock"></i></span>
        </button>
        <?php endif; ?>
        <?php if ($current_user['admin_level'] >= 5): ?>
        <button class="dropdown-nav-item" onclick="alert('Account Management - coming soon!')">
            <span><i class="fas fa-users-cog"></i> Account</span>
        </button>
        <button class="dropdown-nav-item" onclick="alert('Database Management - coming soon!')">
            <span><i class="fas fa-database"></i> Database</span>
        </button>
        <?php else: ?>
        <button class="dropdown-nav-item" disabled style="opacity:0.5; cursor:not-allowed;">
            <span><i class="fas fa-users-cog"></i> Account</span>
            <span class="badge-lock"><i class="fas fa-lock"></i></span>
        </button>
        <button class="dropdown-nav-item" disabled style="opacity:0.5; cursor:not-allowed;">
            <span><i class="fas fa-database"></i> Database</span>
            <span class="badge-lock"><i class="fas fa-lock"></i></span>
        </button>
        <?php endif; ?>
        <div style="border-top: 1px solid #eee;"></div>
        <a href="/auth/logout" class="dropdown-nav-item logout-trigger">
            <span><i class="fas fa-sign-out-alt"></i> Logout</span>
        </a>
    </div>

    <!-- ===== FILTER TOOLBAR STRIP (collapsible) ===== -->
    <div class="filter-toolbar-strip" id="filterStrip">
        <button class="filter-toggle-btn" id="clearFiltersBtn" onclick="clearFilters()">
            <i class="fas fa-sliders-h" id="clearFiltersBtnIcon"></i>
            <span id="clearFiltersBtnLabel">Filters</span>
        </button>

        <select class="filter-select" data-bind="value: selectedDepartment, event: { change: applyFilters }">
            <option value="">All Departments</option>
            <?php foreach ($departments as $dept): ?>
            <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
            <?php endforeach; ?>
        </select>

        <select class="filter-select" data-bind="value: selectedPosition, event: { change: applyFilters }">
            <option value="">All Positions</option>
            <?php foreach ($positions as $pos): ?>
            <option value="<?php echo $pos['id']; ?>"><?php echo $pos['name']; ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Skills Checkbox Dropdown -->
        <div class="checkbox-filter-wrapper" id="skillsFilterWrapper">
            <button class="checkbox-filter-btn" onclick="toggleCheckboxPanel('skillsPanel', 'skillsFilterWrapper')">
                <i class="fas fa-code"></i>
                <span id="skillsBtnLabel">Skills</span>
                <i class="fas fa-chevron-down checkbox-filter-chevron" id="skillsChevron"></i>
            </button>
            <div class="checkbox-filter-panel" id="skillsPanel">
                <?php foreach ($skills as $skill): ?>
                <label class="checkbox-filter-item">
                    <input type="checkbox" value="<?php echo $skill['id']; ?>" data-bind="checked: selectedSkills" onchange="viewModel && viewModel.applyFilters()">
                    <?php echo $skill['name']; ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Certificates Checkbox Dropdown -->
        <div class="checkbox-filter-wrapper" id="certsFilterWrapper">
            <button class="checkbox-filter-btn" onclick="toggleCheckboxPanel('certsPanel', 'certsFilterWrapper')">
                <i class="fas fa-certificate"></i>
                <span id="certsBtnLabel">Certificates</span>
                <i class="fas fa-chevron-down checkbox-filter-chevron" id="certsChevron"></i>
            </button>
            <div class="checkbox-filter-panel" id="certsPanel">
                <?php foreach ($certificates as $cert): ?>
                <label class="checkbox-filter-item">
                    <input type="checkbox" value="<?php echo $cert['id']; ?>" data-bind="checked: selectedCertificates" onchange="viewModel && viewModel.applyFilters()">
                    <?php echo $cert['name']; ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <select class="filter-select" data-bind="value: selectedYears, event: { change: applyFilters }">
            <option value="">All Tenures</option>
            <option value="1">< 1 year</option>
            <option value="2">1-2 years</option>
            <option value="3">3-5 years</option>
            <option value="5">5+ years</option>
        </select>

        <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Search by name..." data-bind="value: searchText, event: { keyup: applyFilters }">
        </div>
    </div>

    <!-- ===== MAIN DASHBOARD ===== -->
    <div class="dashboard-container">
        <div class="grid-panel">
        <div class="employee-profile-grid">
            <!-- ko foreach: pagedEmployees -->
            <div class="employee-card" data-bind="click: $parent.viewProfile">
                <!-- Card Top: Identity -->
                <div class="card-top-identity">
                    <div class="profile-avatar-block">
                        <div class="card-avatar" data-bind="text: avatarUrl ? '' : initials, style: { backgroundImage: avatarUrl ? 'url(' + avatarUrl + ')' : 'none' }"></div>
                        <div>
                            <h3 data-bind="text: name"></h3>
                            <p data-bind="text: position_name"></p>
                        </div>
                    </div>
                    <button class="btn-card-action-more" data-bind="click: function() { return true; }, clickBubble: false">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <!-- Card Body: About -->
                <div class="card-body-about">
                    <h4>About</h4>
                    <p data-bind="text: introduction"></p>
                </div>

                <!-- Card Footer: Metrics -->
                <div class="card-footer-metrics">
                    <div class="metric-item">
                        <span class="metric-value" data-bind="text: project_count"></span>
                        <span class="metric-label">Projects</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-value" data-bind="text: skill_count"></span>
                        <span class="metric-label">Skills</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-value" data-bind="text: certificate_count"></span>
                        <span class="metric-label">Certs</span>
                    </div>
                </div>
            </div>
            <!-- /ko -->

            <!-- No Results (inside grid so it spans all columns) -->
            <!-- ko if: filteredEmployees().length === 0 -->
            <div class="empty-grid-fallback">
                <i class="fas fa-search" style="font-size: 2.5rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>No employees match your filter criteria.</p>
            </div>
            <!-- /ko -->
        </div>

        <!-- ===== PAGINATION BAR ===== -->
        <div class="pagination-bar">
            <button class="page-btn page-nav" data-bind="click: prevPage, disable: currentPage() === 1">
                <i class="fas fa-chevron-left"></i>
            </button>
            <!-- ko foreach: pageNumbers -->
            <button class="page-btn" data-bind="text: $data, click: $parent.goToPage, css: { 'page-btn-active': $data === $parent.currentPage() }"></button>
            <!-- /ko -->
            <button class="page-btn page-nav" data-bind="click: nextPage, disable: currentPage() === totalPages()">
                <i class="fas fa-chevron-right"></i>
            </button>
            <span class="page-info" data-bind="text: 'Page ' + currentPage() + ' of ' + totalPages()"></span>
        </div>
        </div><!-- /.grid-panel -->
    </div><!-- /.dashboard-container -->

    <!-- ===== PROFILE MODAL OVERLAY ===== -->
    <!-- ko if: selectedEmployee() !== null -->
    <div class="profile-modal-overlay" data-bind="click: closeProfile">
        <div class="profile-modal-panel" data-bind="with: selectedEmployee, click: function(){}, clickBubble: false, animateVisible: true">

            <!-- Close Button -->
            <button class="modal-close-btn" data-bind="click: $parent.closeProfile, clickBubble: false">
                <i class="fas fa-times"></i>
            </button>

            <!-- ① Modal Header: Avatar + Identity -->
            <div class="modal-header-section">
                <div class="modal-avatar" data-bind="text: avatarUrl ? '' : initials, style: { backgroundImage: avatarUrl ? 'url(' + avatarUrl + ')' : 'none' }"></div>
                <div class="modal-identity">
                    <h2 data-bind="text: name"></h2>
                    <p class="modal-position" data-bind="text: position_name"></p>
                    <p class="modal-department">
                        <i class="fas fa-building"></i>
                        <span data-bind="text: department_name"></span>
                    </p>
                    <p class="modal-hire-date">
                        <i class="fas fa-calendar-alt"></i>
                        Joined: <span data-bind="text: hire_date"></span>
                    </p>
                </div>
            </div>

            <!-- ② About Section -->
            <div class="modal-section">
                <h3 class="modal-section-title"><i class="fas fa-user"></i> About</h3>
                <p class="modal-about-text" data-bind="text: introduction"></p>
            </div>

            <!-- ③ Skills Section -->
            <!-- ko if: skills && skills.length > 0 -->
            <div class="modal-section">
                <h3 class="modal-section-title"><i class="fas fa-code"></i> Skills</h3>
                <div class="modal-badge-row">
                    <!-- ko foreach: skills -->
                    <span class="skill-badge" data-bind="css: { 'badge-expert': level === 'expert', 'badge-intermediate': level === 'intermediate', 'badge-beginner': level === 'beginner' }">
                        <span data-bind="text: skillsConfig[skill_id] || 'Unknown'"></span>
                        <span class="badge-level" data-bind="text: '— ' + level"></span>
                    </span>
                    <!-- /ko -->
                </div>
            </div>
            <!-- /ko -->

            <!-- ④ Certificates Section -->
            <!-- ko if: certificates && certificates.length > 0 -->
            <div class="modal-section">
                <h3 class="modal-section-title"><i class="fas fa-certificate"></i> Certificates</h3>
                <div class="modal-badge-row">
                    <!-- ko foreach: certificates -->
                    <span class="cert-badge badge-expert">
                        <span data-bind="text: certsConfig[certificate_id] || 'Unknown'"></span>
                        <span class="cert-scale" data-bind="text: '(' + scale + ')'"></span>
                        <span class="badge-level" data-bind="text: '— ' + level"></span>
                    </span>
                    <!-- /ko -->
                </div>
            </div>
            <!-- /ko -->

            <!-- ⑤ Projects Section -->
            <!-- ko if: projects && projects.length > 0 -->
            <div class="modal-section">
                <h3 class="modal-section-title"><i class="fas fa-project-diagram"></i> Projects</h3>
                <div class="modal-project-list">
                    <!-- ko foreach: projects -->
                    <div class="modal-project-row">
                        <div class="project-info">
                            <span class="project-name" data-bind="text: name"></span>
                            <span class="project-dates" data-bind="text: start_date + ' → ' + end_date"></span>
                        </div>
                        <span class="project-status-chip" data-bind="text: status, css: { 'status-ongoing': status === 'ongoing', 'status-completed': status === 'completed', 'status-planning': status === 'planning' }"></span>
                    </div>
                    <!-- /ko -->
                </div>
            </div>
            <!-- /ko -->

            <!-- ⑥ Comments Section -->
            <div class="modal-section">
                <h3 class="modal-section-title"><i class="fas fa-comments"></i> Colleague Comments</h3>
                <!-- ko if: comments && comments.length > 0 -->
                <div class="modal-comments-list" data-bind="attr: { id: 'comments-list-' + id }">
                    <!-- ko foreach: comments -->
                    <div class="modal-comment-block">
                        <!-- View mode — use $index() for reliable DOM IDs -->
                        <p class="comment-content" data-bind="text: content, attr: { id: 'comment-text-' + $index() }"></p>
                        <!-- Edit mode (hidden by default) -->
                        <div class="comment-edit-area" data-bind="attr: { id: 'comment-edit-' + $index() }" style="display:none;">
                            <textarea class="comment-textarea comment-edit-textarea" rows="3" maxlength="500" data-bind="attr: { id: 'comment-edit-input-' + $index() }"></textarea>
                            <div class="comment-edit-actions">
                                <button class="comment-save-btn" data-bind="click: function() { $root.saveComment(comment_id, $index()) }"><i class="fas fa-check"></i> Save</button>
                                <button class="comment-cancel-btn" data-bind="click: function() { $root.cancelEdit($index()) }"><i class="fas fa-times"></i> Cancel</button>
                            </div>
                        </div>
                        <div class="comment-meta">
                            <span class="comment-author"><i class="fas fa-user-circle"></i> <span data-bind="text: author_name"></span></span>
                            <span class="comment-date" data-bind="text: create_time"></span>
                            <!-- Edit/Delete — only shown to the comment author -->
                            <!-- ko if: String(author_id) === String($root.currentUserId) -->
                            <span class="comment-owner-actions">
                                <button class="comment-edit-btn" data-bind="click: function() { $root.startEdit($index(), content) }" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                                <button class="comment-delete-btn" data-bind="click: function() { $root.deleteComment(comment_id, $index()) }" title="Delete"><i class="fas fa-trash-alt"></i></button>
                            </span>
                            <!-- /ko -->
                        </div>
                    </div>
                    <!-- /ko -->
                </div>
                <!-- /ko -->
                <!-- ko if: !comments || comments.length === 0 -->
                <p class="modal-no-comments" data-bind="attr: { id: 'comments-list-' + id }">No comments yet. Be the first to leave one!</p>
                <!-- /ko -->

                <!-- Comment Form — hidden when viewing own profile -->
                <!-- ko if: id != $root.currentUserId -->
                <div class="comment-form-wrapper">
                    <h4 class="comment-form-title"><i class="fas fa-pen"></i> Leave a Comment</h4>
                    <textarea class="comment-textarea" data-bind="attr: { id: 'comment-input-' + id }" placeholder="Write something about this colleague... (max 500 characters)" maxlength="500" rows="3"></textarea>
                    <div class="comment-form-footer">
                        <span class="comment-char-count" data-bind="attr: { id: 'comment-chars-' + id }">0 / 500</span>
                        <button class="comment-submit-btn" data-bind="click: function() { $parent.submitComment(id) }">
                            <i class="fas fa-paper-plane"></i> Post Comment
                        </button>
                    </div>
                    <p class="comment-form-msg" data-bind="attr: { id: 'comment-msg-' + id }"></p>
                </div>
                <!-- /ko -->
            </div>

        </div>
    </div>
    <!-- /ko -->

<script>
        // ===== Hamburger Dropdown Toggle =====
        function toggleDropdown() {
            document.getElementById('menuDropdown').classList.toggle('open');
        }

        document.addEventListener('click', function(e) {
            var dropdown = document.getElementById('menuDropdown');
            var hamburger = document.getElementById('hamburgerBtn');
            if (dropdown && hamburger && !dropdown.contains(e.target) && !hamburger.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });

        // ===== Collapsible Filter Toggle =====
        function toggleFilters() {
            var strip = document.getElementById('filterStrip');
            if (strip) strip.classList.toggle('collapsed');
        }

        // ===== Knockout.js Data Initialization Pipeline =====
        var employeesData = <?php echo isset($employees_json) ? $employees_json : '[]'; ?>;
        var skillsConfig  = <?php echo isset($skills_json) ? $skills_json : '{}'; ?>;
        var certsConfig   = <?php echo isset($certificates_json) ? $certificates_json : '{}'; ?>;
        var currentUserId = <?php echo (int)$current_user['id']; ?>;

        // Enrich profiles to map empty properties safely
        if (Array.isArray(employeesData)) {
            employeesData.forEach(function(emp) {
                emp.name              = emp.name || 'Unknown Employee';
                emp.position_name     = emp.position_name || 'Staff Member';
                emp.department_name   = emp.department_name || 'Unknown Department';
                emp.introduction      = emp.introduction || 'No introduction provided.';
                emp.project_count     = emp.project_count || 0;
                emp.skill_count       = emp.skill_count || 0;
                emp.certificate_count = emp.certificate_count || 0;
                emp.skills            = emp.skills || [];
                emp.certificates      = emp.certificates || [];
                emp.projects          = emp.projects || [];
                emp.comments          = emp.comments || [];
                emp.avatarUrl         = emp.avatar ? '/assets/uploads/avatars/' + emp.avatar : null;

                // Compute initials
                var parts = emp.name.trim().split(' ');
                emp.initials = parts.length >= 2
                    ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
                    : parts[0].substring(0, 2).toUpperCase();

                emp.yearsEmployed = emp.hire_date
                    ? Math.floor((new Date() - new Date(emp.hire_date)) / (365.25 * 24 * 60 * 60 * 1000))
                    : 0;
            });
        }

        function DashboardViewModel() {
            var self = this;

            self.allEmployees = employeesData;
            self.currentUserId = currentUserId;

            // ===== Profile Modal State =====
            self.selectedEmployee = ko.observable(null);

            self.viewProfile = function(employee) {
                self.selectedEmployee(employee);
                document.body.style.overflow = 'hidden'; // prevent background scroll
            };

            self.closeProfile = function() {
                self.selectedEmployee(null);
                document.body.style.overflow = ''; // restore scroll
            };

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    self.closeProfile();
                }
            });

            // ===== Reactive Filter Observables =====
            self.selectedDepartment    = ko.observable('');
            self.selectedPosition      = ko.observable('');
            self.selectedSkills        = ko.observableArray([]);   // multi-select
            self.selectedCertificates  = ko.observableArray([]);   // multi-select
            self.selectedYears         = ko.observable('');
            self.searchText            = ko.observable('');

            // Update button labels when checkbox selections change
            self.selectedSkills.subscribe(function(vals) {
                var label = vals.length > 0 ? 'Skills (' + vals.length + ')' : 'Skills';
                document.getElementById('skillsBtnLabel').textContent = label;
            });
            self.selectedCertificates.subscribe(function(vals) {
                var label = vals.length > 0 ? 'Certificates (' + vals.length + ')' : 'Certificates';
                document.getElementById('certsBtnLabel').textContent = label;
            });

            // ===== Computed Filtering Engine =====
            self.filteredEmployees = ko.computed(function() {
                var dept    = self.selectedDepartment();
                var pos     = self.selectedPosition();
                var skills  = self.selectedSkills();
                var certs   = self.selectedCertificates();
                var years   = self.selectedYears();
                var search  = self.searchText().toLowerCase().trim();

                return self.allEmployees.filter(function(emp) {
                    if (dept && dept !== "" && String(emp.department_id) !== dept) return false;
                    if (pos  && pos  !== "" && String(emp.position_id)   !== pos)  return false;

                    // Skills: employee must have ALL of the checked skills
                    if (skills.length > 0) {
                        var empSkillIds = emp.skills.map(function(s) { return String(s.skill_id); });
                        var hasAllSkills = skills.every(function(sid) {
                            return empSkillIds.indexOf(sid) !== -1;
                        });
                        if (!hasAllSkills) return false;
                    }

                    // Certificates: employee must have ALL of the checked certs
                    if (certs.length > 0) {
                        var empCertIds = emp.certificates.map(function(c) { return String(c.certificate_id); });
                        var hasAllCerts = certs.every(function(cid) {
                            return empCertIds.indexOf(cid) !== -1;
                        });
                        if (!hasAllCerts) return false;
                    }

                    if (years && years !== "") {
                        var y = parseInt(years);
                        if (y === 1 && emp.yearsEmployed >= 1) return false;
                        else if (y === 2 && (emp.yearsEmployed < 1 || emp.yearsEmployed > 2)) return false;
                        else if (y === 3 && (emp.yearsEmployed < 3 || emp.yearsEmployed > 5)) return false;
                        else if (y === 5 && emp.yearsEmployed < 5) return false;
                    }

                    if (search && emp.name.toLowerCase().indexOf(search) === -1) return false;

                    return true;
                });
            });

            // ===== Pagination =====
            var PAGE_SIZE = 6;
            self.currentPage = ko.observable(1);

            // Reset to page 1 whenever filters change
            self.filteredEmployees.subscribe(function() {
                self.currentPage(1);
            });

            self.totalPages = ko.computed(function() {
                return Math.max(1, Math.ceil(self.filteredEmployees().length / PAGE_SIZE));
            });

            // Array of page numbers [1, 2, 3, ...]
            self.pageNumbers = ko.computed(function() {
                var pages = [];
                for (var i = 1; i <= self.totalPages(); i++) {
                    pages.push(i);
                }
                return pages;
            });

            // Slice of employees for the current page
            self.pagedEmployees = ko.computed(function() {
                var start = (self.currentPage() - 1) * PAGE_SIZE;
                return self.filteredEmployees().slice(start, start + PAGE_SIZE);
            });

            self.goToPage = function(page) {
                if (page >= 1 && page <= self.totalPages()) {
                    self.currentPage(page);
                }
            };

            self.prevPage = function() { self.goToPage(self.currentPage() - 1); };
            self.nextPage = function() { self.goToPage(self.currentPage() + 1); };

            self.applyFilters = function() { return true; };

            // ===== Comment Submission =====
            self.submitComment = function(receiverId) {
                var textarea  = document.getElementById('comment-input-' + receiverId);
                var msgEl     = document.getElementById('comment-msg-' + receiverId);
                var content   = textarea ? textarea.value.trim() : '';

                if (!content) {
                    msgEl.textContent = 'Please write something before posting.';
                    msgEl.className   = 'comment-form-msg comment-msg-error';
                    return;
                }

                var formData = new FormData();
                formData.append('receiver_id', receiverId);
                formData.append('content', content);

                fetch('/dashboard/comment', {
                    method: 'POST',
                    body: formData
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        // Push new comment into the employee's comments array
                        var emp = self.selectedEmployee();
                        if (emp) {
                            emp.comments.unshift(data.comment);
                            // Force Knockout to re-render by toggling selectedEmployee
                            self.selectedEmployee(null);
                            self.selectedEmployee(emp);
                        }
                        // Clear textarea and counter
                        if (textarea) textarea.value = '';
                        var charEl = document.getElementById('comment-chars-' + receiverId);
                        if (charEl) charEl.textContent = '0 / 500';
                        msgEl.textContent = 'Comment posted!';
                        msgEl.className   = 'comment-form-msg comment-msg-success';
                        setTimeout(function() { msgEl.textContent = ''; msgEl.className = 'comment-form-msg'; }, 3000);
                    } else {
                        msgEl.textContent = data.error || 'Failed to post comment.';
                        msgEl.className   = 'comment-form-msg comment-msg-error';
                    }
                })
                .catch(function() {
                    msgEl.textContent = 'Network error. Please try again.';
                    msgEl.className   = 'comment-form-msg comment-msg-error';
                });
            };

            // Wire up character counter (delegated — runs after modal renders)
            document.addEventListener('input', function(e) {
                if (e.target && e.target.classList.contains('comment-textarea')) {
                    var id = e.target.id.replace('comment-input-', '').replace('comment-edit-input-', '');
                    var charEl = document.getElementById('comment-chars-' + id);
                    if (charEl) charEl.textContent = e.target.value.length + ' / 500';
                }
            });

            // ===== Comment Edit / Delete =====

            // Show inline edit textarea for a comment
            // idx = $index() from Knockout (reliable DOM key)
            // currentContent = the comment text to pre-fill
            self.startEdit = function(idx, currentContent) {
                var textEl  = document.getElementById('comment-text-'       + idx);
                var editEl  = document.getElementById('comment-edit-'       + idx);
                var inputEl = document.getElementById('comment-edit-input-' + idx);

                if (textEl)  textEl.style.display = 'none';
                if (editEl)  editEl.style.display  = 'block';
                if (inputEl) {
                    inputEl.value = currentContent;
                    inputEl.focus();
                }
            };

            // Cancel edit — restore view mode
            self.cancelEdit = function(idx) {
                var textEl = document.getElementById('comment-text-' + idx);
                var editEl = document.getElementById('comment-edit-' + idx);
                if (textEl) textEl.style.display = '';
                if (editEl) editEl.style.display  = 'none';
            };

            // Save edited comment via AJAX
            // commentId = real DB id for the AJAX call
            // idx       = $index() for DOM lookup
            self.saveComment = function(commentId, idx) {
                var inputEl    = document.getElementById('comment-edit-input-' + idx);
                var newContent = inputEl ? inputEl.value.trim() : '';

                if (!newContent) return;

                var formData = new FormData();
                formData.append('comment_id', commentId);
                formData.append('content', newContent);

                fetch('/dashboard/edit_comment', { method: 'POST', body: formData })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        // Update the visible text paragraph in the DOM
                        var textEl = document.getElementById('comment-text-' + idx);
                        if (textEl) textEl.textContent = data.content;

                        // Also update the underlying data object so re-renders are correct
                        var emp = self.selectedEmployee();
                        if (emp && emp.comments[idx]) {
                            emp.comments[idx].content = data.content;
                        }
                        self.cancelEdit(idx);
                    } else {
                        alert(data.error || 'Failed to update comment.');
                    }
                })
                .catch(function() { alert('Network error. Please try again.'); });
            };

            // Delete a comment via AJAX and remove it from the list
            self.deleteComment = function(commentId, index) {
                if (!confirm('Delete this comment?')) return;

                var formData = new FormData();
                formData.append('comment_id', commentId);

                fetch('/dashboard/delete_comment', { method: 'POST', body: formData })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        var emp = self.selectedEmployee();
                        if (emp) {
                            emp.comments.splice(index, 1);
                            // Force Knockout re-render
                            self.selectedEmployee(null);
                            self.selectedEmployee(emp);
                        }
                    } else {
                        alert(data.error || 'Failed to delete comment.');
                    }
                })
                .catch(function() { alert('Network error. Please try again.'); });
            };

            // ===== Active Filter Detection =====
            self.hasActiveFilters = ko.computed(function() {
                return self.selectedDepartment()   !== ''  ||
                       self.selectedPosition()     !== ''  ||
                       self.selectedSkills().length  > 0   ||
                       self.selectedCertificates().length > 0 ||
                       self.selectedYears()        !== ''  ||
                       self.searchText().trim()    !== '';
            });

            // Update the clear-filters button appearance reactively
            self.hasActiveFilters.subscribe(function(active) {
                var btn   = document.getElementById('clearFiltersBtn');
                var icon  = document.getElementById('clearFiltersBtnIcon');
                var label = document.getElementById('clearFiltersBtnLabel');
                if (!btn) return;
                if (active) {
                    btn.classList.add('filter-btn-active');
                    icon.className  = 'fas fa-times';
                    label.textContent = 'Clear Filters';
                } else {
                    btn.classList.remove('filter-btn-active');
                    icon.className  = 'fas fa-sliders-h';
                    label.textContent = 'Filters';
                }
            });
        }

        // ===== Checkbox Filter Panel Toggle =====
        function toggleCheckboxPanel(panelId, wrapperId) {
            var panel = document.getElementById(panelId);
            var wrapper = document.getElementById(wrapperId);
            var isOpen = panel.classList.contains('open');

            // Close all other open panels first
            document.querySelectorAll('.checkbox-filter-panel.open').forEach(function(p) {
                p.classList.remove('open');
            });
            document.querySelectorAll('.checkbox-filter-wrapper.active').forEach(function(w) {
                w.classList.remove('active');
            });

            if (!isOpen) {
                panel.classList.add('open');
                wrapper.classList.add('active');
            }
        }

        // Close checkbox panels when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.checkbox-filter-wrapper')) {
                document.querySelectorAll('.checkbox-filter-panel.open').forEach(function(p) {
                    p.classList.remove('open');
                });
                document.querySelectorAll('.checkbox-filter-wrapper.active').forEach(function(w) {
                    w.classList.remove('active');
                });
            }
        });

        function clearFilters() {
            viewModel.selectedDepartment('');
            viewModel.selectedPosition('');
            viewModel.selectedSkills([]);
            viewModel.selectedCertificates([]);
            viewModel.selectedYears('');
            viewModel.searchText('');
            document.querySelectorAll('.filter-select').forEach(function(sel) { sel.selectedIndex = 0; });
            document.querySelectorAll('.checkbox-filter-panel input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
            var searchInput = document.querySelector('.search-input-wrapper input');
            if (searchInput) searchInput.value = '';
        }

        var viewModel = new DashboardViewModel();
        ko.applyBindings(viewModel);
    </script>

</body>
</html>
