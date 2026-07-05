<?php
return array(
	'_root_'  => 'welcome/index',  // The default route
	'_404_'   => 'welcome/404',    // The main 404 route
	'dashboard' => 'dashboard/index',  // The dashboard route
	'profile' => 'profile/index',  // The profile route
	'project' => 'project/index',  // The project management route
	'project/update' => 'project/update',
	'project/create' => 'project/create',
	'project/delete' => 'project/delete',
	'project/get' => 'project/get',
	'project/search_employee' => 'project/search_employee',

	'account' => 'account/index',
	'account/verify_password' => 'account/verify_password',
	'account/update_name' => 'account/update_name',
	'account/update_email' => 'account/update_email',
	'account/update_password' => 'account/update_password',

	'db' => 'db/index',
	'db/get_rows' => 'db/get_rows',
	'db/get_row' => 'db/get_row',
	'db/get_fk_options' => 'db/get_fk_options',
	'db/create' => 'db/create',
	'db/update' => 'db/update',
	'db/delete' => 'db/delete',

	'hello(/:name)?' => array('welcome/hello', 'name' => 'hello'),
);
