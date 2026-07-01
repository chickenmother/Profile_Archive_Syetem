<?php

class Controller_Profile extends Controller
{
    /**
     * Before filter — authentication guard (same as Dashboard)
     */
    public function before()
    {
        parent::before();

        if (Session::get('employee_id')) {
            return;
        }

        $remember_token = Cookie::get('remember_token');
        if ($remember_token) {
            $employee = Model_Employee::find_by_remember_token($remember_token);
            if ($employee) {
                Session::set('employee_id', $employee['id']);
                Session::set('employee_name', $employee['name']);
                return;
            }
        }

        Response::redirect('/auth');
    }

    /**
     * GET /profile
     * Shows the logged-in user's own editable profile page
     */
    public function action_index()
    {
        $employee_id = Session::get('employee_id');

        $employee = Model_Employee::find_by_id($employee_id);
        $profile_data = Model_Employee::get_own_profile_data($employee_id);

        $employee['skills']       = $profile_data['skills'];
        $employee['certificates'] = $profile_data['certificates'];
        $employee['projects']     = $profile_data['projects'];
        $employee['comments']     = $profile_data['comments'];

        // Load skill & certificate config files (id => name maps)
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

        $admin_level = Model_Employee::get_admin_level($employee_id);
        $current_user = array(
            'id'          => $employee_id,
            'name'        => Session::get('employee_name'),
            'admin_level' => $admin_level,
        );

        $view = View::forge('profile/index');
        $view->set('employee_json',      json_encode($employee),           false);
        $view->set('skills_json',        json_encode($skills_config),      false);
        $view->set('certificates_json',  json_encode($certificates_config), false);
        $view->set('current_user',       $current_user,                    true);

        return Response::forge($view);
    }

    /**
     * POST /profile/update_introduction
     */
    public function action_update_introduction()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(['success' => false, 'error' => 'Method not allowed']), 405, ['Content-Type' => 'application/json']);
        }

        $employee_id  = Session::get('employee_id');
        $introduction = trim(Input::post('introduction', ''));

        if (!$employee_id) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Not authenticated']), 401, ['Content-Type' => 'application/json']);
        }
        if (mb_strlen($introduction) > 1000) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Introduction must be 1000 characters or fewer']), 400, ['Content-Type' => 'application/json']);
        }

        Model_Employee::update_introduction($employee_id, $introduction);

        return Response::forge(json_encode(['success' => true, 'introduction' => $introduction]), 200, ['Content-Type' => 'application/json']);
    }

    /**
     * POST /profile/upload_avatar
     * Accepts a multipart/form-data file upload under field name "avatar"
     */
    public function action_upload_avatar()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(['success' => false, 'error' => 'Method not allowed']), 405, ['Content-Type' => 'application/json']);
        }

        $employee_id = Session::get('employee_id');
        if (!$employee_id) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Not authenticated']), 401, ['Content-Type' => 'application/json']);
        }

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            return Response::forge(json_encode(['success' => false, 'error' => 'No file uploaded or upload error occurred']), 400, ['Content-Type' => 'application/json']);
        }

        $file = $_FILES['avatar'];

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Only JPG, PNG, GIF, or WEBP images are allowed']), 400, ['Content-Type' => 'application/json']);
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Image must be smaller than 5MB']), 400, ['Content-Type' => 'application/json']);
        }

        // Build a unique, safe filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
        $filename = 'avatar_' . $employee_id . '_' . time() . '.' . $ext;

        $upload_dir = DOCROOT . 'assets/uploads/avatars/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $destination = $upload_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Failed to save uploaded file']), 500, ['Content-Type' => 'application/json']);
        }

        // Delete old avatar file if one exists
        $current = Model_Employee::find_by_id($employee_id);
        if (!empty($current['avatar'])) {
            $old_path = $upload_dir . $current['avatar'];
            if (is_file($old_path)) {
                @unlink($old_path);
            }
        }

        Model_Employee::update_avatar($employee_id, $filename);

        return Response::forge(json_encode([
            'success'    => true,
            'avatar_url' => '/assets/uploads/avatars/' . $filename,
        ]), 200, ['Content-Type' => 'application/json']);
    }

    /**
     * POST /profile/add_skill
     */
    public function action_add_skill()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(['success' => false, 'error' => 'Method not allowed']), 405, ['Content-Type' => 'application/json']);
        }

        $employee_id = Session::get('employee_id');
        $skill_id    = (int) Input::post('skill_id');
        $level       = Input::post('level');

        if (!$employee_id) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Not authenticated']), 401, ['Content-Type' => 'application/json']);
        }
        if (!$skill_id || !in_array($level, ['beginner', 'intermediate', 'expert'])) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Invalid skill or level']), 400, ['Content-Type' => 'application/json']);
        }

        $row_id = Model_Employee::add_skill($employee_id, $skill_id, $level);

        return Response::forge(json_encode([
            'success' => true,
            'skill'   => ['row_id' => $row_id, 'skill_id' => (string)$skill_id, 'level' => $level],
        ]), 200, ['Content-Type' => 'application/json']);
    }

    /**
     * POST /profile/remove_skill
     */
    public function action_remove_skill()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(['success' => false, 'error' => 'Method not allowed']), 405, ['Content-Type' => 'application/json']);
        }

        $employee_id = Session::get('employee_id');
        $row_id      = (int) Input::post('row_id');

        if (!$employee_id) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Not authenticated']), 401, ['Content-Type' => 'application/json']);
        }

        $ok = Model_Employee::remove_skill($row_id, $employee_id);

        if (!$ok) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Could not remove skill']), 403, ['Content-Type' => 'application/json']);
        }

        return Response::forge(json_encode(['success' => true]), 200, ['Content-Type' => 'application/json']);
    }

    /**
     * POST /profile/add_certificate
     */
    public function action_add_certificate()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(['success' => false, 'error' => 'Method not allowed']), 405, ['Content-Type' => 'application/json']);
        }

        $employee_id    = Session::get('employee_id');
        $certificate_id = (int) Input::post('certificate_id');
        $level          = trim(Input::post('level', ''));
        $scale          = Input::post('scale');

        if (!$employee_id) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Not authenticated']), 401, ['Content-Type' => 'application/json']);
        }
        if (!$certificate_id || empty($level) || !in_array($scale, ['local', 'national', 'international'])) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Invalid certificate, level, or scale']), 400, ['Content-Type' => 'application/json']);
        }

        $row_id = Model_Employee::add_certificate($employee_id, $certificate_id, $level, $scale);

        return Response::forge(json_encode([
            'success'     => true,
            'certificate' => ['row_id' => $row_id, 'certificate_id' => (string)$certificate_id, 'level' => $level, 'scale' => $scale],
        ]), 200, ['Content-Type' => 'application/json']);
    }

    /**
     * POST /profile/remove_certificate
     */
    public function action_remove_certificate()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(['success' => false, 'error' => 'Method not allowed']), 405, ['Content-Type' => 'application/json']);
        }

        $employee_id = Session::get('employee_id');
        $row_id      = (int) Input::post('row_id');

        if (!$employee_id) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Not authenticated']), 401, ['Content-Type' => 'application/json']);
        }

        $ok = Model_Employee::remove_certificate($row_id, $employee_id);

        if (!$ok) {
            return Response::forge(json_encode(['success' => false, 'error' => 'Could not remove certificate']), 403, ['Content-Type' => 'application/json']);
        }

        return Response::forge(json_encode(['success' => true]), 200, ['Content-Type' => 'application/json']);
    }
}
