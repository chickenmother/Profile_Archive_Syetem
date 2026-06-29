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

    // Get full profile data for a single employee (skills, certs, projects, comments)
    public static function get_profile_data($employee_id)
    {
        // Skills: skill_id (for name lookup from config) + level
        $skills = DB::query(
            "SELECT skill_id, level FROM skills WHERE employee_id = :id"
        )->param('id', $employee_id)->execute()->as_array();

        // Certificates: certificate_id (for name lookup from config) + level + scale
        $certificates = DB::query(
            "SELECT certificate_id, level, scale FROM certificates WHERE employee_id = :id"
        )->param('id', $employee_id)->execute()->as_array();

        // Projects: via junction table
        $projects = DB::query(
            "SELECT p.name, p.status, p.start_date, p.end_date
             FROM projects p
             JOIN employeesProjects ep ON p.id = ep.project_id
             WHERE ep.employee_id = :id
             ORDER BY p.start_date DESC"
        )->param('id', $employee_id)->execute()->as_array();

        // Comments received by this employee
        $comments = DB::query(
            "SELECT c.content, c.create_time, e.name AS author_name
             FROM comments c
             JOIN employees e ON c.author_id = e.id
             WHERE c.receiver_id = :id
             ORDER BY c.create_time DESC"
        )->param('id', $employee_id)->execute()->as_array();

        return array(
            'skills'       => $skills,
            'certificates' => $certificates,
            'projects'     => $projects,
            'comments'     => $comments,
        );
    }
}
