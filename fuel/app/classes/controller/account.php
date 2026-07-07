<?php

class Controller_Account extends Controller
{
    /**
     * Before filter — authentication guard (same pattern as Profile/Project)
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
     * GET /account
     * Renders the account page. The actual settings form is gated behind
     * a client-side password re-entry check (see action_verify_password).
     */
    public function action_index()
    {
        $employee_id = Session::get('employee_id');
        $employee    = Model_Employee::find_by_id($employee_id);
        $admin_level = Model_Position::get_admin_level($employee_id);

        $current_user = array(
            'id'          => $employee_id,
            'name'        => Session::get('employee_name'),
            'admin_level' => $admin_level,
            'avatar'      => !empty($employee['avatar']) ? '/assets/uploads/avatars/' . $employee['avatar'] : '',
        );

        $account_data = array(
            'name'  => $employee['name'],
            'email' => $employee['email'],
        );

        $view = View::forge('account/index');
        $view->set('account_json',  json_encode($account_data), false);
        $view->set('current_user',  $current_user,               true);

        return Response::forge($view);
    }

    /**
     * POST /account/verify_password
     * Verifies the current user's password to unlock the settings form.
     */
    public function action_verify_password()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Method not allowed')), 405, array('Content-Type' => 'application/json'));
        }

        $employee_id = Session::get('employee_id');
        $password    = Input::post('password', '');

        if (!$employee_id) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Not authenticated')), 401, array('Content-Type' => 'application/json'));
        }

        $employee = Model_Employee::find_by_id($employee_id);

        if (!$employee || !Model_Employee::verify_password($employee, $password)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Incorrect password')), 400, array('Content-Type' => 'application/json'));
        }

        return Response::forge(json_encode(array('success' => true)), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * POST /account/update_name
     */
    public function action_update_name()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Method not allowed')), 405, array('Content-Type' => 'application/json'));
        }

        $employee_id = Session::get('employee_id');
        $name        = trim(Input::post('name', ''));

        if (!$employee_id) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Not authenticated')), 401, array('Content-Type' => 'application/json'));
        }
        if (empty($name)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Display name cannot be empty')), 400, array('Content-Type' => 'application/json'));
        }
        if (mb_strlen($name) > 100) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Display name is too long')), 400, array('Content-Type' => 'application/json'));
        }

        Model_Employee::update_name($employee_id, $name);
        Session::set('employee_name', $name);

        return Response::forge(json_encode(array('success' => true, 'name' => $name)), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * POST /account/update_email
     */
    public function action_update_email()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Method not allowed')), 405, array('Content-Type' => 'application/json'));
        }

        $employee_id = Session::get('employee_id');
        $email       = trim(Input::post('email', ''));

        if (!$employee_id) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Not authenticated')), 401, array('Content-Type' => 'application/json'));
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Please enter a valid email address')), 400, array('Content-Type' => 'application/json'));
        }
        if (Model_Employee::email_exists($email, $employee_id)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'This email is already in use')), 400, array('Content-Type' => 'application/json'));
        }

        Model_Employee::update_email($employee_id, $email);

        return Response::forge(json_encode(array('success' => true, 'email' => $email)), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * POST /account/update_password
     */
    public function action_update_password()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Method not allowed')), 405, array('Content-Type' => 'application/json'));
        }

        $employee_id      = Session::get('employee_id');
        $new_password     = Input::post('new_password', '');
        $confirm_password = Input::post('confirm_password', '');

        if (!$employee_id) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Not authenticated')), 401, array('Content-Type' => 'application/json'));
        }
        if (strlen($new_password) < 8) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Password must be at least 8 characters long')), 400, array('Content-Type' => 'application/json'));
        }
        if ($new_password !== $confirm_password) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Passwords do not match')), 400, array('Content-Type' => 'application/json'));
        }

        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        Model_Employee::update_password($employee_id, $hashed);

        return Response::forge(json_encode(array('success' => true)), 200, array('Content-Type' => 'application/json'));
    }
}
