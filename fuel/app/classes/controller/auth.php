<?php

class Controller_Auth extends Controller
{
    /**
     * Before filter — checks for a "Remember Me" cookie and auto-logins if valid.
     */
    public function before()
    {
        parent::before();

        // If user is already logged in via session, no need to check cookie
        if (Session::get('employee_id')) {
            return;
        }

        // Check for remember-me cookie
        $remember_token = Cookie::get('remember_token');
        if ($remember_token) {
            // Look up employee by remember_token hash
            $employee = DB::select('*')
                ->from('employees')
                ->where('remember_token', '=', $remember_token)
                ->execute()
                ->current();

            if ($employee) {
                // Re-establish session
                Session::set('employee_id', $employee['id']);
                Session::set('employee_name', $employee['name']);
            } else {
                // Token invalid or expired — delete the cookie
                Cookie::delete('remember_token');
            }
        }
    }

    // Renders the Login page view structure
    public function action_index()
    {
        // Redirect to home if already logged in
        if (Session::get('employee_id')) {
            Response::redirect('/');
        }

        return Response::forge(View::forge('auth/login'));
    }

    // Handles the processing of the form submission
    public function action_login()
    {
        if (Input::method() == 'POST')
        {
            $email = Input::post('email');
            $password = Input::post('password');
            $remember = Input::post('remember') == '1';

            // 1. Look up the employee record via email from your seeded DB setup
            $employee = DB::select('*')
                ->from('employees')
                ->where('email', '=', $email)
                ->execute()
                ->current();

            // 2. Validate row existence and handle cryptographic verification match
            if ($employee && password_verify($password, $employee['password']))
            {
                // Login Success -> Create clean application session states
                Session::set('employee_id', $employee['id']);
                Session::set('employee_name', $employee['name']);

                // 3. Handle "Remember Me" cookie if requested
                if ($remember) {
                    // Generate a secure random token
                    $token = bin2hex(random_bytes(32));

                    // Store the token in the employee record (plaintext token as identifier)
                    DB::update('employees')
                        ->set(array('remember_token' => $token))
                        ->where('id', '=', $employee['id'])
                        ->execute();

                    // Set persistent cookie (30 days)
                    Cookie::set('remember_token', $token, 30 * 24 * 60 * 60);
                } else {
                    // Clear any existing remember token from DB and cookie
                    DB::update('employees')
                        ->set(array('remember_token' => null))
                        ->where('id', '=', $employee['id'])
                        ->execute();

                    Cookie::delete('remember_token');
                }

                // Redirect straight into your home base landing index area
                Response::redirect('/welcome');
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
        // Clear remember-me token from DB and cookie if present
        $employee_id = Session::get('employee_id');
        if ($employee_id) {
            DB::update('employees')
                ->set(array('remember_token' => null))
                ->where('id', '=', $employee_id)
                ->execute();
        }

        Cookie::delete('remember_token');

        Session::delete('employee_id');
        Session::delete('employee_name');
        Response::redirect('/auth');
    }
}