<?php
class Model_Skill extends Model
{
    // Get skills for an employee (skill_id + level only — used for dashboard display)
    public static function get_by_employee($employee_id)
    {
        return DB::query(
            "SELECT skill_id, level FROM skills WHERE employee_id = :id"
        )->param('id', $employee_id)->execute()->as_array();
    }

    // Get skills for an employee INCLUDING the row id (used on the profile page for remove buttons)
    public static function get_by_employee_with_row_id($employee_id)
    {
        return DB::query(
            "SELECT id AS row_id, skill_id, level FROM skills WHERE employee_id = :id"
        )->param('id', $employee_id)->execute()->as_array();
    }

    // Add a new skill entry for an employee
    public static function add($employee_id, $skill_id, $level)
    {
        list($insert_id) = DB::insert('skills')
            ->columns(['employee_id', 'skill_id', 'level'])
            ->values([$employee_id, $skill_id, $level])
            ->execute();
        return $insert_id;
    }

    // Remove a skill entry (only if it belongs to the employee)
    public static function remove($skill_row_id, $employee_id)
    {
        $affected = DB::delete('skills')
            ->where('id', '=', $skill_row_id)
            ->where('employee_id', '=', $employee_id)
            ->execute();
        return $affected > 0;
    }
}
