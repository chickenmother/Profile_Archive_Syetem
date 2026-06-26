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

    // === Dashboard related methods ===

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

    // Get all departments for filter dropdown
    public static function get_departments()
    {
        return DB::select('id', 'name')
            ->from('departments')
            ->order_by('name','ASC')
            ->execute()
            ->as_array();
    }

    // Get all positions for filter dropdown
    public static function get_positions()
    {
        return DB::select('id', 'name')
            ->from('positions')
            ->order_by('name','ASC')
            ->execute()
            ->as_array();
    }

    // Get admin level for a given employee (for role-based menu visibility)
    public static function get_admin_level($employee_id)
    {
        $result = DB::select('positions.admin_level')
            ->from('employees')
            ->join('positions', 'LEFT')
            ->on('employees.position_id', '=', 'positions.id')
            ->where('employees.id', '=', $employee_id)
            ->execute()
            ->current();
        return $result ? (int)$result['admin_level'] : 0;
    }

    // Get employee with project count, skill count, certificate count
    public static function get_all_with_counts()
    {
        $employees = self::get_all_with_details();
        // Add default count values (0) to each employee
        foreach ($employees as &$emp) {
            $emp['project_count'] = 0;
            $emp['skill_count'] = 0;
            $emp['certificate_count'] = 0;
        }
        return $employees;
    }
}
