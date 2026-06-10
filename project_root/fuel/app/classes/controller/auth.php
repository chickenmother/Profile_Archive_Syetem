<?php

class Controller_Auth extends Controller
{
    // Renders the Login page view structure
    public function action_index()
    {
        return Response::forge(View::forge('auth/login'));
    }

    // Handles the processing of the form submission
    public function action_login()
    {
        if (Input::method() == 'POST')
        {
            $email = Input::post('email');
            $password = Input::post('password');

            // 1. Look up the employee record via email from your seeded DB setup
            $employee = DB::select('*')
                ->from('employees')
                ->where('email', '=', $email)
                ->execute()
                ->current();

            // 2. Validate row existence and handle cryptographic verification match
            // Note: Our seed data holds custom bcrypt strings (e.g. 'password123')
            if ($employee && password_verify($password, $employee['password']))
            {
                // Login Success -> Create clean application session states
                Session::set('employee_id', $employee['id']);
                Session::set('employee_name', $employee['name']);

                // Redirect straight into your home base landing index area
                Response::redirect('/');
            }
            else
            {
                // Login Failed -> Set trigger state flash configurations to activate red warning box styles (Page 2)
                Session::set_flash('error', 'Invalid email or password parameters.');
                Response::redirect('/auth');
            }
        }

        Response::redirect('/auth');
    }

    // Logouts out clear application runtime parameters
    public function action_logout()
    {
        Session::delete('employee_id');
        Session::delete('employee_name');
        Response::redirect('/auth');
    }
}