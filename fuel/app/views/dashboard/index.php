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
        <div class="employee-profile-grid">
            <!-- ko foreach: filteredEmployees -->
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
        </div>

        <!-- No Results -->
        <div class="empty-grid-fallback" data-bind="visible: filteredEmployees().length === 0">
            <i class="fas fa-search" style="font-size: 2.5rem; color: #ccc; margin-bottom: 1rem;"></i>
            <p>No employees match your filter criteria.</p>
        </div>
    </div>

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
        // Using isset checks ensures the JavaScript compilation never drops on empty values
        var employeesData = <?php echo isset($employees_json) ? $employees_json : '[]'; ?>;
        var skillsConfig  = <?php echo isset($skills_json) ? $skills_json : '[]'; ?>;
        var certsConfig   = <?php echo isset($certificates_json) ? $certificates_json : '[]'; ?>;

        // Enrich profiles to map empty properties safely
        if (Array.isArray(employeesData)) {
            employeesData.forEach(function(emp) {
            emp.name              = emp.name || 'Unknown Employee';
            emp.position_name     = emp.position_name || 'Staff Member';
            emp.introduction      = emp.introduction || 'No introduction provided.';
            emp.project_count     = emp.project_count || 0;
            emp.skill_count       = emp.skill_count || 0;
            emp.certificate_count = emp.certificate_count || 0;

            // ✅ Initials — inside forEach where emp is defined
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

            // Reactive UI Filter Tracking
            self.selectedDepartment  = ko.observable('');
            self.selectedPosition    = ko.observable('');
            self.selectedSkill       = ko.observable('');
            self.selectedCertificate = ko.observable('');
            self.selectedYears       = ko.observable('');
            self.searchText          = ko.observable('');

            // Computed Filtering Engine
            self.filteredEmployees = ko.computed(function() {
                var dept   = self.selectedDepartment();
                var pos    = self.selectedPosition();
                var skill  = self.selectedSkill();
                var cert   = self.selectedCertificate();
                var years  = self.selectedYears();
                var search = self.searchText().toLowerCase().trim();

                return self.allEmployees.filter(function(emp) {
                    // 1. Department Mapping Rule
                    if (dept && dept !== "" && String(emp.department_id) !== dept) return false;
                    
                    // 2. Position Mapping Rule
                    if (pos && pos !== "" && String(emp.position_id) !== pos) return false;
                    
                    // 3. Skill Selector Rule
                    if (skill && skill !== "") {
                        // Custom conditional filters can go here later
                    }

                    // 4. Certificate Selector Rule
                    if (cert && cert !== "") {
                        // Custom conditional filters can go here later
                    }

                    // 5. Tenure Range Computation Rule
                    if (years && years !== "") {
                        var y = parseInt(years);
                        if (y === 1 && emp.yearsEmployed >= 1) return false;
                        else if (y === 2 && (emp.yearsEmployed < 1 || emp.yearsEmployed > 2)) return false;
                        else if (y === 3 && (emp.yearsEmployed < 3 || emp.yearsEmployed > 5)) return false;
                        else if (y === 5 && emp.yearsEmployed < 5) return false;
                    }
                    
                    // 6. Text Name Direct Matching Check
                    if (search && emp.name.toLowerCase().indexOf(search) === -1) return false;

                    return true;
                });
            });

            self.applyFilters = function() {
                return true;
            };

            self.viewProfile = function(employee) {
                alert('Profile page for ' + employee.name + ' coming soon!');
            };
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