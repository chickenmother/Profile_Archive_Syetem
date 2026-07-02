<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management - Profile Archive System</title>
    <?php echo Asset::css('project.css'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <button class="hamburger-btn" id="hamburgerBtn" onclick="toggleDropdown()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- ===== HAMBURGER DROPDOWN MENU ===== -->
    <div class="user-profile-menu-dropdown" id="menuDropdown">
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
                <input type="text" id="searchInput" placeholder="Search by project name or ID..." />
            </div>
            <div class="filter-wrapper">
                <select id="statusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="planning">Planning</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>
        <button class="btn-add-project" id="btnAddProject" onclick="openSidePanel()">
            <i class="fas fa-plus"></i> Add New Project
        </button>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="project-container">
        <div class="table-panel">
            <table class="project-table" id="projectTable">
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
                <tbody id="projectTableBody">
                    <!-- Rendered by JS -->
                </tbody>
            </table>

            <!-- Empty state -->
            <div class="empty-state" id="emptyState" style="display:none;">
                <p>No projects found.</p>
            </div>

            <!-- Pagination -->
            <div class="pagination-bar" id="paginationBar"></div>
        </div>
    </div>

    <!-- ===== SIDE PANEL (Create / Edit) ===== -->
    <div class="side-panel-overlay" id="sidePanelOverlay" onclick="closeSidePanel()"></div>
    <div class="side-panel" id="sidePanel">
        <div class="side-panel-header">
            <h2 id="sidePanelTitle">Add New Project</h2>
            <button class="side-panel-close" onclick="closeSidePanel()"><i class="fas fa-times"></i></button>
        </div>
        <form id="projectForm" class="side-panel-body" onsubmit="return false;">
            <input type="hidden" id="formProjectId" value="" />

            <div class="form-group">
                <label for="formName">Project Name <span class="required">*</span></label>
                <input type="text" id="formName" required maxlength="255" />
            </div>

            <div class="form-group">
                <label for="formLeader">Leader <span class="required">*</span></label>
                <select id="formLeader" required>
                    <option value="">Select leader...</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label for="formStartDate">Start Date <span class="required">*</span></label>
                    <input type="date" id="formStartDate" required />
                </div>
                <div class="form-group half">
                    <label for="formEndDate">End Date <span class="required">*</span></label>
                    <input type="date" id="formEndDate" required />
                </div>
            </div>

            <div class="form-group">
                <label for="formStatus">Status <span class="required">*</span></label>
                <select id="formStatus" required>
                    <option value="planning">Planning</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div class="form-group">
                <label>Members</label>
                <div class="member-search-wrapper">
                    <i class="fas fa-search member-search-icon"></i>
                    <input type="text" id="memberSearchInput" placeholder="Search by employee ID or name..." autocomplete="off" />
                </div>
                <div class="member-search-results" id="memberSearchResults"></div>
                <div class="member-chips" id="memberChips"></div>
            </div>

            <div class="side-panel-footer">
                <button type="button" class="btn-save" id="btnSave" onclick="saveProject()">
                    <i class="fas fa-check"></i> Save
                </button>
                <button type="button" class="btn-delete" id="btnDelete" onclick="confirmDelete()" style="display:none;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>

            <div class="form-message" id="formMessage"></div>
        </form>
    </div>

    <!-- ===== DELETE CONFIRMATION DIALOG ===== -->
    <div class="confirm-overlay" id="confirmOverlay">
        <div class="confirm-dialog">
            <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h3>Delete Project?</h3>
            <p>This action cannot be undone. All member assignments will be removed.</p>
            <div class="confirm-actions">
                <button class="btn-confirm-cancel" onclick="closeConfirmDialog()">Cancel</button>
                <button class="btn-confirm-delete" onclick="deleteProject()">Delete</button>
            </div>
        </div>
    </div>

<script>
// ===== DATA =====
var allProjects = <?php echo $projects_json; ?>;
var allEmployees = <?php echo $employees_json; ?>;
var currentUserId = <?php echo (int)$current_user['id']; ?>;
var ITEMS_PER_PAGE = 10;
var currentPage = 1;
var selectedMembers = []; // array of {id, name}

// ===== INIT =====
document.addEventListener('DOMContentLoaded', function() {
    populateLeaderDropdown();
    renderTable();

    document.getElementById('searchInput').addEventListener('input', function() {
        currentPage = 1;
        renderTable();
    });
    document.getElementById('statusFilter').addEventListener('change', function() {
        currentPage = 1;
        renderTable();
    });

    // Member search
    document.getElementById('memberSearchInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchMembers(this.value);
        }
    });
    document.getElementById('memberSearchInput').addEventListener('input', function() {
        var val = this.value.trim();
        if (val.length >= 2) {
            searchMembers(val);
        } else {
            document.getElementById('memberSearchResults').innerHTML = '';
        }
    });
});

