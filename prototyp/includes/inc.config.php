<?php
/* ===============================================================================
 * Subversion information:
 * $LastChangedDate: 2008-09-07 20:21:43 +0200 (So, 07 Sep 2008) $
 * $LastChangedRevision: 64 $
 * $LastChangedBy: a.skwar $
 * $HeadURL: https://pas3.googlecode.com/svn/trunk/paras3/src/paras3.py $
 * $Id: paras3.py 64 2008-09-07 18:21:43Z a.skwar $
 * ===============================================================================
 */

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
