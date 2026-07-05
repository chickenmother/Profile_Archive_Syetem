<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management - Profile Archive System</title>
    <?php echo Asset::css('project.css'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.js"></script>
</head>
<body>

    <!-- ===== TOP BANNER (identical to dashboard) ===== -->
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
        <a href="/project" class="dropdown-nav-item" style="background-color: #f0f2ff;">
            <span><i class="fas fa-project-diagram"></i> Project</span>
        </a>
        <?php if ($current_user['admin_level'] >= 5): ?>
        <button class="dropdown-nav-item" onclick="alert('Account Management - coming soon!')">
            <span><i class="fas fa-users-cog"></i> Account</span>
        </button>
        <button class="dropdown-nav-item" onclick="alert('Database Management - coming soon!')">
            <span><i class="fas fa-database"></i> Database</span>
        </button>
        <?php endif; ?>
        <a href="/auth/logout" class="dropdown-nav-item logout-trigger">
            <span><i class="fas fa-sign-out-alt"></i> Logout</span>
        </a>
    </div>

    <!-- ===== TOOLBAR ===== -->
    <div class="project-toolbar">
        <div class="toolbar-left">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" placeholder="Search by project name or ID..." data-bind="textInput: searchText" />
            </div>
            <div class="filter-wrapper">
                <select class="filter-select" data-bind="value: statusFilter">
                    <option value="">All Status</option>
                    <option value="planning">Planning</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="filter-wrapper">
                <select class="filter-select" data-bind="value: sortBy">
                    <option value="id">Sort: Project ID</option>
                    <option value="name">Sort: Project Name</option>
                    <option value="date">Sort: Start Date</option>
                </select>
            </div>
            <button class="btn-toggle-myprojects" data-bind="click: toggleMyProjects, css: { active: showMyProjectsOnly() }">
                <i class="fas fa-user-check"></i>
                <span data-bind="text: showMyProjectsOnly() ? 'My Projects' : 'All Projects'"></span>
            </button>
        </div>
        <button class="btn-add-project" data-bind="click: openCreatePanel">
            <i class="fas fa-plus"></i> Add New Project
        </button>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="project-container">
        <div class="table-panel">
            <table class="project-table">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-name">Project Name</th>
                        <th class="col-leader">Leader</th>
                        <th class="col-date">Date Started</th>
                        <th class="col-members">Members</th>
                        <th class="col-status">Status</th>
                        <th class="col-action"></th>
                    </tr>
                </thead>
                <tbody data-bind="foreach: pagedProjects">
                    <tr data-bind="css: { 'row-even': $index() % 2 === 0, 'row-odd': $index() % 2 !== 0 }">
                        <td class="col-id" data-bind="text: id"></td>
                        <td class="col-name" data-bind="text: name"></td>
                        <td class="col-leader" data-bind="text: leader_name"></td>
                        <td class="col-date" data-bind="text: start_date"></td>
                        <td class="col-members" data-bind="text: member_count"></td>
                        <td class="col-status">
                            <span class="project-status-chip" data-bind="text: status.charAt(0).toUpperCase() + status.slice(1), css: 'status-' + status"></span>
                        </td>
                        <td class="col-action">
                            <!-- ko if: $root.isLeader($data) -->
                            <button class="btn-gear" data-bind="click: function() { $root.openEditPanel($data); }" title="Edit project">
                                <i class="fas fa-cog"></i>
                            </button>
                            <!-- /ko -->
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Empty state -->
            <div class="empty-state" data-bind="visible: filteredProjects().length === 0">
                <p>No projects found.</p>
            </div>

            <!-- Pagination -->
            <div class="pagination-bar" data-bind="visible: filteredProjects().length > 0">
                <button class="page-btn page-nav" data-bind="click: prevPage, enable: currentPage() > 1">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <!-- ko foreach: pageNumbers -->
                <button class="page-btn" data-bind="text: $data, click: function() { $root.goToPage($data); }, css: { 'page-btn-active': $data === $root.currentPage() }"></button>
                <!-- /ko -->
                <button class="page-btn page-nav" data-bind="click: nextPage, enable: currentPage() < totalPages()">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <span class="page-info" data-bind="text: 'Page ' + currentPage() + ' of ' + totalPages() + ' (' + filteredProjects().length + ' projects)'"></span>
            </div>
        </div>
    </div>

    <!-- ===== SIDE PANEL (Create / Edit) ===== -->
    <div class="side-panel-overlay" data-bind="click: closeSidePanel, css: { open: sidePanelOpen() }"></div>
    <div class="side-panel" data-bind="css: { open: sidePanelOpen() }">
        <div class="side-panel-header">
            <h2 data-bind="text: sidePanelTitle()"></h2>
            <button class="side-panel-close" data-bind="click: closeSidePanel"><i class="fas fa-times"></i></button>
        </div>
        <form class="side-panel-body" onsubmit="return false;">
            <input type="hidden" data-bind="value: formProjectId" />

            <div class="form-group">
                <label>Project Name <span class="required">*</span></label>
                <input type="text" required maxlength="255" data-bind="textInput: formName" />
            </div>

            <div class="form-group">
                <label>Leader <span class="required">*</span></label>
                <select required data-bind="value: formLeader, options: allEmployees, optionsText: function(emp) { return emp.name + ' (ID: ' + emp.id + ')'; }, optionsValue: 'id', optionsCaption: 'Select leader...'"></select>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label>Start Date <span class="required">*</span></label>
                    <input type="date" required data-bind="value: formStartDate" />
                </div>
                <div class="form-group half">
                    <label>End Date</label>
                    <input type="date" data-bind="value: formEndDate" />
                </div>
            </div>

            <div class="form-group">
                <label>Status <span class="required">*</span></label>
                <select required data-bind="value: formStatus">
                    <option value="planning">Planning</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div class="form-group">
                <label>Members</label>
                <div class="member-search-wrapper">
                    <i class="fas fa-search member-search-icon"></i>
                    <input type="text" placeholder="Search by employee ID or name..." autocomplete="off" data-bind="textInput: memberSearchText" />
                </div>
                <div class="member-search-results" data-bind="visible: memberSearchResults().length > 0 || memberSearchText().length >= 2">
                    <!-- ko if: memberSearchResults().length === 0 && memberSearchText().length >= 2 -->
                    <div class="search-no-results">No employees found</div>
                    <!-- /ko -->
                    <!-- ko foreach: memberSearchResults -->
                    <div class="search-result-item" data-bind="click: function() { $root.addMember($data); }">
                        <span class="search-result-id" data-bind="text: 'ID: ' + id"></span>
                        <span class="search-result-name" data-bind="text: name"></span>
                    </div>
                    <!-- /ko -->
                </div>
                <div class="member-chips">
                    <!-- ko if: selectedMembers().length === 0 -->
                    <span class="no-members">No members added yet</span>
                    <!-- /ko -->
                    <!-- ko foreach: selectedMembers -->
                    <span class="member-chip">
                        <span data-bind="text: name"></span>
                        <span class="member-chip-id" data-bind="text: '(ID: ' + id + ')'"></span>
                        <button type="button" class="member-chip-remove" data-bind="click: function() { $root.removeMember($data); }">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                    <!-- /ko -->
                </div>
            </div>

            <div class="side-panel-footer">
                <button type="button" class="btn-save" data-bind="click: saveProject">
                    <i class="fas fa-check"></i> Save
                </button>
                <button type="button" class="btn-delete" data-bind="click: confirmDelete, visible: formProjectId()">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>

            <div class="form-message" data-bind="text: formMessage, css: formMessageType"></div>
        </form>
    </div>

    <!-- ===== DELETE CONFIRMATION DIALOG ===== -->
    <div class="confirm-overlay" data-bind="css: { open: confirmDialogOpen() }">
        <div class="confirm-dialog">
            <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h3>Delete Project?</h3>
            <p>This action cannot be undone. All member assignments will be removed.</p>
            <div class="confirm-actions">
                <button class="btn-confirm-cancel" data-bind="click: closeConfirmDialog">Cancel</button>
                <button class="btn-confirm-delete" data-bind="click: deleteProject">Delete</button>
            </div>
        </div>
    </div>

<script>
// ===== DATA =====
var allProjectsData = <?php echo $projects_json; ?>;
var allEmployeesData = <?php echo $employees_json; ?>;
var currentUserId = <?php echo (int)$current_user['id']; ?>;
var ITEMS_PER_PAGE = 10;

// ===== VIEW MODEL =====
function ProjectViewModel() {
    var self = this;

    // ===== DATA =====
    self.allProjects = ko.observableArray(allProjectsData);
    self.allEmployees = ko.observableArray(allEmployeesData);
    self.currentUserId = currentUserId;

    // ===== UI STATE =====
    self.dropdownOpen = ko.observable(false);
    self.sidePanelOpen = ko.observable(false);
    self.confirmDialogOpen = ko.observable(false);

    // ===== FILTER STATE =====
    self.searchText = ko.observable('');
    self.statusFilter = ko.observable('');
    self.sortBy = ko.observable('id');
    self.showMyProjectsOnly = ko.observable(false);

    // ===== PAGINATION =====
    self.currentPage = ko.observable(1);

    // ===== FORM STATE =====
    self.sidePanelTitle = ko.observable('Add New Project');
    self.formProjectId = ko.observable('');
    self.formName = ko.observable('');
    self.formLeader = ko.observable('');
    self.formStartDate = ko.observable('');
    self.formEndDate = ko.observable('');
    self.formStatus = ko.observable('planning');
    self.formMessage = ko.observable('');
    self.formMessageType = ko.observable('');

    // ===== MEMBER SEARCH =====
    self.memberSearchText = ko.observable('');
    self.memberSearchResults = ko.observableArray([]);
    self.selectedMembers = ko.observableArray([]);

    // ===== HELPER: Check if current user is related to a project (leader or member) =====
    self.isRelatedToUser = function(project) {
        if (parseInt(project.leader_id) === self.currentUserId) {
            return true;
        }
        if (project.member_ids) {
            var ids = String(project.member_ids).split(',').map(function(id) { return parseInt(id); });
            return ids.indexOf(self.currentUserId) !== -1;
        }
        return false;
    };

    // ===== COMPUTED: FILTERED PROJECTS =====
    self.filteredProjects = ko.computed(function() {
        var search = self.searchText().trim().toLowerCase();
        var status = self.statusFilter();
        var myOnly = self.showMyProjectsOnly();
        var sortKey = self.sortBy();

        var result = self.allProjects().filter(function(p) {
            var matchSearch = !search ||
                p.name.toLowerCase().indexOf(search) !== -1 ||
                String(p.id).indexOf(search) !== -1;
            var matchStatus = !status || p.status === status;
            var matchMine = !myOnly || self.isRelatedToUser(p);
            return matchSearch && matchStatus && matchMine;
        });

        // Sort
        result = result.slice().sort(function(a, b) {
            if (sortKey === 'name') {
                return a.name.localeCompare(b.name);
            } else if (sortKey === 'date') {
                return new Date(a.start_date) - new Date(b.start_date);
            }
            // default: sort by ID
            return parseInt(a.id) - parseInt(b.id);
        });

        return result;
    });

    // Reset to page 1 when filters change
    self.filteredProjects.subscribe(function() {
        self.currentPage(1);
    });

    // ===== COMPUTED: PAGINATION =====
    self.totalPages = ko.computed(function() {
        return Math.max(1, Math.ceil(self.filteredProjects().length / ITEMS_PER_PAGE));
    });

    self.pageNumbers = ko.computed(function() {
        var pages = [];
        for (var i = 1; i <= self.totalPages(); i++) {
            pages.push(i);
        }
        return pages;
    });

    self.pagedProjects = ko.computed(function() {
        var start = (self.currentPage() - 1) * ITEMS_PER_PAGE;
        return self.filteredProjects().slice(start, start + ITEMS_PER_PAGE);
    });

    // ===== COMPUTED: MEMBER SEARCH (INSTANT) =====
    self.memberSearchText.subscribe(function(query) {
        query = query.trim();
        if (query.length >= 2) {
            self.searchMembers(query);
        } else {
            self.memberSearchResults([]);
        }
    });

    // ===== METHODS: DROPDOWN =====
    self.toggleDropdown = function() {
        self.dropdownOpen(!self.dropdownOpen());
    };

    // ===== METHODS: MY PROJECTS TOGGLE =====
    self.toggleMyProjects = function() {
        self.showMyProjectsOnly(!self.showMyProjectsOnly());
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        var menu = document.getElementById('menuDropdown');
        var btn = document.getElementById('hamburgerBtn');
        if (menu && btn && !menu.contains(e.target) && !btn.contains(e.target)) {
            self.dropdownOpen(false);
        }
    });

    // ===== METHODS: PAGINATION =====
    self.goToPage = function(page) {
        if (page >= 1 && page <= self.totalPages()) {
            self.currentPage(page);
        }
    };

    self.prevPage = function() {
        self.goToPage(self.currentPage() - 1);
    };

    self.nextPage = function() {
        self.goToPage(self.currentPage() + 1);
    };

    // ===== METHODS: HELPER =====
    self.isLeader = function(project) {
        return parseInt(project.leader_id) === self.currentUserId;
    };

    self.escapeHtml = function(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    };

    // ===== METHODS: SIDE PANEL =====
    self.openCreatePanel = function() {
        self.sidePanelTitle('Add New Project');
        self.formProjectId('');
        self.formName('');
        self.formLeader(self.currentUserId);
        // Set start date to today (YYYY-MM-DD format)
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var dd = String(today.getDate()).padStart(2, '0');
        self.formStartDate(yyyy + '-' + mm + '-' + dd);
        self.formEndDate('');
        self.formStatus('planning');
        self.formMessage('');
        self.formMessageType('');
        self.selectedMembers([]);
        self.memberSearchText('');
        self.memberSearchResults([]);
        self.sidePanelOpen(true);
    };

    self.openEditPanel = function(project) {
        // Fetch project data with members
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/project/get?id=' + project.id, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    var p = data.project;
                    self.sidePanelTitle('Edit Project #' + p.id);
                    self.formProjectId(p.id);
                    self.formName(p.name);
                    self.formLeader(p.leader_id);
                    self.formStartDate(p.start_date);
                    self.formEndDate(p.end_date);
                    self.formStatus(p.status);
                    self.formMessage('');
                    self.formMessageType('');

                    self.selectedMembers(data.members.map(function(m) {
                        return { id: parseInt(m.id), name: m.name };
                    }));

                    self.memberSearchText('');
                    self.memberSearchResults([]);
                    self.sidePanelOpen(true);
                }
            }
        };
        xhr.send();
    };

    self.closeSidePanel = function() {
        self.sidePanelOpen(false);
        self.memberSearchText('');
        self.memberSearchResults([]);
    };

    // ===== METHODS: MEMBER SEARCH =====
    self.searchMembers = function(query) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/project/search_employee?q=' + encodeURIComponent(query), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    // Filter out already added members
                    var results = data.results.filter(function(emp) {
                        return !self.selectedMembers().some(function(m) {
                            return m.id === parseInt(emp.id);
                        });
                    });
                    self.memberSearchResults(results);
                }
            }
        };
        xhr.send();
    };

    self.addMember = function(employee) {
        var intId = parseInt(employee.id);
        if (!self.selectedMembers().some(function(m) { return m.id === intId; })) {
            self.selectedMembers.push({ id: intId, name: employee.name });
        }
        self.memberSearchText('');
        self.memberSearchResults([]);
    };

    self.removeMember = function(member) {
        self.selectedMembers.remove(member);
    };

    // ===== METHODS: SAVE =====
    self.saveProject = function() {
        var projectId = self.formProjectId();
        var name = self.formName().trim();
        var leaderId = self.formLeader();
        var startDate = self.formStartDate();
        var endDate = self.formEndDate();
        var status = self.formStatus();
        var memberIds = self.selectedMembers().map(function(m) { return m.id; });

        // Validate
        if (!name) { self.showFormMessage('Project name is required', 'error'); return; }
        if (!leaderId) { self.showFormMessage('Leader is required', 'error'); return; }
        if (!startDate) { self.showFormMessage('Start date is required', 'error'); return; }

        var url = projectId ? '/project/update' : '/project/create';
        var params = 'name=' + encodeURIComponent(name) +
                     '&leader_id=' + encodeURIComponent(leaderId) +
                     '&start_date=' + encodeURIComponent(startDate) +
                     '&end_date=' + encodeURIComponent(endDate) +
                     '&status=' + encodeURIComponent(status) +
                     '&members=' + encodeURIComponent(JSON.stringify(memberIds));

        if (projectId) {
            params += '&project_id=' + encodeURIComponent(projectId);
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    self.showFormMessage(projectId ? 'Project updated successfully!' : 'Project created successfully!', 'success');
                    setTimeout(function() {
                        self.closeSidePanel();
                        self.refreshProjects();
                    }, 800);
                } else {
                    self.showFormMessage(data.error || 'An error occurred', 'error');
                }
            } catch (e) {
                self.showFormMessage('Server error: ' + xhr.responseText.substring(0, 100), 'error');
            }
        };
        xhr.onerror = function() {
            self.showFormMessage('Network error. Please try again.', 'error');
        };
        xhr.send(params);
    };

    // ===== METHODS: DELETE =====
    self.confirmDelete = function() {
        self.confirmDialogOpen(true);
    };

    self.closeConfirmDialog = function() {
        self.confirmDialogOpen(false);
    };

    self.deleteProject = function() {
        var projectId = self.formProjectId();
        if (!projectId) return;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/project/delete', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var data = JSON.parse(xhr.responseText);
            if (data.success) {
                self.closeConfirmDialog();
                self.closeSidePanel();
                self.refreshProjects();
            } else {
                self.showFormMessage(data.error || 'Failed to delete project', 'error');
                self.closeConfirmDialog();
            }
        };
        xhr.send('project_id=' + encodeURIComponent(projectId));
    };

    // ===== METHODS: REFRESH =====
    self.refreshProjects = function() {
        window.location.reload();
    };

    // ===== METHODS: FORM MESSAGE =====
    self.showFormMessage = function(msg, type) {
        self.formMessage(msg);
        self.formMessageType(type === 'error' ? 'msg-error' : 'msg-success');
    };
}

// ===== APPLY BINDINGS =====
ko.applyBindings(new ProjectViewModel());
</script>

</body>
</html>