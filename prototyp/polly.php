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

        /*****************************************************************************************************
         * Package      : feedtr
         * File         : polly.php
         * Version      : 0.0.1
         * Author       : Christian Land / tagdocs.biz
         *****************************************************************************************************
         * History:
         *
         * v0.0.1       : Erste Version
         *****************************************************************************************************/

	// Allgemeiner Kram
	
	require_once('includes'.DIRECTORY_SEPARATOR.'inc.config.php');			// Konfiguration
	require_once('includes'.DIRECTORY_SEPARATOR.'inc.constants.php');		// Konstanten

	// Klassen für den Hausgebrauch ;-)

	require_once('classes'.DIRECTORY_SEPARATOR.'class.tinyDB.php');			// Datenbank-Klasse von 2005 *g*
	require_once('classes'.DIRECTORY_SEPARATOR.'class.tdb.mysqli.php');		// s.o.

	require_once('classes'.DIRECTORY_SEPARATOR.'class.simplepie.php');		// RSS-Parser

	// OK, legen wir mal los...
	
	$oDatabase		= new tinyMySQLi(DB_ASSOC_ARRAY);

	echo 'Connecting database...'."\n";

	if ($config['db']['port'] > 0)
		$oDatabase->connect($config['db']['host'].':'.$config['db']['port'], $config['db']['database'], $config['db']['username'], $config['db']['password']);
	else
		$oDatabase->connect($config['db']['host'], $config['db']['database'], $config['db']['username'], $config['db']['password']);
	
	// An dieser stelle haben wir hoffentlich ein nettes, kleines Datenbank-Objekt

	// 1. Schritt: Wir holen uns einen zufälligen Feed der noch unbearbeitet ist
	
	echo 'Looking for some dirty job...'."\n";

	$sqlquery	= 'SELECT * FROM '.TABLE_FEEDS.' WHERE (feed_status = 0) AND ((feed_last_poll + feed_interval) <= '.time().') ORDER BY RAND() LIMIT 1';
	
	if ($oDatabase->query($sqlquery))
	{
		if ($oDatabase->numRows() > 0)
		{
			$feed	= $oDatabase->getResult();
			$oDatabase->freeResults();
		}
		else
		{
			echo 'Nothing to do.';
			exit();
		}
	}
	else
	{
		echo $oDatabase->getError();
		exit();
	}

	// 2. Schritt: Wir setzen den Status auf "in Bearbeitung"
	//
	//             feed_last_poll JETZT wird NOCH NICHT gesetzt!!! Ganz wichtig!!!

	echo 'Telling the other Pollys that I\'m busy...'."\n";

	$sqlquery	= 'UPDATE '.TABLE_FEEDS.' SET feed_status = 1 WHERE (feed_id = '.$oFeed['feed_id'].')';

	$oDatabase->query($sqlquery);

	echo 'Fetching RSS-Feed...'."\n";
	echo ' URL: '.$feed['feed_url']."\n";

	// 3. Schritt: Wir holen uns die RSS-Daten

	$url	= $feed['feed_url'];
	$cache	= ($feed['feed_interval'] < 300) ? 300 : $feed['feed_interval'];		// min. 5 Minuten

	$oFeed	= new SimplePie();

	$oFeed->set_feed_url($url);								// URL des Feeds

	$oFeed->set_cache_duration($cache);							// Cache-Zeitraum
	$oFeed->set_cache_location($config['cache_path']);					// Pfad für Cache-Dateien

	$oFeed->set_output_encoding('UTF-8');

	$oFeed->enable_cache(true);

	$oFeed->init();

	if ($oFeed->error())
	{

		// OK, wir haben einen Fehler...

		$sqlquery	= 'INSERT INTO '.TABLE_LOG_ERRORS.' (log_timestamp,log_feed_id,log_error) VALUES('.time().','.$feed['feed_id'].','.mysql_real_escape_string($oFeed->error()).')';

		$oDatabase->query($sqlquery);

		//
		
		$sqlquery	= 'UPDATE '.TABLE_FEEDS.' SET feed_status = 2, feed_error_count = feed_error_count + 1 WHERE (feed_id = '.$feed['feed_id'].')';

	}
	else
	{

		// Alles OK, wir können uns einen Keks freuen

		foreach ($oFeed->get_items() as $item)
		{
			
			// Daten holen und vorbereiten
			
			$title		= mysql_real_escape_string($item->get_title());
			$desc		= mysql_real_escape_string($item->get_description());
			$url		= mysql_real_escape_string($item->get_permalink());
			$time		= (int)$item->get_date('U');
		
			// Unique ID basteln
			
			$uid		= strtoupper(md5(strtolower(trim($url.$time))));
		
			// In die DB schmeissen - falls es den Eintrag noch nicht gibt *g*
			
			$sqlquery	= 'SELECT COUNT(*) AS rcount FROM '.TABLE_POSTS.' WHERE feed_uid = \''.$uid.'\'';

			$oDatabase->query($sqlquery);

			$data		= $oDatabase->getResult();

			$oDatabase->freeResults();

			//

			if ((int)$data['rcount'] == 0)
			{

				$sqlquery	= 'INSERT INTO '.TABLE_POSTS.' (feed_id, feed_uid, post_date, post_title, post_url) VALUES(\''.$feed['feed_id'].'\',\''.$uid.'\','.$time.',\''.$title.'\',\''.$url.'\')';

				$oDatabase->query($sqlquery);

			}

		}

		// Zu guter Letzt - das Status-Update SQL bauen...

		$sqlquery	= 'UPDATE '.TABLE_FEEDS.' SET feed_last_poll = '.time().', feed_status = 0, feed_error_count = 0 WHERE (feed_id = '.$feed['feed_id'].')';

	}

	// x. Schritt: Datenbank aktualisieren

	$oDatabase->query($sqlquery);
	
	// Aufräumen - wir sind ja ordentlich

	$oDatabase->disconnect();

?>
