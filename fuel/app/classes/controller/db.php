<?php

class Controller_Db extends Controller
{
    /**
     * Before filter — authentication + admin_level >= 5 guard
     */
    public function before()
    {
        parent::before();

        if (Session::get('employee_id')) {
            // authenticated via session
        } else {
            $remember_token = Cookie::get('remember_token');
            if ($remember_token) {
                $employee = Model_Employee::find_by_remember_token($remember_token);
                if ($employee) {
                    Session::set('employee_id', $employee['id']);
                    Session::set('employee_name', $employee['name']);
                }
            }

            if (!Session::get('employee_id')) {
                Response::redirect('/auth');
                return;
            }
        }

        $admin_level = Model_Position::get_admin_level(Session::get('employee_id'));
        if ($admin_level < 5) {
            Response::redirect('/dashboard');
            return;
        }
    }

    /**
     * GET /db
     * Main DB management page — left table list + grid + slide-in form
     */
    public function action_index()
    {
        $employee_id = Session::get('employee_id');
        $admin_level = Model_Position::get_admin_level($employee_id);
        $current_employee = Model_Employee::find_by_id($employee_id);

        $current_user = array(
            'id'          => $employee_id,
            'name'        => Session::get('employee_name'),
            'admin_level' => $admin_level,
            'avatar'      => !empty($current_employee['avatar']) ? '/assets/uploads/avatars/' . $current_employee['avatar'] : '',
        );

        $schema = Model_Db::get_schema();

        // Build a lightweight table list for the left panel (name, label, icon)
        $table_list = array();
        foreach ($schema as $table_name => $table_def) {
            $table_list[] = array(
                'table' => $table_name,
                'label' => $table_def['label'],
                'icon'  => $table_def['icon'],
            );
        }

        $view = View::forge('db/index');
        $view->set('schema_json',     json_encode($schema),     false);
        $view->set('table_list_json', json_encode($table_list), false);
        $view->set('current_user',    $current_user,            true);

        return Response::forge($view);
    }

