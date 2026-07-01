<?php
class Model_Project extends Model
{
    // Get all projects an employee is assigned to (via the junction table)
    // Includes leader_name — used by both the dashboard modal and the profile page
    public static function get_by_employee($employee_id)
    {
        return DB::query(
            "SELECT p.name, p.status, p.start_date, p.end_date, p.leader_name
             FROM projects p
             JOIN employeesProjects ep ON p.id = ep.project_id
             WHERE ep.employee_id = :id
             ORDER BY p.start_date DESC"
        )->param('id', $employee_id)->execute()->as_array();
    }
}
