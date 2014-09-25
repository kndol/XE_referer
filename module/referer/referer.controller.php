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

       	$remote = $_SERVER["REMOTE_ADDR"];
		$uagent = removeHackTag($_SERVER["HTTP_USER_AGENT"]);

	    $oDB = &DB::getInstance();
	    $oDB->begin();
	    $ret = $this->insertRefererLog($remote, $referer['host'], $direct_access ? "http://localhost" : removeHackTag($_SERVER["HTTP_REFERER"]), $uagent);
	    if (!$ret->error)
	    {
		    $this->deleteOlddatedRefererLogs($delete_olddata);
		    $this->updateRefererStatistics($remote, $referer['host'], $uagent);
		    $oDB->commit();
		}
	}

	function updateRefererStatistics($remote, $host, $uagent)
	{
	    $oRefererModel = &getModel('referer');
	    
	    $args->remote = $remote;
	    if($oRefererModel->isInsertedRemote($remote))
	    {
			$output = executeQuery('referer.updateRemoteStatistics', $args);
	    }
	    else
	    {
			$output = executeQuery('referer.insertRemoteStatistics', $args);
	    }
		if ($host != "") {
		    $args->host = $host;
		    if($oRefererModel->isInsertedHost($host))
		    {
				$output = executeQuery('referer.updateRefererStatistics', $args);
		    }
		    else
		    {
				$output = executeQuery('referer.insertRefererStatistics', $args);
		    }
		}
		if ($uagent != "") {
		    $args->uagent = $uagent;
		    if($oRefererModel->isInsertedUAgent($uagent))
		    {
				$output = executeQuery('referer.updateUAgentStatistics', $args);
		    }
		    else
		    {
				$output = executeQuery('referer.insertUAgentStatistics', $args);
		    }
		}
	    return $output;
	}

	function insertRefererLog($remote, $host, $url, $uagent)
	{
	    $recent = &getModel('referer')->getRecentRefererList();
	    if ((($url != "http://localhost" && $recent->url == $url) || ($url == "http://localhost" && $recent->host == $host)) && $recent->uagent == $uagent)
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
		    $args->remote = $remote;
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
/* End of file referer.controller.php */
/* Location: ./modules/referer/referer.controller.php */
