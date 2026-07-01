<?php
class Model_Department extends Model
{
    // Get all departments for filter dropdown
    public static function get_all()
    {
        return DB::select('id', 'name')
            ->from('departments')
            ->order_by('name', 'ASC')
            ->execute()
            ->as_array();
    }
}
