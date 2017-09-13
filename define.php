<?php
define ('DS',DIRECTORY_SEPARATOR);
define ('file_path',dirname(__FILE__));
define ('model_path',file_path . DS . 'models' . DS);
define ('view_path', file_path . DS . 'views' . DS);
define ('controller_path', file_path . DS . 'controllers'. DS);
define ('lib_path', file_path . DS . 'libs'. DS);
//------ DATABASE ------
define ('DB_server', 'localhost');
define ('DB_user','root');
define ('DB_pass', '');
define ('DB_database','mydb');
define ('DB_table','user');