<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB Management - Profile Archive System</title>
    <?php echo Asset::css('project.css'); ?>
    <?php echo Asset::css('db.css'); ?>
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
        <a href="/account" class="dropdown-nav-item">
            <span><i class="fas fa-user-cog"></i> Account</span>
        </a>
        <a href="/project" class="dropdown-nav-item">
            <span><i class="fas fa-project-diagram"></i> Project</span>
        </a>
        <a href="/db" class="dropdown-nav-item" style="background-color: #f0f2ff;">
            <span><i class="fas fa-database"></i> Database</span>
        </a>
        <a href="/auth/logout" class="dropdown-nav-item logout-trigger">
            <span><i class="fas fa-sign-out-alt"></i> Logout</span>
        </a>
    </div>

    <!-- ===== MAIN LAYOUT: LEFT + MIDDLE ===== -->
    <div class="db-layout">

        <!-- ===== LEFT PANEL: TABLE LIST ===== -->
        <div class="db-left-panel">
            <div class="db-left-panel-header">Tables</div>
            <!-- ko foreach: tableList -->
            <div class="db-table-nav-item" data-bind="click: function() { $root.selectTable($data.table); }, css: { active: $root.selectedTable() === $data.table }">
                <i class="fas" data-bind="css: $data.icon"></i>
                <span data-bind="text: $data.label"></span>
            </div>
            <!-- /ko -->
        </div>

        <!-- ===== MIDDLE PANEL: GRID ===== -->
        <div class="db-middle-panel">
            <div class="db-grid-header">
                <div class="db-grid-title">
                    <span data-bind="text: currentTableLabel()"></span>
                    <span class="row-count" data-bind="text: '(' + filteredRows().length + ' of ' + currentRows().length + ' rows)'"></span>
                </div>
                <button class="btn-add-row" data-bind="click: openCreatePanel">
                    <i class="fas fa-plus"></i> Add New Row
                </button>
            </div>

            <!-- ===== FILTER TOOLBAR ===== -->
            <div class="db-toolbar">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" placeholder="Search by keyword..." data-bind="textInput: searchText" />
                </div>
                <!-- ko foreach: filterableColumns -->
                <div class="filter-wrapper">
                    <select class="filter-select" data-bind="value: $root.filterValues[$data.name], options: $data.options, optionsText: 'label', optionsValue: 'value', optionsCaption: $data.caption"></select>
                </div>
                <!-- /ko -->
            </div>

            <div class="form-message" data-bind="text: gridMessage, css: gridMessageType, visible: gridMessage()"></div>

            <div class="db-table-panel">
                <!-- ko if: loadingRows() -->
                <div class="db-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                <!-- /ko -->

                <!-- ko ifnot: loadingRows() -->
                <table class="db-table" data-bind="visible: pagedRows().length > 0">
                    <thead>
                        <tr>
                            <!-- ko foreach: currentColumns -->
                            <th data-bind="text: $data.label"></th>
                            <!-- /ko -->
                            <th class="db-col-action"></th>
                        </tr>
                    </thead>
                    <tbody data-bind="foreach: pagedRows">
                        <tr data-bind="css: { 'row-even': $index() % 2 === 0, 'row-odd': $index() % 2 !== 0 }">
                            <!-- ko foreach: $root.currentColumns -->
                            <td data-bind="text: $root.getCellValue($parent, $data)"></td>
                            <!-- /ko -->
                            <td class="db-col-action">
                                <button class="btn-gear" data-bind="click: function() { $root.openEditPanel($data); }" title="Edit row">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <button class="btn-row-delete" data-bind="click: function() { $root.confirmDeleteFromGrid($data); }" title="Delete row">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="db-empty-state" data-bind="visible: pagedRows().length === 0">
                    <p data-bind="text: currentRows().length === 0 ? 'No rows found in this table.' : 'No rows match your search/filter.'"></p>
                </div>

                <!-- Pagination -->
                <div class="pagination-bar" data-bind="visible: filteredRows().length > 0">
                    <button class="page-btn page-nav" data-bind="click: prevPage, enable: currentPage() > 1">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <!-- ko foreach: pageNumbers -->
                    <button class="page-btn" data-bind="text: $data, click: function() { $root.goToPage($data); }, css: { 'page-btn-active': $data === $root.currentPage() }"></button>
                    <!-- /ko -->
                    <button class="page-btn page-nav" data-bind="click: nextPage, enable: currentPage() < totalPages()">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <span class="page-info" data-bind="text: 'Page ' + currentPage() + ' of ' + totalPages() + ' (' + filteredRows().length + ' rows)'"></span>
                </div>
                <!-- /ko -->
            </div>
        </div>
    </div>

    <!-- ===== RIGHT SIDE PANEL (Create / Edit) ===== -->
    <div class="side-panel-overlay" data-bind="click: closeSidePanel, css: { open: sidePanelOpen() }"></div>
    <div class="side-panel" data-bind="css: { open: sidePanelOpen() }">
        <div class="side-panel-header">
            <h2 data-bind="text: sidePanelTitle()"></h2>
            <button class="side-panel-close" data-bind="click: closeSidePanel"><i class="fas fa-times"></i></button>
        </div>
        <form class="side-panel-body" onsubmit="return false;">

            <!-- ko foreach: formFields -->
            <div class="form-group">
                <label>
                    <span data-bind="text: $data.label"></span>
                    <span class="required" data-bind="visible: $data.required">*</span>
                </label>

                <!-- ko if: $data.type === 'text' -->
                <input type="text" data-bind="value: $root.formValues[$data.name]" />
                <!-- /ko -->

                <!-- ko if: $data.type === 'number' -->
                <input type="number" data-bind="value: $root.formValues[$data.name]" />
                <!-- /ko -->

                <!-- ko if: $data.type === 'date' -->
                <input type="date" data-bind="value: $root.formValues[$data.name]" />
                <!-- /ko -->

                <!-- ko if: $data.type === 'textarea' -->
                <textarea data-bind="value: $root.formValues[$data.name]"></textarea>
                <!-- /ko -->

                <!-- ko if: $data.type === 'select' || $data.type === 'fk_select' || $data.type === 'config_select' -->
                <select data-bind="value: $root.formValues[$data.name], options: $data.options, optionsText: 'label', optionsValue: 'value', optionsCaption: 'Select...'"></select>
                <!-- /ko -->
            </div>
            <!-- /ko -->

            <div class="side-panel-footer">
                <button type="button" class="btn-save" data-bind="click: savePanel">
                    <i class="fas fa-check"></i> Save
                </button>
                <button type="button" class="btn-delete" data-bind="click: confirmDeleteFromPanel, visible: formMode() === 'edit'">
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
            <h3>Delete Row?</h3>
            <p>This action cannot be undone. Related records may also be affected.</p>
            <div class="confirm-actions">
                <button class="btn-confirm-cancel" data-bind="click: closeConfirmDialog">Cancel</button>
                <button class="btn-confirm-delete" data-bind="click: executeDelete">Delete</button>
            </div>
        </div>
    </div>

