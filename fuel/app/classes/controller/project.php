<?php

class Controller_Project extends Controller
{
    /**
     * Before filter — authentication guard + admin level check
     */
    public function before()
    {
        parent::before();

        // Authentication (same as Dashboard)
        if (Session::get('employee_id')) {
            // authenticated via session
        } else {
            $remember_token = Cookie::get('remember_token');
            if ($remember_token) {
                $employee = Model_Employee::find_by_remember_token($remember_token);
                if ($employee) {
                    Session::set('employee_id', $employee['id']);
                    Session::set('employee_name', $employee['name']);
                }
            }

            // Still not authenticated — redirect to login
            if (!Session::get('employee_id')) {
                Response::redirect('/auth');
                return;
            }
        }

        // Note: Project Management page is now viewable by all employees.
        // Only project creation is restricted to admin_level >= 3 (see action_create).
    }

    /**
     * GET /project
     * Main project management page
     */
    public function action_index()
    {
        $employee_id = Session::get('employee_id');
        $admin_level = Model_Position::get_admin_level($employee_id);
        $current_employee = Model_Employee::find_by_id($employee_id);

        $current_user = array(
            'id'          => $employee_id,
            'name'        => Session::get('employee_name'),
            'admin_level' => $admin_level,
            'avatar'      => !empty($current_employee['avatar']) ? '/assets/uploads/avatars/' . $current_employee['avatar'] : '',
        );

        // Get all projects with leader name and member count
        $projects = Model_Project::get_all_with_leader();

        // Get all employees for the leader dropdown and member search
        $employees = DB::select('id', 'name')->from('employees')->order_by('name', 'ASC')->execute()->as_array();

        $view = View::forge('project/index');
        $view->set('projects_json',  json_encode($projects),   false);
        $view->set('employees_json', json_encode($employees),  false);
        $view->set('current_user',   $current_user,            true);

        return Response::forge($view);
    }

    /**
     * POST /project/create
     * Create a new project
     */
    public function action_create()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Method not allowed')), 405, array('Content-Type' => 'application/json'));
        }

        // Only admin_level >= 3 can create new projects
        $admin_level = Model_Position::get_admin_level(Session::get('employee_id'));
        if ($admin_level < 3) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'You do not have permission to create projects')), 403, array('Content-Type' => 'application/json'));
        }

        $name       = trim(Input::post('name', ''));
        $leader_id  = (int) Input::post('leader_id');
        $start_date = trim(Input::post('start_date', ''));
        $end_date   = trim((string) Input::post('end_date', ''));
        $end_date   = ($end_date === '' || strtolower($end_date) === 'null') ? null : $end_date;
        $status     = trim(Input::post('status', ''));
        $members    = Input::post('members'); // JSON array of employee IDs

        // Validate
        if (empty($name)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Project name is required')), 400, array('Content-Type' => 'application/json'));
        }
        if (!$leader_id) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Leader is required')), 400, array('Content-Type' => 'application/json'));
        }
        if (empty($start_date)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Start date is required')), 400, array('Content-Type' => 'application/json'));
        }
        if (!in_array($status, array('planning', 'ongoing', 'completed'))) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Invalid status')), 400, array('Content-Type' => 'application/json'));
        }

        $project_id = Model_Project::create($name, $leader_id, $start_date, $end_date , $status);

        // Sync members — the leader is always guaranteed to be included as a member
        $member_ids = array();
        if (!empty($members)) {
            $decoded = is_array($members) ? $members : json_decode($members, true);
            if (is_array($decoded)) {
                $member_ids = $decoded;
            }
        }
        Model_Project::sync_members($project_id, $member_ids, $leader_id);

        return Response::forge(json_encode(array('success' => true, 'project_id' => $project_id)), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * POST /project/update
     * Update an existing project (only the leader can update)
     */
    public function action_update()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Method not allowed')), 405, array('Content-Type' => 'application/json'));
        }

        $project_id = (int) Input::post('project_id');
        $name       = trim(Input::post('name', ''));
        $leader_id  = (int) Input::post('leader_id');
        $start_date = trim(Input::post('start_date', ''));
        $end_date   = trim((string) Input::post('end_date', ''));
        $end_date   = ($end_date === '' || strtolower($end_date) === 'null') ? null : $end_date;
        $status     = trim(Input::post('status', ''));
        $members    = Input::post('members'); // JSON array of employee IDs

        // Verify the current user is the leader of this project
        $project = Model_Project::find_by_id($project_id);
        if (!$project) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Project not found')), 404, array('Content-Type' => 'application/json'));
        }

        $current_user_id = Session::get('employee_id');
        if ((int) $project['leader_id'] !== (int) $current_user_id) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Only the project leader can edit this project')), 403, array('Content-Type' => 'application/json'));
        }

        // Validate
        if (empty($name)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Project name is required')), 400, array('Content-Type' => 'application/json'));
        }
        if (!$leader_id) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Leader is required')), 400, array('Content-Type' => 'application/json'));
        }
        if (empty($start_date)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Start date is required')), 400, array('Content-Type' => 'application/json'));
        }
        if (!in_array($status, array('planning', 'ongoing', 'completed'))) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Invalid status')), 400, array('Content-Type' => 'application/json'));
        }

        try {
            Model_Project::update_project($project_id, $name, $leader_id, $start_date, $end_date, $status);

            // Sync members — the (possibly new) leader is always guaranteed to be included as a member
            if ($members !== null) {
                $member_ids = is_array($members) ? $members : json_decode($members, true);
                if (is_array($member_ids)) {
                    Model_Project::sync_members($project_id, $member_ids, $leader_id);
                }
            }

            return Response::forge(json_encode(array('success' => true)), 200, array('Content-Type' => 'application/json'));
        } catch (Exception $e) {
            error_log('Project update error: ' . $e->getMessage());
            return Response::forge(json_encode(array('success' => false, 'error' => 'An unexpected error occurred. Please try again later.')), 500, array('Content-Type' => 'application/json'));
        }
    }

    /**
     * POST /project/delete
     * Delete a project (only the leader can delete)
     */
    public function action_delete()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Method not allowed')), 405, array('Content-Type' => 'application/json'));
        }

        $project_id = (int) Input::post('project_id');

        // Verify the current user is the leader of this project
        $project = Model_Project::find_by_id($project_id);
        if (!$project) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Project not found')), 404, array('Content-Type' => 'application/json'));
        }

        $current_user_id = Session::get('employee_id');
        if ((int) $project['leader_id'] !== (int) $current_user_id) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Only the project leader can delete this project')), 403, array('Content-Type' => 'application/json'));
        }

        Model_Project::delete_project($project_id);

        return Response::forge(json_encode(array('success' => true)), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * GET /project/get/:id
     * Get a single project with its members (for the edit form)
     */
    public function action_get()
    {
        $project_id = (int) Input::get('id');

        $project = Model_Project::find_by_id($project_id);
        if (!$project) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Project not found')), 404, array('Content-Type' => 'application/json'));
        }

        $members = Model_Project::get_members($project_id);

        return Response::forge(json_encode(array(
            'success' => true,
            'project' => $project,
            'members' => $members,
        )), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * GET /project/search_employee?q=...
     * AJAX endpoint: search employees by ID or name
     */
    public function action_search_employee()
    {
        $query = Input::get('q', '');
        $results = Model_Project::search_employees($query);

        return Response::forge(json_encode(array(
            'success' => true,
            'results' => $results,
        )), 200, array('Content-Type' => 'application/json'));
    }
}