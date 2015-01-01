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
		$request_uri = removeHackTag($_SERVER["REQUEST_URI"]);
		$logged_info = Context::get('logged_info');
		$oRefererModel = &getModel('referer');
		if($oRefererModel->isBot($uagent))
			$member_srl = -1;
		else if ($logged_info == NULL)
			$member_srl = 0;
		else
			$member_srl = $logged_info->member_srl;
		$ref_mid = Context::get('mid');
		$ref_document_srl = Context::get('document_srl');
		if (!$ref_mid && !$ref_document_srl) $ref_mid = '/';

	    $oDB = &DB::getInstance();
	    $oDB->begin();
	    $ret = $this->insertRefererLog($remote, $referer['host'], $direct_access ? "http://localhost" : removeHackTag($_SERVER["HTTP_REFERER"]), $uagent, $member_srl, $request_uri, $ref_mid, $ref_document_srl);
	    if(!$ret->error)
	    {
		    $this->deleteOlddatedRefererLogs($delete_olddata);
		    $this->updateRefererStatistics($remote, $referer['host'], $uagent, $member_srl, $ref_mid, $ref_document_srl);
		    $oDB->commit();
		}
	}

	function updateRefererStatistics($remote, $host, $uagent, $member_srl, $ref_mid, $ref_document_srl)
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
		if($host != "") {
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
		if($uagent != "") {
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

	    $args->member_srl = $member_srl;
	    if($oRefererModel->isInsertedUser($member_srl))
	    {
			$output = executeQuery('referer.updateUserStatistics', $args);
	    }
	    else
	    {
			$output = executeQuery('referer.insertUserStatistics', $args);
	    }

	    $args->ref_mid = $ref_mid;
	    $args->ref_document_srl = $ref_document_srl;
	    if($oRefererModel->isInsertedPage($ref_mid, $ref_document_srl))
	    {
			$output = executeQuery('referer.updatePageStatistics', $args);
	    }
	    else
	    {
			$output = executeQuery('referer.insertPageStatistics', $args);
	    }

	    return $output;
	}

	function insertRefererLog($remote, $host, $url, $uagent, $member_srl, $request_uri, $ref_mid, $ref_document_srl)
	{
	    $recent = &getModel('referer')->getRecentRefererList();
	    if((($url != "http://localhost" && $recent->url == $url) || ($url == "http://localhost" && $recent->host == $host))
	    	&& $recent->uagent == $uagent && $recent->member_srl == $member_srl && $recent->request_uri == $request_uri)
	    {
		    $args->regdate_last		= date("YmdHis");
	    	$args->regdate			= $recent->regdate;
	    	$args->url				= $recent->url;
	    	$args->uagent			= $recent->uagent;
	    	$args->member_srl		= $recent->member_srl;
	    	$args->request_uri		= $recent->request_uri;

		    return executeQuery('referer.updateRefererLog', $args);
	    }
		else
		{
		    $args->regdate = $args->regdate_last = date("YmdHis");
		    $args->remote			= $remote;
		    $args->host				= $host;
		    $args->url				= $url;
		    $args->uagent			= $uagent;
	    	$args->member_srl		= $member_srl;
	    	$args->request_uri		= $request_uri;
			$args->ref_mid 			= $ref_mid;
			$args->ref_document_srl	= $ref_document_srl;

		    return executeQuery('referer.insertRefererLog', $args);
	    }
	}

	function deleteOlddatedRefererLogs($delete_olddata)
	{
		if($delete_olddata<1) return true;
		$day = "-" . (($delete_olddata == 1) ? $delete_olddata . " day" : $delete_olddata . " days");
	    $args->regdate = date("YmdHis", strtotime($day));
	    return executeQuery('referer.deleteOlddatedLogs', $args);
	}
}
/* End of file referer.controller.php */
/* Location: ./modules/referer/referer.controller.php */
