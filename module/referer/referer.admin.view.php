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
		if ($output->page > $output->total_page) {
			$args->page = $output->total_page;
        	$output = $oRefererModel->getLogList($args);
        }
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
/*
		Context::set('host', '');
		Context::set('remote', '');
		Context::set('uagent', '');
*/
		if ($ranking_mode != 3) {
	        $args->page = Context::get('page'); ///< 페이지
	        $args->sort_index = 'cnt'; ///< 소팅 값
			$args->list_count = 20; // 한 페이지에 표시할 항목 수
			$args->search_keyword  = trim(Context::get('search_keyword'));
		}
        $oRefererModel = &getModel('referer');
        $output = $oRefererModel->getRefererRanking($ranking_mode, $args);
		if ($ranking_mode != 3) {
			if ($output->page > $output->total_page) {
				$args->page = $output->total_page;
    	    	$output = $oRefererModel->getRefererRanking($ranking_mode, $args);
    	    }
	        Context::set('total_count', $output->total_count);
	        Context::set('total_page', $output->total_page);
	        Context::set('page', $output->page);
	        Context::set('page_navigation', $output->page_navigation);
	        Context::set('referer_status', $output->data);
			Context::set('rank', $args->list_count*($output->page-1)+1);
        }
		$this->refererConfig = $oRefererModel->getRefererConfig();
		Context::set('refererConfig', $this->refererConfig);

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
        	case 3:
        		$this->prepareUAStatData($output->data);
   				$this->setTemplateFile('referer_uagent_stat');
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

	function dispRefererAdminUAgentStat(){
        $this->dispRefererAdminRankingPage(3);
	}

	function prepareUAStatData(&$data) {
		$Types = array('desktop' => 0, 'mobile' => 0, 'unknown' => 0, 'bot' => 0, 'notbot' => 0);
		$Bots = array();
		$Browsers = array();
		$OSes = array();
		$IEs = array();
		$Windows = array();
		$Macs = array();
		$iOSes = array();
		$Androids = array();

		foreach($data as $no => $val) {
			$uagent = $val->uagent;
			$count = $val->cnt;
			$bot = $os = $browser = $mac = $ios = $android = $win = $ie = "";
			$mobile = false;
			
			if ( preg_match('/(googlebot)/i',$uagent) || stripos($uagent, 'Google favicon') ) $bot = "Google";
			else if ( preg_match('/(bing|msnbot)/i',$uagent) ) $bot = "Bing";
			else if ( preg_match('/(daumoa)/i',$uagent) ) $bot = "Daum";
			else if ( preg_match('/(naverbot|yeti)/i',$uagent) ) $bot = "Naver";
			else if ( preg_match('/(zumbot)/i',$uagent) ) $bot = "Zum";
			else if ( preg_match('/(yahoo)/i',$uagent) ) $bot = "Yahoo!";
			else if ( preg_match('/(siteexplorer)/i',$uagent) ) $bot = "SiteExplorer";
			else if ( preg_match('/(EasouSpider)/i',$uagent) ) $bot = "EasouSpider";
			else if ( preg_match('/(Sogou)/i',$uagent) ) $bot = "Sogou web spider";
			else if ( preg_match('/(YandexBot)/i',$uagent) ) $bot = "YandexBot";
			else if ( !strstr($uagent, 'SocialXE ClientBot')
				&& (preg_match('/(bot|spider|crawler|slurp|yeti|daumoa|FeedDemon|Aboundex|NewsGatorOnline|DigExt|inboundscore|feedfetcher|Teoma|recruitingpoint|NetcraftSurveyAgent)/i',$uagent)
				|| strstr($uagent, 'MSIE or Firefox mutant; not on Windows server;')) ) $bot = "Others";
		
			if ($bot != "") {
				if (array_key_exists($bot, $Bots)) $Bots[$bot] += $count;
				else $Bots[$bot] = $count;
				$Types['bot'] += $count;
			}
			else {
				if ( stripos($uagent, "iPhone") || stripos($uagent, "iPod") || stripos($uagent, "iPad") ) 
				{
					$mobile = true;
					$os = "iOS";

					if ( preg_match('/OS ([0-9_]+) like/i',$uagent, $matches) ) {
						$ios = "iOS " . str_replace("_", ".", $matches[1]);

						if (array_key_exists($ios, $iOSes)) $iOSes[$ios] += $count;
						else $iOSes[$ios] = $count;
					}
				}
				else if ( stripos($uagent, "Android") ) {
					$mobile = true;
					$os  = 'Android';
					if ( stripos($uagent, "Android 1.0") ) $android = "1.0 Appie pie";
					else if ( stripos($uagent, "Android 1.1") ) $android = "1.1 Bananabread";
					else if ( stripos($uagent, "Android 1.5") ) $android = "1.5 Cupcake";
					else if ( stripos($uagent, "Android 1.6") ) $android = "1.6 Donut";
					else if ( stripos($uagent, "Android 2.0") ) $android = "2.0 Eclair";
					else if ( stripos($uagent, "Android 2.1") ) $android = "2.1 Eclair";
					else if ( stripos($uagent, "Android 2.2") ) $android = "2.2 Froyo";
					else if ( stripos($uagent, "Android 2.3") ) $android = "2.3 Gingerbread";
					else if ( stripos($uagent, "Android 3.0") ) $android = "3.0 Honeycomb";
					else if ( stripos($uagent, "Android 3.1") ) $android = "3.1 Honeycomb";
					else if ( stripos($uagent, "Android 3.2") ) $android = "3.2 Honeycomb";
					else if ( stripos($uagent, "Android 4.0") ) $android = "4.0 ICS";
					else if ( stripos($uagent, "Android 4.1") ) $android = "4.1 Jelly Bean";
					else if ( stripos($uagent, "Android 4.2") ) $android = "4.2 Jelly Bean";
					else if ( stripos($uagent, "Android 4.3") ) $android = "4.3 Jelly Bean";
					else if ( stripos($uagent, "Android 4.4") ) $android = "4.4 KitKat";
					else $android = "Others";

					if (array_key_exists($android, $Androids)) $Androids[$android] += $count;
					else $Androids[$android] = $count;
				}
				else if ( stripos($uagent, "Windows CE") )
				{
					$mobile = true;
					$os = "Windows CE";
					if ( stripos($uagent, "IEMobile") ) $browser = "Internet Explorer Mobile";
				}
				else if ( stripos($uagent, "Zune") )
				{
					$mobile = true;
					$os = "MS Zune OS";
					$browser = "Internet Explorer Mobile";
				}
				else if ( stripos($uagent, "Windows Phone") )
				{
					$mobile = true;
					$os = "MS Windows Phone";
					if ( stripos($uagent, "Windows Phone 6") ) $os .= " 6";
					else if ( stripos($uagent, "Windows Phone OS 7") ) $os .= " 7";
					else if ( stripos($uagent, "Windows Phone 8.0") ) $os .= " 8.0";
					else if ( stripos($uagent, "Windows Phone 8.1") ) $os .= " 8.1";
					$browser = "Internet Explorer Mobile";
				}
				else if ( stripos($uagent, "BlackBerry") )
				{
					$mobile = true;
					$os = "BlackBerry";
					$browser = "BlackBerry Browser";
				}
				else if ( stripos($uagent, "Linux") ) {
					$os = "Linux";
					if ( stripos($uagent, "x86_64") ) $os .= " (64bits)";
				}
				else if ( stripos($uagent, "macintosh") || stripos($uagent, "mac os x") ) {
					$os = "Mac OS X";
					if    ( stripos($uagent, "10.10") || stripos($uagent, "10_10") ) $mac = "10.10 Yosemite";
					else if ( stripos($uagent, "10.9") || stripos($uagent, "10_9") ) $mac = "10.9 Mavericks";
					else if ( stripos($uagent, "10.8") || stripos($uagent, "10_8") ) $mac = "10.8 Mountain Lion";
					else if ( stripos($uagent, "10.7") || stripos($uagent, "10_7") ) $mac = "10.7 Lion";
					else if ( stripos($uagent, "10.6") || stripos($uagent, "10_6") ) $mac = "10.6 Snow Leopard";
					else if ( stripos($uagent, "10.5") || stripos($uagent, "10_5") ) $mac = "10.5 Leopard";
					else if ( stripos($uagent, "10.4") || stripos($uagent, "10_4") ) $mac = "10.4 Tiger";
					else if ( stripos($uagent, "10.3") || stripos($uagent, "10_3") ) $mac = "10.3 Panther";
					else if ( stripos($uagent, "10.2") || stripos($uagent, "10_2") ) $mac = "10.2 Jaguar";
					else if ( stripos($uagent, "10.1") || stripos($uagent, "10_1") ) $mac = "10.1 Puma";
					else if ( stripos($uagent, "10.0") || stripos($uagent, "10_0") ) $mac = "10.0 Cheetar";
					else $mac = "Others";

					if (array_key_exists($mac, $Macs)) $Macs[$mac] += $count;
					else $Macs[$mac] = $count;
				}
				else if ( stripos($uagent, "Windows") || stripos($uagent, "Win") ) {
					$os = "MS Windows";
					if ( stripos($uagent, "NT 6.3") ) $win = "Win 8.1";
					else if ( stripos($uagent, "NT 6.2") ) $win = "Win 8";
					else if ( stripos($uagent, "NT 6.1") ) $win = "Win 7";
					else if ( stripos($uagent, "NT 6.0") ) $win = "Win Vista";
					else if ( stripos($uagent, "NT 5.2") ) $win = "Win 2003 Server";
					else if ( stripos($uagent, "NT 5.1") ) $win = "Win XP";
					else if ( stripos($uagent, "NT 5.0") ) $win = "Win 2000";
					else if ( stripos($uagent, "NT") ) $win = "Win NT";
					else if ( stripos($uagent, "Windows 98") ) $win = "Win 98";
					else if ( stripos($uagent, "Windows 95") ) $win = "Win 95";
					else $win = "Others";
					if ( stripos($uagent, "WOW64") || stripos($uagent, "Win64") || stripos($uagent, "x64") ) {
						$os .= " (64bits)";
						$win .= " (64bits)";
					}
					
					if (array_key_exists($win, $Windows)) $Windows[$win] += $count;
					else $Windows[$win] = $count;

					if ( stripos($uagent, "Trident/7.0") ) $ie = "MSIE 11";
					else if ( stripos($uagent, "Trident/6.0") ) $ie = "MSIE 10";
					else if ( stripos($uagent, "Trident/5.0") ) $ie = "MSIE 9";
					else if ( stripos($uagent, "Trident/4.0") ) $ie = "MSIE 8";
					else if ( stripos($uagent, "MSIE 7") ) $ie = "MSIE 7";
					else if ( stripos($uagent, "MSIE 6") ) $ie = "MSIE 6";
					else if ( stripos($uagent, "MSIE 5") ) $ie = "MSIE 5";

					if ($ie != "") {
						$browser = "Internet Explorer";
						if (array_key_exists($ie, $IEs)) $IEs[$ie] += $count;
						else $IEs[$ie] = $count;
					}
				}
				
				if ($browser == "") {
					if ( stripos($uagent, "NAVER") ) {
						$browser = "Naver App";
						$mobile = true;
					}
					else if ( stripos($uagent, "DaumApps") ) {
						$browser = "Daum App";
						$mobile = true;
					}
					else if ( stripos($uagent, "Chrome") ) $browser = "Google Chrome";
					else if ( stripos($uagent, "Firefox") ) $browser = "Mozilla Firefox";
					else if ( stripos($uagent, "Opera") ) $browser = "Opera Browser";
					else if ( stripos($uagent, "Netscape") ) $browser = "Netscape";
					else if ( stripos($uagent, "Safari") ) $browser = "Apple Safari";
					else $browser = "Others";
				}
				if ($os == "") $os = "Others";
		
				if (array_key_exists($os, $OSes)) $OSes[$os] += $count;
				else $OSes[$os] = $count;
		
				if (array_key_exists($browser, $Browsers)) $Browsers[$browser] += $count;
				else $Browsers[$browser] = $count;

				if ( $mobile )	$Types['mobile'] += $count;
				else if ($os == "Others") $Types['unknown'] += $count;
				else			$Types['desktop'] += $count;

				$Types['notbot'] += $count;
			}
		}
		arsort($Bots);
		arsort($Browsers);
		arsort($OSes);
		arsort($IEs);
		arsort($Windows);
		arsort($Macs);
		arsort($iOSes);
		arsort($Androids);
        Context::set('Types', $Types);
        Context::set('Bots', $Bots);
        Context::set('Browsers', $Browsers);
        Context::set('OSes', $OSes);
        Context::set('IEs', $IEs);
        Context::set('Windows', $Windows);
        Context::set('Macs', $Macs);
        Context::set('iOSes', $iOSes);
        Context::set('Androids', $Androids);
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
