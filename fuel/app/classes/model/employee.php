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
            "SELECT c.id AS comment_id, c.author_id, c.content, c.create_time, e.name AS author_name
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

    // Insert a new comment and return the saved comment data (with author name)
    public static function post_comment($author_id, $receiver_id, $content)
    {
        // Insert the comment
        list($insert_id) = DB::insert('comments')
            ->columns(['author_id', 'receiver_id', 'content'])
            ->values([$author_id, $receiver_id, $content])
            ->execute();

        // Fetch the saved row with author name, timestamp, and IDs
        $row = DB::query(
            "SELECT c.id AS comment_id, c.author_id, c.content, c.create_time, e.name AS author_name
             FROM comments c
             JOIN employees e ON c.author_id = e.id
             WHERE c.id = :id"
        )->param('id', $insert_id)->execute()->current();

        return $row;
    }

    // Update a comment's content (only if author matches)
    public static function update_comment($comment_id, $author_id, $new_content)
    {
        $affected = DB::update('comments')
            ->set(['content' => $new_content])
            ->where('id', '=', $comment_id)
            ->where('author_id', '=', $author_id)
            ->execute();
        return $affected > 0;
    }

    // Delete a comment (only if author matches)
    public static function delete_comment($comment_id, $author_id)
    {
        $affected = DB::delete('comments')
            ->where('id', '=', $comment_id)
            ->where('author_id', '=', $author_id)
            ->execute();
        return $affected > 0;
    }

    // === Profile Page related methods ===

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

    // Add a new skill entry for an employee
    public static function add_skill($employee_id, $skill_id, $level)
    {
        list($insert_id) = DB::insert('skills')
            ->columns(['employee_id', 'skill_id', 'level'])
            ->values([$employee_id, $skill_id, $level])
            ->execute();
        return $insert_id;
    }

    // Remove a skill entry (only if it belongs to the employee)
    public static function remove_skill($skill_row_id, $employee_id)
    {
        $affected = DB::delete('skills')
            ->where('id', '=', $skill_row_id)
            ->where('employee_id', '=', $employee_id)
            ->execute();
        return $affected > 0;
    }

    // Add a new certificate entry for an employee
    public static function add_certificate($employee_id, $certificate_id, $level, $scale)
    {
        list($insert_id) = DB::insert('certificates')
            ->columns(['employee_id', 'certificate_id', 'level', 'scale'])
            ->values([$employee_id, $certificate_id, $level, $scale])
            ->execute();
        return $insert_id;
    }

    // Remove a certificate entry (only if it belongs to the employee)
    public static function remove_certificate($cert_row_id, $employee_id)
    {
        $affected = DB::delete('certificates')
            ->where('id', '=', $cert_row_id)
            ->where('employee_id', '=', $employee_id)
            ->execute();
        return $affected > 0;
    }

    // Get full profile data WITH row ids for skills/certs (needed for remove buttons)
    public static function get_own_profile_data($employee_id)
    {
        // Skills: include row id so it can be individually removed
        $skills = DB::query(
            "SELECT id AS row_id, skill_id, level FROM skills WHERE employee_id = :id"
        )->param('id', $employee_id)->execute()->as_array();

        // Certificates: include row id so it can be individually removed
        $certificates = DB::query(
            "SELECT id AS row_id, certificate_id, level, scale FROM certificates WHERE employee_id = :id"
        )->param('id', $employee_id)->execute()->as_array();

        // Projects: view-only, via junction table
        $projects = DB::query(
            "SELECT p.name, p.status, p.start_date, p.end_date, p.leader_name
             FROM projects p
             JOIN employeesProjects ep ON p.id = ep.project_id
             WHERE ep.employee_id = :id
             ORDER BY p.start_date DESC"
        )->param('id', $employee_id)->execute()->as_array();

        // Comments received from colleagues
        $comments = DB::query(
            "SELECT c.id AS comment_id, c.author_id, c.content, c.create_time, e.name AS author_name
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