// ===== HAMBURGER DROPDOWN =====
function toggleDropdown() {
    document.getElementById('menuDropdown').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    var menu = document.getElementById('menuDropdown');
    var btn = document.getElementById('hamburgerBtn');
    if (!menu.contains(e.target) && !btn.contains(e.target)) {
        menu.classList.remove('open');
    }
});

// ===== FILTERING =====
function getFilteredProjects() {
    var search = document.getElementById('searchInput').value.trim().toLowerCase();
    var status = document.getElementById('statusFilter').value;

    return allProjects.filter(function(p) {
        var matchSearch = !search ||
            p.name.toLowerCase().indexOf(search) !== -1 ||
            String(p.id).indexOf(search) !== -1;
        var matchStatus = !status || p.status === status;
        return matchSearch && matchStatus;
    });
}

// ===== RENDER TABLE =====
function renderTable() {
    var filtered = getFilteredProjects();
    var totalPages = Math.max(1, Math.ceil(filtered.length / ITEMS_PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;

    var start = (currentPage - 1) * ITEMS_PER_PAGE;
    var pageItems = filtered.slice(start, start + ITEMS_PER_PAGE);

    var tbody = document.getElementById('projectTableBody');
    var emptyState = document.getElementById('emptyState');

    if (filtered.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'block';
    } else {
        emptyState.style.display = 'none';
        var html = '';
        for (var i = 0; i < pageItems.length; i++) {
            var p = pageItems[i];
            var isLeader = (parseInt(p.leader_id) === currentUserId);
            var statusClass = 'status-' + p.status;
            var statusLabel = p.status.charAt(0).toUpperCase() + p.status.slice(1);

            html += '<tr class="' + (i % 2 === 0 ? 'row-even' : 'row-odd') + '">';
            html += '<td class="col-id">' + p.id + '</td>';
            html += '<td class="col-name">' + escapeHtml(p.name) + '</td>';
            html += '<td class="col-leader">' + escapeHtml(p.leader_name) + '</td>';
            html += '<td class="col-date">' + p.start_date + '</td>';
            html += '<td class="col-members">' + p.member_count + '</td>';
            html += '<td class="col-status"><span class="project-status-chip ' + statusClass + '">' + statusLabel + '</span></td>';
            html += '<td class="col-action">';
            if (isLeader) {
                html += '<button class="btn-gear" onclick="openEditPanel(' + p.id + ')" title="Edit project"><i class="fas fa-cog"></i></button>';
            }
            html += '</td>';
            html += '</tr>';
        }
        tbody.innerHTML = html;
    }

    renderPagination(filtered.length, totalPages);
}

// ===== PAGINATION =====
function renderPagination(total, totalPages) {
    var bar = document.getElementById('paginationBar');
    if (totalPages <= 1) {
        bar.innerHTML = '';
        return;
    }

    var html = '';
    // Prev
    html += '<button class="page-btn page-nav" ' + (currentPage <= 1 ? 'disabled' : '') + ' onclick="goToPage(' + (currentPage - 1) + ')"><i class="fas fa-chevron-left"></i></button>';

    for (var i = 1; i <= totalPages; i++) {
        html += '<button class="page-btn ' + (i === currentPage ? 'page-btn-active' : '') + '" onclick="goToPage(' + i + ')">' + i + '</button>';
    }

    // Next
    html += '<button class="page-btn page-nav" ' + (currentPage >= totalPages ? 'disabled' : '') + ' onclick="goToPage(' + (currentPage + 1) + ')"><i class="fas fa-chevron-right"></i></button>';

    html += '<span class="page-info">Page ' + currentPage + ' of ' + totalPages + ' (' + total + ' projects)</span>';

    bar.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    renderTable();
}

// ===== LEADER DROPDOWN =====
function populateLeaderDropdown() {
    var sel = document.getElementById('formLeader');
    // Keep the first placeholder option
    for (var i = 0; i < allEmployees.length; i++) {
        var opt = document.createElement('option');
        opt.value = allEmployees[i].id;
        opt.textContent = allEmployees[i].name + ' (ID: ' + allEmployees[i].id + ')';
        sel.appendChild(opt);
    }
}

// ===== SIDE PANEL =====
function openSidePanel() {
    // Create mode
    document.getElementById('sidePanelTitle').textContent = 'Add New Project';
    document.getElementById('formProjectId').value = '';
    document.getElementById('formName').value = '';
    document.getElementById('formLeader').value = '';
    document.getElementById('formStartDate').value = '';
    document.getElementById('formEndDate').value = '';
    document.getElementById('formStatus').value = 'planning';
    document.getElementById('btnDelete').style.display = 'none';
    document.getElementById('formMessage').textContent = '';
    selectedMembers = [];
    renderMemberChips();

    document.getElementById('sidePanel').classList.add('open');
    document.getElementById('sidePanelOverlay').classList.add('open');
}

function openEditPanel(projectId) {
    // Fetch project data with members
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/project/get?id=' + projectId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            if (data.success) {
                var p = data.project;
                document.getElementById('sidePanelTitle').textContent = 'Edit Project #' + p.id;
                document.getElementById('formProjectId').value = p.id;
                document.getElementById('formName').value = p.name;
                document.getElementById('formLeader').value = p.leader_id;
                document.getElementById('formStartDate').value = p.start_date;
                document.getElementById('formEndDate').value = p.end_date;
                document.getElementById('formStatus').value = p.status;
                document.getElementById('btnDelete').style.display = 'flex';
                document.getElementById('formMessage').textContent = '';

                selectedMembers = data.members.map(function(m) {
                    return { id: parseInt(m.id), name: m.name };
                });
                renderMemberChips();

                document.getElementById('sidePanel').classList.add('open');
                document.getElementById('sidePanelOverlay').classList.add('open');
            }
        }
    };
    xhr.send();
}

