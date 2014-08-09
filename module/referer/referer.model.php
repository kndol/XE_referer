<?php
/**
 * @class refererModel
 * @author haneul (haneul0318@gmail.com)
 * @enhanced by KnDol (kndol@kndol.net)
 * @brief referer 모듈의 Model class
 **/

class refererModel extends referer {
    function init() {
    }

    function isInsertedHost($host) {
        $args->host = $host;
        $output = executeQuery('referer.getHostStatus', $args);
        return $output->data->count?true:false;
    }

    function isInsertedRemote($remote) {
        $args->remote = $remote;
        $output = executeQuery('referer.getRemoteStatus', $args);
        return $output->data->count?true:false;
    }

    function isInsertedUAgent($uagent) {
		$args->uagent = $uagent;
		$output = executeQuery('referer.getUAgentStatus', $args);
		return $output->data->count?true:false;
    }

    function getLogList($obj) {
		if ($obj->host) {
			$args->host = $obj->host;
            $query_id = 'referer.getRefererLogListHost';
		}
		else if ($obj->remote) {
			$args->remote = $obj->remote;
            $query_id = 'referer.getRefererLogListRemote';
		}
		else if ($obj->uagent) {
			$args->uagent = $obj->uagent;
            $query_id = 'referer.getRefererLogListUAgent';
		}
		else {
	        $query_id = 'referer.getRefererLogList';
	        $search_target = Context::get('search_target');
	        if ($search_target == "referer")        $args->search_referer = trim(Context::get('search_keyword'));
	        else if ($search_target == "remote")    $args->search_remote  = trim(Context::get('search_keyword'));
	        else if ($search_target == "uagent")    $args->search_uagent  = trim(Context::get('search_keyword'));
			else if ($search_target == "date")      $args->search_date = preg_replace("/[^0-9]*/s", "", Context::get('search_keyword'));
			else if ($search_target == "date_less") $args->search_date_less = preg_replace("/[^0-9]*/s", "", Context::get('search_keyword'));
			else if ($search_target == "date_more") $args->search_date_more = preg_replace("/[^0-9]*/s", "", Context::get('search_keyword'));
		}

        $args->sort_index = 'regdate';
        $args->page = $obj->page?$obj->page:1;

        $args->list_count = $obj->list_count?$obj->list_count:20;
        $args->page_count = $obj->page_count?$obj->page_count:10;

        $output = executeQuery($query_id, $args);

        // 결과가 없거나 오류 발생시 그냥 return
        if(!$output->toBool()||!count($output->data)) return $output;

        return $output;
    }

	function getRecentRefererList()
	{
        $output = executeQuery('referer.getRecentRefererLog');

        // 결과가 없거나 오류 발생시 그냥 return
        if(!$output->toBool()||!count($output->data)) return;

        return $output->data;
	}

    function getRefererRanking($ranking_mode, $obj) {
    	switch ($ranking_mode) {
    		case 0:
    			return executeQuery("referer.getRefererRanking", $obj);
    			break;
    		case 1:
		        return executeQuery("referer.getRemoteRanking", $obj);
    			break;
    		case 2:
		        return executeQuery("referer.getUAgentRanking", $obj);
    			break;
    		case 3:
		        return executeQuery("referer.getUAgentStatistics", $obj);
    			break;
    	}
    }
    
	/**
	 * @brief Return referer's configuration
	 */
	function getRefererConfig()
	{
		static $refererConfig;

		if($refererConfig) return $refererConfig;

		// Get member configuration stored in the DB
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getModuleConfig('referer');

		if(!$config->GeoIPSite) $config->GeoIPSite = 'freegeoip';

		$refererConfig = $config;

		return $config;
	}
}
/* End of file referer.controller.php */
/* Location: ./modules/referer/referer.model.php */
