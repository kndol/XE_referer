<?php
/**
 * @class  RefererAdminView
 * @author haneul (haneul0318@gmail.com)
 * @enhanced by KnDol (kndol@kndol.net)
 * @brief  Referer 모듈의 Admin view class
 **/
class RefererAdminView extends Referer {
	/**
	 * Referer module config.
	 *
	 * @var Object
	 */
	var $refererConfig = NULL;

    /**
     * @brief 초기화
     **/
    function init() {
        // 템플릿 경로 지정 
        $this->setTemplatePath($this->module_path.'tpl');
    }

    /**
     * @brief 관리자 페이지 초기화면
     **/
    function dispRefererAdminIndex() {
        $this->dispRefererAdminList();
    }

	function dispRefererAdminDeleteStat () {
	    if(!Context::get('host')) return $this->dispRefererAdminRanking();
	    $this->setTemplateFile('delete_stat');
	}

	function dispRefererAdminDeleteRemoteStat () {
	    if(!Context::get('remote')) return $this->dispRefererAdminRemoteRanking();
	    $this->setTemplateFile('delete_stat');
	}

	function dispRefererAdminDeleteUAgentStat () {
	    if(!Context::get('uagent')) return $this->dispRefererAdminUAgentRanking();
	    $this->setTemplateFile('delete_stat');
	}

	function dispRefererAdminResetData () {
	    $this->setTemplateFile('reset_data');
	}
	
    function dispRefererAdminList() {
        // 목록을 구하기 위한 옵션
        $args->host = Context::get('host'); ///< 선택한 호스트
        $args->remote = Context::get('remote'); ///< 선택한 리모트IP
        $args->uagent = Context::get('uagent'); ///< 선택한 리모트IP
        $args->page = Context::get('page'); ///< 페이지
        $args->sort_index = 'regdate'; ///< 소팅 값

        $oRefererModel = &getModel('referer');
        $output = $oRefererModel->getLogList($args);
		if ($output->page > $output->total_page) $args->page = $output->total_page;
        $output = $oRefererModel->getLogList($args);
		$this->refererConfig = $oRefererModel->getRefererConfig();

		Context::set('refererConfig', $this->refererConfig);
        Context::set('total_count', $output->total_count);
        Context::set('total_page', $output->total_page);
        Context::set('page', $output->page);
        Context::set('referer_list', $output->data);
        Context::set('page_navigation', $output->page_navigation);

        // 템플릿 지정
        $this->setTemplatePath($this->module_path.'tpl');
        $this->setTemplateFile('referer_list');
	}
	
	function dispRefererAdminRankingPage($ranking_mode){
		Context::set('host', '');
		Context::set('remote', '');
		Context::set('uagent', '');
		Context::set('search_keyword', '');
        $args->page = Context::get('page'); ///< 페이지
        $args->sort_index = 'cnt'; ///< 소팅 값
		$args->list_count = 20; // 한 페이지에 표시할 항목 수
		
        $oRefererModel = &getModel('referer');
        $output = $oRefererModel->getRefererStatus($ranking_mode, $args);
		if ($output->page > $output->total_page) $args->page = $output->total_page;
        $output = $oRefererModel->getRefererStatus($ranking_mode, $args);
		$this->refererConfig = $oRefererModel->getRefererConfig();

		Context::set('refererConfig', $this->refererConfig);
        Context::set('total_count', $output->total_count);
        Context::set('total_page', $output->total_page);
        Context::set('page', $output->page);
        Context::set('page_navigation', $output->page_navigation);
        Context::set('referer_status', $output->data);
		Context::set('rank', $args->list_count*($output->page-1)+1);
		

        // 템플릿 지정
        $this->setTemplatePath($this->module_path.'tpl');
        switch ($ranking_mode) {
        	case 0:
        		$this->setTemplateFile('referer_ranking');
        		break;
        	case 1:
        		$this->setTemplateFile('referer_remote_ranking');
        		break;
        	case 2:
   				$this->setTemplateFile('referer_uagent_ranking');
        		break;
		}   
	}

	function dispRefererAdminRanking(){
        $this->dispRefererAdminRankingPage(0);
	}
	
	function dispRefererAdminRemoteRanking(){
        $this->dispRefererAdminRankingPage(1);
	}

	function dispRefererAdminUAgentRanking(){
        $this->dispRefererAdminRankingPage(2);
	}

	function dispRefererAdminConfirmReset(){
		$this->setTemplatePath($this->module_path.'tpl');
        $this->setTemplateFile('reset_data');
	}
	
	/**
	 * Set the config.
	 *
	 * @return void
	 */
	public function dispRefererAdminConfig()
	{
        $oRefererModel = &getModel('referer');
		$this->refererConfig = $oRefererModel->getRefererConfig();
		Context::set('refererConfig', $this->refererConfig);

		$this->setTemplateFile('referer_config');
	}
}
/* End of file referer.admin.view.php */
/* Location: ./modules/referer/referer.admin.view.php */