function closeSidePanel() {
    document.getElementById('sidePanel').classList.remove('open');
    document.getElementById('sidePanelOverlay').classList.remove('open');
    document.getElementById('memberSearchInput').value = '';
    document.getElementById('memberSearchResults').innerHTML = '';
}

// ===== MEMBER SEARCH =====
function searchMembers(query) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/project/search_employee?q=' + encodeURIComponent(query), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            if (data.success) {
                renderSearchResults(data.results);
            }
        }
    };
    xhr.send();
}

function renderSearchResults(results) {
    var container = document.getElementById('memberSearchResults');
    if (results.length === 0) {
        container.innerHTML = '<div class="search-no-results">No employees found</div>';
        return;
    }

    var html = '';
    for (var i = 0; i < results.length; i++) {
        var emp = results[i];
        var alreadyAdded = selectedMembers.some(function(m) { return m.id === parseInt(emp.id); });
        if (!alreadyAdded) {
            html += '<div class="search-result-item" onclick="addMember(' + emp.id + ', \'' + escapeHtml(emp.name).replace(/'/g, "\\'") + '\')">';
            html += '<span class="search-result-id">ID: ' + emp.id + '</span>';
            html += '<span class="search-result-name">' + escapeHtml(emp.name) + '</span>';
            html += '</div>';
        }
    }
    container.innerHTML = html;
}

function addMember(id, name) {
    var intId = parseInt(id);
    if (!selectedMembers.some(function(m) { return m.id === intId; })) {
        selectedMembers.push({ id: intId, name: name });
        renderMemberChips();
    }
    document.getElementById('memberSearchInput').value = '';
    document.getElementById('memberSearchResults').innerHTML = '';
}

function removeMember(id) {
    selectedMembers = selectedMembers.filter(function(m) { return m.id !== id; });
    renderMemberChips();
}

function renderMemberChips() {
    var container = document.getElementById('memberChips');
    if (selectedMembers.length === 0) {
        container.innerHTML = '<span class="no-members">No members added yet</span>';
        return;
    }

    var html = '';
    for (var i = 0; i < selectedMembers.length; i++) {
        var m = selectedMembers[i];
        html += '<span class="member-chip">';
        html += escapeHtml(m.name) + ' <span class="member-chip-id">(ID: ' + m.id + ')</span>';
        html += '<button type="button" class="member-chip-remove" onclick="removeMember(' + m.id + ')"><i class="fas fa-times"></i></button>';
        html += '</span>';
    }
    container.innerHTML = html;
}

// ===== SAVE (Create or Update) =====
function saveProject() {
    var projectId = document.getElementById('formProjectId').value;
    var name = document.getElementById('formName').value.trim();
    var leaderId = document.getElementById('formLeader').value;
    var startDate = document.getElementById('formStartDate').value;
    var endDate = document.getElementById('formEndDate').value;
    var status = document.getElementById('formStatus').value;
    var memberIds = selectedMembers.map(function(m) { return m.id; });

    // Validate
    if (!name) { showFormMessage('Project name is required', 'error'); return; }
    if (!leaderId) { showFormMessage('Leader is required', 'error'); return; }
    if (!startDate || !endDate) { showFormMessage('Start and end dates are required', 'error'); return; }

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
                showFormMessage(projectId ? 'Project updated successfully!' : 'Project created successfully!', 'success');
                setTimeout(function() {
                    closeSidePanel();
                    refreshProjects();
                }, 800);
            } else {
                showFormMessage(data.error || 'An error occurred', 'error');
            }
        } catch (e) {
            showFormMessage('Server error: ' + xhr.responseText.substring(0, 100), 'error');
        }
    };
    xhr.onerror = function() {
        showFormMessage('Network error. Please try again.', 'error');
    };
    xhr.send(params);
}

// ===== DELETE =====
function confirmDelete() {
    document.getElementById('confirmOverlay').classList.add('open');
}

function closeConfirmDialog() {
    document.getElementById('confirmOverlay').classList.remove('open');
}

function deleteProject() {
    var projectId = document.getElementById('formProjectId').value;
    if (!projectId) return;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/project/delete', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        var data = JSON.parse(xhr.responseText);
        if (data.success) {
            closeConfirmDialog();
            closeSidePanel();
            refreshProjects();
        } else {
            showFormMessage(data.error || 'Failed to delete project', 'error');
            closeConfirmDialog();
        }
    };
    xhr.send('project_id=' + encodeURIComponent(projectId));
}

// ===== REFRESH =====
function refreshProjects() {
    // Reload the page to get updated project list
    window.location.reload();
}

// ===== HELPERS =====
function showFormMessage(msg, type) {
    var el = document.getElementById('formMessage');
    el.textContent = msg;
    el.className = 'form-message ' + (type === 'error' ? 'msg-error' : 'msg-success');
}

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>

</body>
</html>