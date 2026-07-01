<?php
class Model_Employee extends Model
{   //find employee by email for login
    public static function find_by_email($email)
    {
        return DB::select('*')
            ->from('employees')
            ->where('email', '=', $email)
            ->execute()
            ->current();
    }

    //verify password using PHP's password_verify function
    public static function verify_password($employee, $password)
    {
        return $employee && password_verify($password, $employee['password']);
    }

    //fin employee by remember token for auto-login
    public static function find_by_remember_token($token)
    {
        return DB::select('*')
            ->from('employees')
            ->where('remember_token', '=', $token)
            ->execute()
            ->current();
    }

    //update remember token for a given employee
    public static function update_remember_token($employee_id, $token)
    {
        DB::update('employees')
            ->set(['remember_token' => $token])
            ->where('id', '=', $employee_id)
            ->execute();
    }

    // Get a single employee's full data (basic info + department/position names)
    public static function find_by_id($employee_id)
    {
        return DB::query("SELECT e.*, d.name AS department_name, p.name AS position_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE e.id = :id")
            ->param('id', $employee_id)
            ->execute()
            ->current();
    }

    // Get all employees joined with their department and position names
    public static function get_all_with_details()
    {
        return DB::query("SELECT e.*, d.name AS department_name, p.name AS position_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id")
            ->execute()
            ->as_array();
    }

    // Update the employee's self-introduction text
    public static function update_introduction($employee_id, $introduction)
    {
        DB::update('employees')
            ->set(['introduction' => $introduction])
            ->where('id', '=', $employee_id)
            ->execute();
    }

    // Update the employee's avatar filename
    public static function update_avatar($employee_id, $filename)
    {
        DB::update('employees')
            ->set(['avatar' => $filename])
            ->where('id', '=', $employee_id)
            ->execute();
    }
}
