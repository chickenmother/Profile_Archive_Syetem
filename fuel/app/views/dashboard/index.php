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
            <span style="font-size: 0.9rem; opacity: 0.9;"><i class="fas fa-user-circle"></i> <?php echo $current_user['name']; ?></span>
            <button class="hamburger-btn" id="hamburgerBtn" onclick="toggleDropdown()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- ===== HAMBURGER DROPDOWN MENU ===== -->
    <div class="user-profile-menu-dropdown" id="menuDropdown">
        <button class="dropdown-nav-item" onclick="alert('My Profile - coming soon!')">
            <span><i class="fas fa-user-circle"></i> My Profile</span>
        </button>
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
        <button class="filter-toggle-btn" onclick="toggleFilters()">
            <i class="fas fa-sliders-h"></i> Filters
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

        <select class="filter-select" data-bind="value: selectedSkill, event: { change: applyFilters }">
            <option value="">All Skills</option>
            <?php foreach ($skills as $skill): ?>
            <option value="<?php echo $skill['id']; ?>"><?php echo $skill['name']; ?></option>
            <?php endforeach; ?>
        </select>

        <select class="filter-select" data-bind="value: selectedCertificate, event: { change: applyFilters }">
            <option value="">All Certificates</option>
            <?php foreach ($certificates as $cert): ?>
            <option value="<?php echo $cert['id']; ?>"><?php echo $cert['name']; ?></option>
            <?php endforeach; ?>
        </select>

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
                        <div class="card-avatar" data-bind="text: initials"></div>
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
                <div class="modal-avatar" data-bind="text: initials"></div>
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
                    <span class="cert-badge" data-bind="css: { 'badge-expert': level === 'expert', 'badge-intermediate': level === 'intermediate', 'badge-beginner': level === 'beginner' }">
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
            <!-- ko if: comments && comments.length > 0 -->
            <div class="modal-section">
                <h3 class="modal-section-title"><i class="fas fa-comments"></i> Colleague Comments</h3>
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
            </div>
            <!-- /ko -->

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
            self.selectedDepartment  = ko.observable('');
            self.selectedPosition    = ko.observable('');
            self.selectedSkill       = ko.observable('');
            self.selectedCertificate = ko.observable('');
            self.selectedYears       = ko.observable('');
            self.searchText          = ko.observable('');

            // ===== Computed Filtering Engine =====
            self.filteredEmployees = ko.computed(function() {
                var dept   = self.selectedDepartment();
                var pos    = self.selectedPosition();
                var skill  = self.selectedSkill();
                var cert   = self.selectedCertificate();
                var years  = self.selectedYears();
                var search = self.searchText().toLowerCase().trim();

                return self.allEmployees.filter(function(emp) {
                    if (dept && dept !== "" && String(emp.department_id) !== dept) return false;
                    if (pos  && pos  !== "" && String(emp.position_id)   !== pos)  return false;

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
        }

        function clearFilters() {
            viewModel.selectedDepartment('');
            viewModel.selectedPosition('');
            viewModel.selectedSkill('');
            viewModel.selectedCertificate('');
            viewModel.selectedYears('');
            viewModel.searchText('');
            document.querySelectorAll('.filter-select').forEach(function(sel) { sel.selectedIndex = 0; });
            var searchInput = document.querySelector('.search-input-wrapper input');
            if (searchInput) searchInput.value = '';
        }

        var viewModel = new DashboardViewModel();
        ko.applyBindings(viewModel);
    </script>

</body>
</html>
