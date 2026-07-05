<?php
class Model_Project extends Model
{
    /**
     * Get all projects an employee is assigned to (via junction table)
     * Used by dashboard modal and profile page
     */
    public static function get_by_employee($employee_id)
    {
        return DB::query(
            "SELECT p.id, p.name, p.status, p.start_date, p.end_date,
                    e.name AS leader_name
             FROM projects p
             JOIN employees e ON p.leader_id = e.id
             JOIN employeesProjects ep ON p.id = ep.project_id
             WHERE ep.employee_id = :id
             ORDER BY p.start_date DESC"
        )->param('id', $employee_id)->execute()->as_array();
    }

    /**
     * Get all projects with leader name and member count
     * Used by the Project Management page
     */
    public static function get_all_with_leader()
    {
        return DB::query(
            "SELECT p.id, p.name, p.leader_id, p.start_date, p.end_date, p.status,
                    e.name AS leader_name,
                    (SELECT COUNT(*) FROM employeesProjects ep WHERE ep.project_id = p.id) AS member_count,
                    (SELECT GROUP_CONCAT(ep2.employee_id) FROM employeesProjects ep2 WHERE ep2.project_id = p.id) AS member_ids
             FROM projects p
             JOIN employees e ON p.leader_id = e.id
             ORDER BY p.id ASC"
        )->execute()->as_array();
    }

    /**
     * Get a single project by ID with leader name
     */
    public static function find_by_id($project_id)
    {
        return DB::query(
            "SELECT p.*, e.name AS leader_name
             FROM projects p
             JOIN employees e ON p.leader_id = e.id
             WHERE p.id = :id"
        )->param('id', $project_id)->execute()->current();
    }

    /**
     * Get all members of a project (via junction table)
     */
    public static function get_members($project_id)
    {
        return DB::query(
            "SELECT e.id, e.name
             FROM employees e
             JOIN employeesProjects ep ON e.id = ep.employee_id
             WHERE ep.project_id = :pid
             ORDER BY e.name ASC"
        )->param('pid', $project_id)->execute()->as_array();
    }

    /**
     * Create a new project, returns the new project ID
     */
    public static function create($name, $leader_id, $start_date, $end_date, $status)
    {
        $result = DB::query("INSERT INTO `projects` (`name`, `leader_id`, `start_date`, `end_date`, `status`) VALUES (:name, :leader_id, :start_date, :end_date, :status)", \DB::INSERT)
            ->param('name', $name)
            ->param('leader_id', (int) $leader_id)
            ->param('start_date', $start_date)
            ->param('end_date', $end_date)
            ->param('status', $status)
            ->execute();

        return $result[0]; // Returns the insert ID
    }

    /**
     * Update an existing project
     */
    public static function update_project($project_id, $name, $leader_id, $start_date, $end_date, $status)
    {
        return DB::update('projects')
            ->set(array(
                'name'       => $name,
                'leader_id'  => $leader_id,
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'status'     => $status,
            ))
            ->where('id', '=', $project_id)
            ->execute();
    }

    /**
     * Delete a project (cascade removes junction rows)
     */
    public static function delete_project($project_id)
    {
        return DB::delete('projects')
            ->where('id', '=', $project_id)
            ->execute();
    }

    /**
     * Sync members: remove all current members, then add the new list
     */
    public static function sync_members($project_id, $employee_ids)
    {
        // Remove all current members
        DB::delete('employeesProjects')
            ->where('project_id', '=', $project_id)
            ->execute();

        // Add new members using direct SQL to avoid DB::insert issues
        foreach ($employee_ids as $emp_id) {
            DB::query("INSERT INTO `employeesProjects` (`employee_id`, `project_id`) VALUES (:emp_id, :proj_id)")
                ->param('emp_id', (int) $emp_id)
                ->param('proj_id', (int) $project_id)
                ->execute();
        }
    }

    /**
     * Search employees by ID or name (partial match) for the member search field
     */
    public static function search_employees($query)
    {
        $query = trim($query);
        if (empty($query)) {
            return array();
        }

        // If query is numeric, search by ID (exact match)
        if (ctype_digit($query)) {
            return DB::select('id', 'name')
                ->from('employees')
                ->where('id', '=', (int) $query)
                ->execute()
                ->as_array();
        }

        // Otherwise search by name (partial match)
        return DB::select('id', 'name')
            ->from('employees')
            ->where('name', 'LIKE', '%' . $query . '%')
            ->order_by('name', 'ASC')
            ->limit(10)
            ->execute()
            ->as_array();
    }
}