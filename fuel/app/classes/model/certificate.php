<?php
class Model_Certificate extends Model
{
    // Get certificates for an employee (certificate_id + level + scale — used for dashboard display)
    public static function get_by_employee($employee_id)
    {
        return DB::query(
            "SELECT certificate_id, level, scale FROM certificates WHERE employee_id = :id"
        )->param('id', $employee_id)->execute()->as_array();
    }

    // Get certificates for an employee INCLUDING the row id (used on the profile page for remove buttons)
    public static function get_by_employee_with_row_id($employee_id)
    {
        return DB::query(
            "SELECT id AS row_id, certificate_id, level, scale FROM certificates WHERE employee_id = :id"
        )->param('id', $employee_id)->execute()->as_array();
    }

    // Add a new certificate entry for an employee
    public static function add($employee_id, $certificate_id, $level, $scale)
    {
        list($insert_id) = DB::insert('certificates')
            ->columns(['employee_id', 'certificate_id', 'level', 'scale'])
            ->values([$employee_id, $certificate_id, $level, $scale])
            ->execute();
        return $insert_id;
    }

    // Remove a certificate entry (only if it belongs to the employee)
    public static function remove($cert_row_id, $employee_id)
    {
        $affected = DB::delete('certificates')
            ->where('id', '=', $cert_row_id)
            ->where('employee_id', '=', $employee_id)
            ->execute();
        return $affected > 0;
    }
}
