<?php
class Model_Position extends Model
{
    // Get all positions for filter dropdown
    public static function get_all()
    {
        return DB::select('id', 'name')
            ->from('positions')
            ->order_by('name', 'ASC')
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
}
