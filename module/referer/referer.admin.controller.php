<?php
/**
 * @class  refererAdminController 
 * @author haneul (haneul0318@gmail.com) 
 * @enhanced by KnDol (kndol@kndol.net)
 * @brief  Referer 모듈의 admin controller class
 **/
class refererAdminController extends referer {
    /**
     * @brief 초기화
     **/
    function init() {
    }

    function procRefererAdminDeleteStat() {
	    $args->host = Context::get('host');
	    $args->remote = Context::get('remote');
	    $args->uagent = Context::get('uagent');

		$oDB = &DB::getInstance();
		$oDB->begin();

	    if ($args->host != "")        $output = executeQuery('referer.deleteRefererStat', $args);
	    else if ($args->remote != "") $output = executeQuery('referer.deleteRemoteStat', $args);
	    else                          $output = executeQuery('referer.deleteUAgentStat', $args);
        if(!$output->toBool()) {
        	$oDB->rollback();
			return $output;
		}
		
		$oDB->commit();

		$this->add('called_from', $args->host ? 'referer': ($args->remote ? 'remote' : 'uagent'));
		$this->add('page',Context::get('page'));
        $this->setMessage('success_deleted');
	}
	
	function procRefererAdminResetData() {
	    $oDB = &DB::getInstance();
	    $oDB->begin();

		$output = $oDB->DropTable("referer_log");
        if($output->error) return $output;
		$output = $oDB->createTableByXmlFile($this->module_path.'schemas/referer_log.xml');
        if($output->error) return $output;

		$output = $oDB->DropTable("referer_statistics");
        if($output->error) return $output;
		$output = $oDB->createTableByXmlFile($this->module_path.'schemas/referer_statistics.xml');
        if($output->error) return $output;

		$output = $oDB->DropTable("referer_remote_statistics");
        if($output->error) return $output;
		$output = $oDB->createTableByXmlFile($this->module_path.'schemas/referer_remote_statistics.xml');
        if($output->error) return $output;

		$output = $oDB->DropTable("referer_uagent_statistics");
        if($output->error) return $output;
		$output = $oDB->createTableByXmlFile($this->module_path.'schemas/referer_uagent_statistics.xml');
        if($output->error) return $output;
        
		$oDB->commit();

		$this->add('called_from', '');

        $this->setMessage('success_reset');
	}

	public function procRefererAdminInsertConfig()
	{
		$args = Context::gets(
			'GeoIPSite'
		);

		if ($args->GeoIPSite == '') $args->GeoIPSite = 'freegeoip';
		
		$oModuleController = getController('module');
		$output = $oModuleController->updateModuleConfig('referer', $args);
        if(!$output->toBool()) return $output;

		// default setting end
		$this->setMessage('success_updated');

		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'referer', 'admin', 'act', 'dispRefererAdminConfig');
		$this->setRedirectUrl($returnUrl);
	}
}
/* End of file referer.admin.controller.php */
/* Location: ./modules/referer/referer.admin.controller.php */
