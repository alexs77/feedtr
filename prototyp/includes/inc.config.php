<?php

        // ===================================================================================================
        // Package      : feedtr
        // File         : inc.config.php
        // Version      : 0.0.1
        // Author       : Christian Land / tagdocs.biz
        // ===================================================================================================
        // History:
        //
        // v0.0.1       : Erste Version
        // ===================================================================================================
	
	$config			= array();
	
	$config['db']		= array( 'host'		=> 'localhost',
					 'port'		=> 3306,
					 'username'	=> 'root',
					 'password'	=> '',
					 'database'	=> 'test2',
					 'prefix'	=> 'feedtr_'		);

	$config['cache_path']	= 'cache';

?>