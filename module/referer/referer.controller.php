<?php
    /**
     * @class  refererController
     * @author haneul (haneul0318@gmail.com) 
	 * @enhanced by KnDol (kndol@kndol.net)
     * @brief  referer 모듈의 controller class
     **/

    class refererController extends referer {
        /**
         * @brief initialization
         **/
        function init() {
        }

		function procRefererExecute($delete_olddata) {
		    // Log only from different hosts
		    $direct_access = empty($_SERVER["HTTP_REFERER"]);

	       	$referer = parse_url($_SERVER["HTTP_REFERER"]);
	    	if(!$direct_access && $referer['host'] == $_SERVER['HTTP_HOST']) return;
	
		    $oDB = &DB::getInstance();
		    $oDB->begin();
		    $ret = $this->insertRefererLog($direct_access ? "localhost" : $referer['host'], $direct_access ? "http://localhost" : removeHackTag($_SERVER["HTTP_REFERER"]), $_SERVER["HTTP_USER_AGENT"]);
		    if (!$ret->error)
		    {
			    $this->deleteOlddatedRefererLogs($delete_olddata);
			    $this->updateRefererStatistics($direct_access ? "localhost" : $referer['host']);
			    $oDB->commit();
			}
		}
	
		function updateRefererStatistics($host)
		{
		    $oRefererModel = &getModel('referer');
		    $args->host = $host;
		    if($oRefererModel->isInsertedHost($host))
		    {
				$output = executeQuery('referer.updateRefererStatistics', $args);
		    }
		    else
		    {
				$output = executeQuery('referer.insertRefererStatistics', $args);
		    }
	
		    return $output;
		}
	
		function insertRefererLog($host, $url, $uagent)
		{
		    $recent = &getModel('referer')->getRecentRefererList();
		    if ($recent->url == $url && $recent->uagent == $uagent)
		    {
			    $args->regdate_last = date("YmdHis");
		    	$args->regdate      = $recent->regdate;
		    	$args->url          = $recent->url;
		    	$args->uagent       = $recent->uagent;

			    return executeQuery('referer.updateRefererLog', $args);
		    }
			else
			{
			    $args->regdate = $args->regdate_last = date("YmdHis");
			    $args->host = $host;
			    $args->url = $url;
			    $args->uagent = $uagent;

			    return executeQuery('referer.insertRefererLog', $args);
		    }
		}
	
		function deleteOlddatedRefererLogs($delete_olddata)
		{
			if ($delete_olddata<1) return true;
			$day = "-" . (($delete_olddata == 1) ? $delete_olddata . " day" : $delete_olddata . " days");
		    $args->regdate = date("YmdHis", strtotime($day));
		    return executeQuery('referer.deleteOlddatedLogs', $args);
		}
    }
?>
