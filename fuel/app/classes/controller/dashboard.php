<?php

class Controller_Dashboard extends Controller
{
    /**
     * Before filter — authentication guard
     */
    public function before()
    {
        parent::before();

        // If user is already logged in via session, proceed
        if (Session::get('employee_id')) {
            return;
        }

        // Check for remember-me cookie
        $remember_token = Cookie::get('remember_token');
        if ($remember_token) {
            $employee = Model_Employee::find_by_remember_token($remember_token);
            if ($employee) {
                Session::set('employee_id', $employee['id']);
                Session::set('employee_name', $employee['name']);
                return; // Authenticated via cookie — allow access
            }
        }

        // Not authenticated — redirect to login
        Response::redirect('/auth');
    }

    /**
     * Main dashboard page
     * Loads employee data and filter options, passes them to the view as JSON
     */
    public function action_index()
    {
        // Get all employees with department and position names
        $employees = Model_Employee::get_all_with_details();

        // Enrich each employee with full profile data (skills, certs, projects, comments)
        for ($i = 0; $i < count($employees); $i++) {
            $profile = Model_Employee::get_profile_data($employees[$i]['id']);
            $employees[$i]['skills']       = $profile['skills'];
            $employees[$i]['certificates'] = $profile['certificates'];
            $employees[$i]['projects']     = $profile['projects'];
            $employees[$i]['comments']     = $profile['comments'];
            // Real counts from actual data
            $employees[$i]['project_count']     = count($profile['projects']);
            $employees[$i]['skill_count']        = count($profile['skills']);
            $employees[$i]['certificate_count']  = count($profile['certificates']);
        }

        // Get filter dropdown options
        $departments = Model_Employee::get_departments();
        $positions = Model_Employee::get_positions();

        // Load skill & certificate config files directly
        try {
            $skills_config = require APPPATH . 'config/skills.php';
        } catch (Exception $e) {
            $skills_config = array();
        }
        try {
            $certificates_config = require APPPATH . 'config/certificates.php';
        } catch (Exception $e) {
            $certificates_config = array();
        }

        // Convert flat config arrays into dropdown-friendly format (like departments/positions)
        $skills_list = array();
        $certificates_list = array();
        foreach ($skills_config as $id => $name) {
            $skills_list[] = array('id' => (string)$id, 'name' => $name);
        }
        foreach ($certificates_config as $id => $name) {
            $certificates_list[] = array('id' => (string)$id, 'name' => $name);
        }

        // Get logged-in user info for the top banner, including admin level
        $employee_id = Session::get('employee_id');
        $admin_level = Model_Employee::get_admin_level($employee_id);
        $current_user = array(
            'id'          => $employee_id,
            'name'        => Session::get('employee_name'),
            'admin_level' => $admin_level,
        );

        // Pass everything to the view
        // NOTE: false as 3rd arg disables FuelPHP's auto HTML-escaping for JSON strings
        $view = View::forge('dashboard/index');
        $view->set('employees_json',     json_encode($employees),          false);
        $view->set('departments',        $departments,                     true);
        $view->set('positions',          $positions,                       true);
        $view->set('skills',             $skills_list,                     true);
        $view->set('certificates',       $certificates_list,               true);
        $view->set('skills_json',        json_encode($skills_config),      false);
        $view->set('certificates_json',  json_encode($certificates_config), false);
        $view->set('current_user',       $current_user,                    true);

        return Response::forge($view);
    }

    /**
     * POST /dashboard/comment
     * Accepts: receiver_id, content (JSON body or form POST)
     * Returns: JSON { success, comment } or { success, error }
     */
    public function action_comment()
    {
        // Only allow POST
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(['success' => false, 'error' => 'Method not allowed']), 405, ['Content-Type' => 'application/json']);
        }

        $author_id   = Session::get('employee_id');
        $receiver_id = (int) Input::post('receiver_id');
        $content     = trim(Input::post('content', ''));

        // Validate
        if (!$author_id) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Not authenticated']), 401, ['Content-Type' => 'application/json']);
        }
        if ($author_id === $receiver_id) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Cannot comment on your own profile']), 400, ['Content-Type' => 'application/json']);
        }
        if (empty($content)) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Comment cannot be empty']), 400, ['Content-Type' => 'application/json']);
        }
        if (mb_strlen($content) > 500) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Comment must be 500 characters or fewer']), 400, ['Content-Type' => 'application/json']);
        }

        $comment = Model_Employee::post_comment($author_id, $receiver_id, $content);

        return Response::forge(json_encode(['success' => true, 'comment' => $comment]), 200, ['Content-Type' => 'application/json']);
    }

    /**
     * POST /dashboard/edit_comment
     * Accepts: comment_id, content
     * Returns: JSON { success } or { success, error }
     */
    public function action_edit_comment()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(['success' => false, 'error' => 'Method not allowed']), 405, ['Content-Type' => 'application/json']);
        }

        $author_id  = Session::get('employee_id');
        $comment_id = (int) Input::post('comment_id');
        $content    = trim(Input::post('content', ''));

        if (!$author_id) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Not authenticated']), 401, ['Content-Type' => 'application/json']);
        }
        if (empty($content)) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Comment cannot be empty']), 400, ['Content-Type' => 'application/json']);
        }
        if (mb_strlen($content) > 500) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Comment must be 500 characters or fewer']), 400, ['Content-Type' => 'application/json']);
        }

        $ok = Model_Employee::update_comment($comment_id, $author_id, $content);

        if (!$ok) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Could not update comment. You may not be the author.']), 403, ['Content-Type' => 'application/json']);
        }

        return Response::forge(json_encode(['success' => true, 'content' => $content]), 200, ['Content-Type' => 'application/json']);
    }

    /**
     * POST /dashboard/delete_comment
     * Accepts: comment_id
     * Returns: JSON { success } or { success, error }
     */
    public function action_delete_comment()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(['success' => false, 'error' => 'Method not allowed']), 405, ['Content-Type' => 'application/json']);
        }

        $author_id  = Session::get('employee_id');
        $comment_id = (int) Input::post('comment_id');

        if (!$author_id) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Not authenticated']), 401, ['Content-Type' => 'application/json']);
        }

        $ok = Model_Employee::delete_comment($comment_id, $author_id);

        if (!$ok) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Could not delete comment. You may not be the author.']), 403, ['Content-Type' => 'application/json']);
        }

        return Response::forge(json_encode(['success' => true]), 200, ['Content-Type' => 'application/json']);
    }
}
