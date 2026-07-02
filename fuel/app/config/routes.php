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
	
	'hello(/:name)?' => array('welcome/hello', 'name' => 'hello'),
);
