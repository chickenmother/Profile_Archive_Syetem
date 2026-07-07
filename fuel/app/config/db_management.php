<?php
/**
 * DB Management Page — Table Schema Configuration
 *
 * Defines which tables are manageable, their columns, input types,
 * foreign-key relationships, and special handling rules.
 *
 * Column "type" values:
 *   text, number, date, textarea, select, fk_select
 *
 * Column flags:
 *   readonly   — shown in grid, but not editable in the form (e.g. id, create_time)
 *   hidden     — never shown in the form at all (e.g. password, remember_token)
 *   required   — must have a value before saving
 *
 * FK columns (type = fk_select) reference another table's id + display column,
 * so the grid/form can resolve human-readable names instead of raw IDs.
 */

return array(

    'departments' => array(
        'label'      => 'Departments',
        'icon'       => 'fa-building',
        'primary_key'=> 'id',
        'columns'    => array(
            'id'                 => array('label' => 'ID',        'type' => 'number', 'readonly' => true),
            'name'               => array('label' => 'Name',      'type' => 'text',    'required' => true),
            'number_of_employee' => array('label' => 'Employees', 'type' => 'number', 'required' => true, 'default' => 0),
        ),
    ),

    'positions' => array(
        'label'      => 'Positions',
        'icon'       => 'fa-id-badge',
        'primary_key'=> 'id',
        'columns'    => array(
            'id'          => array('label' => 'ID',          'type' => 'number', 'readonly' => true),
            'name'        => array('label' => 'Name',        'type' => 'text',   'required' => true),
            'admin_level' => array('label' => 'Admin Level', 'type' => 'number', 'required' => true, 'default' => 1),
        ),
    ),

    'employees' => array(
        'label'      => 'Employees',
        'icon'       => 'fa-users',
        'primary_key'=> 'id',
        'columns'    => array(
            'id'            => array('label' => 'ID',           'type' => 'number', 'readonly' => true),
            'name'          => array('label' => 'Name',         'type' => 'text',     'required' => true),
            'email'         => array('label' => 'Email',        'type' => 'text',     'required' => true),
            'password'      => array('label' => 'Password',     'type' => 'text',     'hidden' => true),
            'hire_date'     => array('label' => 'Hire Date',    'type' => 'date',     'required' => true),
            'department_id' => array('label' => 'Department',   'type' => 'fk_select', 'fk_table' => 'departments', 'fk_display' => 'name'),
            'position_id'   => array('label' => 'Position',     'type' => 'fk_select', 'fk_table' => 'positions',   'fk_display' => 'name', 'filterable' => true),
            'introduction'  => array('label' => 'Introduction', 'type' => 'textarea'),
            'avatar'        => array('label' => 'Avatar Filename', 'type' => 'text'),
            'remember_token'=> array('label' => 'Remember Token', 'type' => 'text', 'hidden' => true),
        ),
        // New employees created via DB Management get this default password
        'default_password' => 'password123',
    ),

    'projects' => array(
        'label'      => 'Projects',
        'icon'       => 'fa-project-diagram',
        'primary_key'=> 'id',
        'columns'    => array(
            'id'         => array('label' => 'ID',         'type' => 'number', 'readonly' => true),
            'name'       => array('label' => 'Name',       'type' => 'text',     'required' => true),
            'leader_id'  => array('label' => 'Leader',     'type' => 'fk_select', 'fk_table' => 'employees', 'fk_display' => 'name', 'required' => true),
            'start_date' => array('label' => 'Start Date', 'type' => 'date',     'required' => true),
            'end_date'   => array('label' => 'End Date',   'type' => 'date'),
            'status'     => array('label' => 'Status', 'type' => 'select', 'required' => true, 'filterable' => true,
                'options' => array('planning' => 'Planning', 'ongoing' => 'Ongoing', 'completed' => 'Completed')),
        ),
    ),

    'skills' => array(
        'label'      => 'Skills',
        'icon'       => 'fa-code',
        'primary_key'=> 'id',
        'columns'    => array(
            'id'          => array('label' => 'ID',       'type' => 'number', 'readonly' => true),
            'employee_id' => array('label' => 'Employee', 'type' => 'fk_select', 'fk_table' => 'employees', 'fk_display' => 'name', 'required' => true),
            'skill_id'    => array('label' => 'Skill',    'type' => 'config_select', 'config' => 'skills', 'required' => true),
            'level'       => array('label' => 'Level', 'type' => 'select', 'required' => true, 'filterable' => true,
                'options' => array('beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'expert' => 'Expert')),
        ),
    ),

    'certificates' => array(
        'label'      => 'Certificates',
        'icon'       => 'fa-certificate',
        'primary_key'=> 'id',
        'columns'    => array(
            'id'             => array('label' => 'ID',       'type' => 'number', 'readonly' => true),
            'employee_id'    => array('label' => 'Employee', 'type' => 'fk_select', 'fk_table' => 'employees', 'fk_display' => 'name', 'required' => true),
            'certificate_id' => array('label' => 'Certificate', 'type' => 'config_select', 'config' => 'certificates', 'required' => true),
            'level'          => array('label' => 'Level', 'type' => 'text', 'required' => true),
            'scale'          => array('label' => 'Scale', 'type' => 'select', 'required' => true, 'filterable' => true,
                'options' => array('local' => 'Local', 'national' => 'National', 'international' => 'International')),
        ),
    ),

    'comments' => array(
        'label'      => 'Comments',
        'icon'       => 'fa-comments',
        'primary_key'=> 'id',
        'columns'    => array(
            'id'          => array('label' => 'ID',          'type' => 'number', 'readonly' => true),
            'author_id'   => array('label' => 'Author',      'type' => 'fk_select', 'fk_table' => 'employees', 'fk_display' => 'name', 'required' => true),
            'receiver_id' => array('label' => 'Receiver',    'type' => 'fk_select', 'fk_table' => 'employees', 'fk_display' => 'name', 'required' => true),
            'content'     => array('label' => 'Content',     'type' => 'textarea', 'required' => true),
            'create_time' => array('label' => 'Created At',  'type' => 'text', 'readonly' => true),
        ),
    ),

    'employeesProjects' => array(
        'label'       => 'Employee ↔ Project Links',
        'icon'        => 'fa-link',
        'primary_key' => null, // composite key table — no single id column
        'composite_key' => array('employee_id', 'project_id'),
        'columns'     => array(
            'employee_id' => array('label' => 'Employee', 'type' => 'fk_select', 'fk_table' => 'employees', 'fk_display' => 'name', 'required' => true),
            'project_id'  => array('label' => 'Project',  'type' => 'fk_select', 'fk_table' => 'projects',  'fk_display' => 'name', 'required' => true),
        ),
    ),

);
