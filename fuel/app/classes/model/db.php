<?php
/**
 * Model_Db — Generic table CRUD helper for the DB Management page.
 *
 * Works off the schema defined in config/db_management.php so that
 * a single controller can manage all tables without table-specific code.
 */
class Model_Db
{
    /**
     * Load the full schema config
     */
    public static function get_schema()
    {
        static $schema = null;
        if ($schema === null) {
            $schema = require APPPATH . 'config/db_management.php';
        }
        return $schema;
    }

    /**
     * Get the schema for a single table, or null if not manageable
     */
    public static function get_table_schema($table)
    {
        $schema = self::get_schema();
        return isset($schema[$table]) ? $schema[$table] : null;
    }

    /**
     * Whitelist check — only allow known, configured table names
     */
    public static function is_valid_table($table)
    {
        return self::get_table_schema($table) !== null;
    }

    /**
     * Get all rows for a table, with FK / config_select columns resolved
     * to human-readable display values (added as "<column>_display").
     */
    public static function get_rows($table)
    {
        $table_schema = self::get_table_schema($table);
        if (!$table_schema) {
            return array();
        }

        $rows = DB::select('*')->from($table)->execute()->as_array();

        foreach ($table_schema['columns'] as $col_name => $col_def) {
            if ($col_def['type'] === 'fk_select') {
                $fk_table   = $col_def['fk_table'];
                $fk_display = $col_def['fk_display'];
                $lookup     = self::get_fk_lookup($fk_table, $fk_display);

                foreach ($rows as &$row) {
                    $fk_id = $row[$col_name];
                    $row[$col_name . '_display'] = isset($lookup[$fk_id]) ? $lookup[$fk_id] : '(unknown)';
                }
                unset($row);
            } elseif ($col_def['type'] === 'config_select') {
                $config_map = self::get_config_map($col_def['config']);

                foreach ($rows as &$row) {
                    $val = $row[$col_name];
                    $row[$col_name . '_display'] = isset($config_map[$val]) ? $config_map[$val] : '(unknown)';
                }
                unset($row);
            }
        }

        return $rows;
    }

    /**
     * Get a single row by primary key (or composite key for junction tables)
     */
    public static function get_row($table, $id_or_keys)
    {
        $table_schema = self::get_table_schema($table);
        if (!$table_schema) {
            return null;
        }

        $query = DB::select('*')->from($table);

        if ($table_schema['primary_key']) {
            $query->where($table_schema['primary_key'], '=', $id_or_keys);
        } else {
            // Composite key: $id_or_keys is an assoc array of column => value
            foreach ($table_schema['composite_key'] as $key_col) {
                $query->where($key_col, '=', $id_or_keys[$key_col]);
            }
        }

        return $query->execute()->current();
    }

    /**
     * Build id => display_value lookup map for a foreign key table
     */
    public static function get_fk_lookup($fk_table, $display_col)
    {
        $rows = DB::select('id', $display_col)->from($fk_table)->execute()->as_array();
        $map = array();
        foreach ($rows as $row) {
            $map[$row['id']] = $row[$display_col];
        }
        return $map;
    }

    /**
     * Load a config file (e.g. skills.php / certificates.php) as an id => name map
     */
    public static function get_config_map($config_name)
    {
        static $cache = array();
        if (!isset($cache[$config_name])) {
            $path = APPPATH . 'config/' . $config_name . '.php';
            $cache[$config_name] = is_file($path) ? require $path : array();
        }
        return $cache[$config_name];
    }

    /**
     * Get FK options for building <select> dropdowns in the form
     * Returns array of ['id' => x, 'name' => y]
     */
    public static function get_fk_options($fk_table, $display_col)
    {
        $rows = DB::select('id', $display_col)->from($fk_table)->order_by($display_col, 'ASC')->execute()->as_array();
        $options = array();
        foreach ($rows as $row) {
            $options[] = array('id' => $row['id'], 'name' => $row[$display_col]);
        }
        return $options;
    }

    /**
     * Validate submitted data against the table's required fields.
     * Returns an error message string, or null if valid.
     */
    public static function validate($table, $data, $is_create = true)
    {
        $table_schema = self::get_table_schema($table);
        if (!$table_schema) {
            return 'Invalid table';
        }

        foreach ($table_schema['columns'] as $col_name => $col_def) {
            if (!empty($col_def['readonly']) || !empty($col_def['hidden'])) {
                continue;
            }
            if (!empty($col_def['required'])) {
                $val = isset($data[$col_name]) ? trim((string) $data[$col_name]) : '';
                if ($val === '') {
                    return $col_def['label'] . ' is required';
                }
            }
        }

        return null;
    }

    /**
     * Insert a new row. Returns the new insert ID (or true for composite-key tables).
     */
    public static function create_row($table, $data)
    {
        $table_schema = self::get_table_schema($table);
        $insert_data = self::filter_writable_data($table_schema, $data);

        // Special case: employees get a default hashed password
        if ($table === 'employees') {
            $default_pw = isset($table_schema['default_password']) ? $table_schema['default_password'] : 'password123';
            $insert_data['password'] = password_hash($default_pw, PASSWORD_DEFAULT);
        }

        $result = DB::insert($table)->set($insert_data)->execute();
        return $result[0]; // insert ID (0 for tables without auto-increment pk, e.g. junction table)
    }

    /**
     * Update an existing row by primary key (or composite key)
     */
    public static function update_row($table, $id_or_keys, $data)
    {
        $table_schema = self::get_table_schema($table);
        $update_data = self::filter_writable_data($table_schema, $data);

        // Never allow password/remember_token to be touched via generic update
        unset($update_data['password'], $update_data['remember_token']);

        $query = DB::update($table)->set($update_data);

        if ($table_schema['primary_key']) {
            $query->where($table_schema['primary_key'], '=', $id_or_keys);
        } else {
            foreach ($table_schema['composite_key'] as $key_col) {
                $query->where($key_col, '=', $id_or_keys[$key_col]);
            }
        }

        return $query->execute();
    }

    /**
     * Delete a row by primary key (or composite key)
     */
    public static function delete_row($table, $id_or_keys)
    {
        $table_schema = self::get_table_schema($table);
        $query = DB::delete($table);

        if ($table_schema['primary_key']) {
            $query->where($table_schema['primary_key'], '=', $id_or_keys);
        } else {
            foreach ($table_schema['composite_key'] as $key_col) {
                $query->where($key_col, '=', $id_or_keys[$key_col]);
            }
        }

        return $query->execute();
    }

    /**
     * Filter incoming POST data down to only the writable (non-readonly, non-hidden) columns
     */
    private static function filter_writable_data($table_schema, $data)
    {
        $filtered = array();
        foreach ($table_schema['columns'] as $col_name => $col_def) {
            if (!empty($col_def['readonly']) || !empty($col_def['hidden'])) {
                continue;
            }
            if (array_key_exists($col_name, $data)) {
                $val = $data[$col_name];
                // Normalize empty strings to null for nullable date/FK fields
                if ($val === '') {
                    $val = null;
                }
                $filtered[$col_name] = $val;
            }
        }
        return $filtered;
    }
}