<script>
// ===== DATA FROM SERVER =====
var dbSchema = <?php echo $schema_json; ?>;
var dbTableList = <?php echo $table_list_json; ?>;

// ===== VIEW MODEL =====
function DbViewModel() {
    var self = this;

    // ===== DATA =====
    self.schema = dbSchema;
    self.tableList = ko.observableArray(dbTableList);

    // ===== UI STATE =====
    self.dropdownOpen = ko.observable(false);
    self.sidePanelOpen = ko.observable(false);
    self.confirmDialogOpen = ko.observable(false);

    // ===== GRID STATE =====
    self.selectedTable = ko.observable('');
    self.currentColumns = ko.observableArray([]);
    self.currentRows = ko.observableArray([]);
    self.loadingRows = ko.observable(false);
    self.gridMessage = ko.observable('');
    self.gridMessageType = ko.observable('');

    // ===== FILTER / SEARCH / PAGINATION STATE =====
    self.searchText = ko.observable('');
    self.filterableColumns = ko.observableArray([]); // [{ name, label, caption, options: [{value,label}] }]
    self.filterValues = {}; // { colName: ko.observable(value) }
    self.currentPage = ko.observable(1);
    self.pageSize = 10;

    // ===== FORM STATE =====
    self.sidePanelTitle = ko.observable('');
    self.formFields = ko.observableArray([]);
    self.formValues = {};
    self.formMode = ko.observable('create'); // 'create' | 'edit'
    self.formKey = null; // primary key value, or {col: val, ...} for composite keys
    self.formMessage = ko.observable('');
    self.formMessageType = ko.observable('');

    // ===== PENDING DELETE =====
    self.pendingDeleteParams = '';

    // ===== COMPUTED =====
    self.currentTableLabel = ko.computed(function() {
        var t = self.selectedTable();
        return (t && self.schema[t]) ? self.schema[t].label : '';
    });

    // Rows after keyword search + dropdown filters applied
    self.filteredRows = ko.computed(function() {
        var rows = self.currentRows();
        var cols = self.currentColumns();
        var keyword = (self.searchText() || '').trim().toLowerCase();
        var filters = self.filterableColumns();

        // Apply dropdown filters first
        filters.forEach(function(f) {
            var selected = self.filterValues[f.name] ? self.filterValues[f.name]() : '';
            if (selected !== '' && selected !== undefined && selected !== null) {
                rows = rows.filter(function(row) {
                    return String(row[f.name]) === String(selected);
                });
            }
        });

        // Apply keyword search across all visible columns' displayed values
        if (keyword !== '') {
            rows = rows.filter(function(row) {
                return cols.some(function(col) {
                    var val = self.getCellValue(row, col);
                    return String(val).toLowerCase().indexOf(keyword) !== -1;
                });
            });
        }

        return rows;
    });

    self.totalPages = ko.computed(function() {
        return Math.max(1, Math.ceil(self.filteredRows().length / self.pageSize));
    });

    self.pagedRows = ko.computed(function() {
        var rows = self.filteredRows();
        var page = self.currentPage();
        var start = (page - 1) * self.pageSize;
        return rows.slice(start, start + self.pageSize);
    });

    self.pageNumbers = ko.computed(function() {
        var total = self.totalPages();
        var out = [];
        for (var i = 1; i <= total; i++) out.push(i);
        return out;
    });

    // Reset to page 1 whenever the filtered result set changes (search/filter/table switch)
    self.filteredRows.subscribe(function() {
        self.currentPage(1);
    });

    self.prevPage = function() {
        if (self.currentPage() > 1) self.currentPage(self.currentPage() - 1);
    };
    self.nextPage = function() {
        if (self.currentPage() < self.totalPages()) self.currentPage(self.currentPage() + 1);
    };
    self.goToPage = function(page) {
        self.currentPage(page);
    };

    // ===== METHODS: DROPDOWN =====
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

    // ===== METHODS: SCHEMA HELPERS =====
    self.getGridColumnsForTable = function(table) {
        var cols = self.schema[table].columns;
        var out = [];
        Object.keys(cols).forEach(function(name) {
            var c = cols[name];
            if (c.hidden) return;
            out.push({ name: name, label: c.label, type: c.type });
        });
        return out;
    };

    self.getFormFieldsForTable = function(table) {
        var cols = self.schema[table].columns;
        var out = [];
        Object.keys(cols).forEach(function(name) {
            var c = cols[name];
            if (c.hidden || c.readonly) return;
            var field = {
                name: name,
                label: c.label,
                type: c.type,
                required: !!c.required,
                default: (c.default !== undefined) ? c.default : '',
                options: null
            };
            if (c.type === 'select' && c.options) {
                field.options = Object.keys(c.options).map(function(k) {
                    return { value: k, label: c.options[k] };
                });
            }
            out.push(field);
        });
        return out;
    };

    self.loadOptionsForFields = function(table, fields, callback) {
        var needed = fields.filter(function(f) {
            return f.type === 'fk_select' || f.type === 'config_select';
        });

        if (needed.length === 0) {
            callback();
            return;
        }

        var pending = needed.length;
        needed.forEach(function(f) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '/db/get_fk_options?table=' + encodeURIComponent(table) + '&column=' + encodeURIComponent(f.name), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            f.options = data.options.map(function(o) {
                                return { value: o.id, label: o.name };
                            });
                        }
                    } catch (e) {}
                }
                pending--;
                if (pending === 0) callback();
            };
            xhr.onerror = function() {
                pending--;
                if (pending === 0) callback();
            };
            xhr.send();
        });
    };

    self.getCellValue = function(row, col) {
        if (!row) return '';
        var val;
        if (col.type === 'fk_select' || col.type === 'config_select') {
            val = row[col.name + '_display'];
        } else {
            val = row[col.name];
        }
        if (val === null || val === undefined || val === '') return '—';
        return val;
    };

    self.getRowKey = function(table, row) {
        var tableSchema = self.schema[table];
        if (tableSchema.primary_key) {
            return row[tableSchema.primary_key];
        }
        var key = {};
        tableSchema.composite_key.forEach(function(k) { key[k] = row[k]; });
        return key;
    };

    // ===== METHODS: FILTER HELPERS =====
    self.getFilterableColumnsForTable = function(table, callback) {
        var cols = self.schema[table].columns;
        var out = [];
        Object.keys(cols).forEach(function(name) {
            var c = cols[name];
            if (!c.filterable) return;
            var f = { name: name, label: c.label, caption: 'All ' + c.label, options: [] };
            if (c.type === 'select' && c.options) {
                f.options = Object.keys(c.options).map(function(k) {
                    return { value: k, label: c.options[k] };
                });
            }
            out.push(f);
        });

        var needed = out.filter(function(f) {
            return cols[f.name].type === 'fk_select';
        });

        if (needed.length === 0) {
            callback(out);
            return;
        }

        var pending = needed.length;
        needed.forEach(function(f) {
            var colDef = cols[f.name];
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '/db/get_fk_options?table=' + encodeURIComponent(table) + '&column=' + encodeURIComponent(f.name), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            f.options = data.options.map(function(o) {
                                return { value: o.id, label: o.name };
                            });
                        }
                    } catch (e) {}
                }
                pending--;
                if (pending === 0) callback(out);
            };
            xhr.onerror = function() {
                pending--;
                if (pending === 0) callback(out);
            };
            xhr.send();
        });
    };

    // ===== METHODS: TABLE SELECTION / GRID LOADING =====
    self.selectTable = function(tableName) {
        self.selectedTable(tableName);
        self.currentColumns(self.getGridColumnsForTable(tableName));
        self.gridMessage('');
        self.searchText('');
        self.currentPage(1);

        self.getFilterableColumnsForTable(tableName, function(filters) {
            var values = {};
            filters.forEach(function(f) {
                values[f.name] = ko.observable('');
            });
            self.filterValues = values;
            self.filterableColumns(filters);
        });

        self.loadRows(tableName);
    };

    self.loadRows = function(table) {
        self.loadingRows(true);
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/db/get_rows?table=' + encodeURIComponent(table), true);
        xhr.onload = function() {
            self.loadingRows(false);
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        self.currentRows(data.rows);
                    }
                } catch (e) {}
            }
        };
        xhr.onerror = function() { self.loadingRows(false); };
        xhr.send();
    };

    // ===== METHODS: SIDE PANEL =====
    self.openPanel = function(mode, row) {
        var table = self.selectedTable();
        var tableSchema = self.schema[table];
        var fields = self.getFormFieldsForTable(table);

        self.loadOptionsForFields(table, fields, function() {
            var values = {};
            fields.forEach(function(f) {
                var initial = f.default;
                if (mode === 'edit' && row && row[f.name] !== undefined && row[f.name] !== null) {
                    initial = row[f.name];
                }
                values[f.name] = ko.observable(initial === null ? '' : initial);
            });

            self.formValues = values;
            self.formFields(fields);
            self.formMode(mode);
            self.formKey = (mode === 'edit') ? self.getRowKey(table, row) : null;

            var idSuffix = '';
            if (mode === 'edit' && tableSchema.primary_key) {
                idSuffix = ' #' + self.formKey;
            }
            self.sidePanelTitle((mode === 'edit' ? 'Edit ' : 'Add New ') + tableSchema.label + ' Row' + idSuffix);

            self.formMessage('');
            self.formMessageType('');
            self.sidePanelOpen(true);
        });
    };

    self.openCreatePanel = function() {
        if (!self.selectedTable()) return;
        self.openPanel('create', null);
    };

    self.openEditPanel = function(row) {
        self.openPanel('edit', row);
    };

    self.closeSidePanel = function() {
        self.sidePanelOpen(false);
    };

    // ===== METHODS: SAVE =====
    self.savePanel = function() {
        var table = self.selectedTable();
        var tableSchema = self.schema[table];
        var fields = self.formFields();
        var data = {};

        for (var i = 0; i < fields.length; i++) {
            var f = fields[i];
            var val = self.formValues[f.name]();
            if (f.required && (val === '' || val === null || val === undefined)) {
                self.showFormMessage(f.label + ' is required', 'error');
                return;
            }
            data[f.name] = val;
        }

        var isEdit = (self.formMode() === 'edit');
        var url = isEdit ? '/db/update' : '/db/create';
        var params = 'table=' + encodeURIComponent(table) + '&data=' + encodeURIComponent(JSON.stringify(data));

        if (isEdit) {
            if (tableSchema.primary_key) {
                params += '&id=' + encodeURIComponent(self.formKey);
            } else {
                tableSchema.composite_key.forEach(function(k) {
                    params += '&' + k + '=' + encodeURIComponent(self.formKey[k]);
                });
            }
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    self.showFormMessage(isEdit ? 'Row updated successfully!' : 'Row created successfully!', 'success');
                    setTimeout(function() {
                        self.closeSidePanel();
                        self.loadRows(table);
                    }, 700);
                } else {
                    self.showFormMessage(res.error || 'An error occurred', 'error');
                }
            } catch (e) {
                self.showFormMessage('Server error: ' + xhr.responseText.substring(0, 150), 'error');
            }
        };
        xhr.onerror = function() {
            self.showFormMessage('Network error. Please try again.', 'error');
        };
        xhr.send(params);
    };

    // ===== METHODS: DELETE =====
    self.buildDeleteParams = function(table, keyOrRow, fromRow) {
        var tableSchema = self.schema[table];
        var params = 'table=' + encodeURIComponent(table);
        if (tableSchema.primary_key) {
            var idVal = fromRow ? keyOrRow[tableSchema.primary_key] : keyOrRow;
            params += '&id=' + encodeURIComponent(idVal);
        } else {
            tableSchema.composite_key.forEach(function(k) {
                var v = fromRow ? keyOrRow[k] : keyOrRow[k];
                params += '&' + k + '=' + encodeURIComponent(v);
            });
        }
        return params;
    };

    self.confirmDeleteFromGrid = function(row) {
        var table = self.selectedTable();
        self.pendingDeleteParams = self.buildDeleteParams(table, row, true);
        self.confirmDialogOpen(true);
    };

    self.confirmDeleteFromPanel = function() {
        var table = self.selectedTable();
        self.pendingDeleteParams = self.buildDeleteParams(table, self.formKey, false);
        self.confirmDialogOpen(true);
    };

    self.closeConfirmDialog = function() {
        self.confirmDialogOpen(false);
    };

    self.executeDelete = function() {
        var table = self.selectedTable();
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/db/delete', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            self.closeConfirmDialog();
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    self.closeSidePanel();
                    self.gridMessage('');
                    self.loadRows(table);
                } else {
                    var msg = res.error || 'Failed to delete row';
                    if (self.sidePanelOpen()) {
                        self.formMessage(msg);
                        self.formMessageType('msg-error');
                    } else {
                        self.gridMessage(msg);
                        self.gridMessageType('msg-error');
                    }
                }
            } catch (e) {}
        };
        xhr.send(self.pendingDeleteParams);
    };

    // ===== METHODS: FORM MESSAGE =====
    self.showFormMessage = function(msg, type) {
        self.formMessage(msg);
        self.formMessageType(type === 'error' ? 'msg-error' : 'msg-success');
    };

    // ===== INIT: select first table on load =====
    if (self.tableList().length > 0) {
        self.selectTable(self.tableList()[0].table);
    }
}

// ===== APPLY BINDINGS =====
ko.applyBindings(new DbViewModel());
</script>

</body>
</html>