    /**
     * GET /db/get_rows?table=xxx
     * AJAX: returns all rows for the given table (with FK display names resolved)
     */
    public function action_get_rows()
    {
        $table = Input::get('table', '');

        if (!Model_Db::is_valid_table($table)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Invalid table')), 400, array('Content-Type' => 'application/json'));
        }

        $rows = Model_Db::get_rows($table);

        return Response::forge(json_encode(array('success' => true, 'rows' => $rows)), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * GET /db/get_row?table=xxx&id=yyy (or &employee_id=x&project_id=y for composite keys)
     * AJAX: returns a single row for populating the edit form
     */
    public function action_get_row()
    {
        $table = Input::get('table', '');

        if (!Model_Db::is_valid_table($table)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Invalid table')), 400, array('Content-Type' => 'application/json'));
        }

        $table_schema = Model_Db::get_table_schema($table);

        if ($table_schema['primary_key']) {
            $id = Input::get('id');
            $row = Model_Db::get_row($table, $id);
        } else {
            $keys = array();
            foreach ($table_schema['composite_key'] as $key_col) {
                $keys[$key_col] = Input::get($key_col);
            }
            $row = Model_Db::get_row($table, $keys);
        }

        if (!$row) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Row not found')), 404, array('Content-Type' => 'application/json'));
        }

        return Response::forge(json_encode(array('success' => true, 'row' => $row)), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * GET /db/get_fk_options?table=xxx&column=yyy
     * AJAX: returns dropdown options for a foreign-key or config-select column
     */
    public function action_get_fk_options()
    {
        $table  = Input::get('table', '');
        $column = Input::get('column', '');

        $table_schema = Model_Db::get_table_schema($table);
        if (!$table_schema || !isset($table_schema['columns'][$column])) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Invalid table or column')), 400, array('Content-Type' => 'application/json'));
        }

        $col_def = $table_schema['columns'][$column];
        $options = array();

        if ($col_def['type'] === 'fk_select') {
            $options = Model_Db::get_fk_options($col_def['fk_table'], $col_def['fk_display']);
        } elseif ($col_def['type'] === 'config_select') {
            $map = Model_Db::get_config_map($col_def['config']);
            foreach ($map as $id => $name) {
                $options[] = array('id' => $id, 'name' => $name);
            }
        }

        return Response::forge(json_encode(array('success' => true, 'options' => $options)), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * POST /db/create
     * AJAX: insert a new row into the given table
     */
    public function action_create()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Method not allowed')), 405, array('Content-Type' => 'application/json'));
        }

        $table = Input::post('table', '');

        if (!Model_Db::is_valid_table($table)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Invalid table')), 400, array('Content-Type' => 'application/json'));
        }

        $data = Input::post('data');
        $data = is_array($data) ? $data : json_decode($data, true);

        if (!is_array($data)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Invalid data payload')), 400, array('Content-Type' => 'application/json'));
        }

        $error = Model_Db::validate($table, $data, true);
        if ($error) {
            return Response::forge(json_encode(array('success' => false, 'error' => $error)), 400, array('Content-Type' => 'application/json'));
        }

        // Extra safety: employees created here must have a unique email
        if ($table === 'employees' && !empty($data['email']) && Model_Employee::email_exists($data['email'])) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'This email is already in use')), 400, array('Content-Type' => 'application/json'));
        }

        try {
            $new_id = Model_Db::create_row($table, $data);
            return Response::forge(json_encode(array('success' => true, 'id' => $new_id)), 200, array('Content-Type' => 'application/json'));
        } catch (Exception $e) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Database error: ' . $e->getMessage())), 500, array('Content-Type' => 'application/json'));
        }
    }

    /**
     * POST /db/update
     * AJAX: update an existing row
     */
    public function action_update()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Method not allowed')), 405, array('Content-Type' => 'application/json'));
        }

        $table = Input::post('table', '');

        if (!Model_Db::is_valid_table($table)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Invalid table')), 400, array('Content-Type' => 'application/json'));
        }

        $table_schema = Model_Db::get_table_schema($table);

        $data = Input::post('data');
        $data = is_array($data) ? $data : json_decode($data, true);

        if (!is_array($data)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Invalid data payload')), 400, array('Content-Type' => 'application/json'));
        }

        $error = Model_Db::validate($table, $data, false);
        if ($error) {
            return Response::forge(json_encode(array('success' => false, 'error' => $error)), 400, array('Content-Type' => 'application/json'));
        }

        // Determine primary/composite key values
        if ($table_schema['primary_key']) {
            $id = Input::post('id');
            if (!$id) {
                return Response::forge(json_encode(array('success' => false, 'error' => 'Missing row ID')), 400, array('Content-Type' => 'application/json'));
            }

            // Extra safety: employees must keep a unique email (excluding self)
            if ($table === 'employees' && !empty($data['email']) && Model_Employee::email_exists($data['email'], $id)) {
                return Response::forge(json_encode(array('success' => false, 'error' => 'This email is already in use')), 400, array('Content-Type' => 'application/json'));
            }

            $key = $id;
        } else {
            $key = array();
            foreach ($table_schema['composite_key'] as $key_col) {
                $key[$key_col] = Input::post($key_col);
            }
        }

        try {
            Model_Db::update_row($table, $key, $data);
            return Response::forge(json_encode(array('success' => true)), 200, array('Content-Type' => 'application/json'));
        } catch (Exception $e) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Database error: ' . $e->getMessage())), 500, array('Content-Type' => 'application/json'));
        }
    }

    /**
     * POST /db/delete
     * AJAX: delete a row
     */
    public function action_delete()
    {
        if (Input::method() !== 'POST') {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Method not allowed')), 405, array('Content-Type' => 'application/json'));
        }

        $table = Input::post('table', '');

        if (!Model_Db::is_valid_table($table)) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Invalid table')), 400, array('Content-Type' => 'application/json'));
        }

        $table_schema = Model_Db::get_table_schema($table);

        if ($table_schema['primary_key']) {
            $id = Input::post('id');
            if (!$id) {
                return Response::forge(json_encode(array('success' => false, 'error' => 'Missing row ID')), 400, array('Content-Type' => 'application/json'));
            }
            $key = $id;
        } else {
            $key = array();
            foreach ($table_schema['composite_key'] as $key_col) {
                $key[$key_col] = Input::post($key_col);
            }
        }

        try {
            Model_Db::delete_row($table, $key);
            return Response::forge(json_encode(array('success' => true)), 200, array('Content-Type' => 'application/json'));
        } catch (Exception $e) {
            return Response::forge(json_encode(array('success' => false, 'error' => 'Cannot delete: this row may be referenced by other records.')), 500, array('Content-Type' => 'application/json'));
        }
    }
}
