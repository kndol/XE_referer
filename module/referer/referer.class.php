<?php
/**
 * @class  referer
 * @author haneul (haneul0318@gmail.com)
 * @enhanced by KnDol (kndol@kndol.net)
 * @brief  referer module's class 
 **/
class referer extends ModuleObject {
	/**
	 * constructor
	 *
	 * @return void
	 */
	function referer()
	{
	}

    /**
     * @brief Install referer module 
     **/
    function moduleInstall() {
        return new Object();
    }

    /**
     * @brief 설치가 이상이 없는지 체크하는 method
     **/
    function checkUpdate() {
 		$oDB = &DB::getInstance();
		
		if(!$oDB->isColumnExists("referer_log", "idx")) return true;
		if(!$oDB->isColumnExists("referer_log", "remote")) return true;
		if(!$oDB->isColumnExists("referer_statistics", "idx")) return true;
		if(!$oDB->isColumnExists("referer_remote_statistics", "idx")) return true;
		if(!$oDB->isColumnExists("referer_uagent_statistics", "idx")) return true;
		if(!$oDB->isIndexExists("referer_log","unique_referer_log")) return true;
		if(!$oDB->isIndexExists("referer_statistics","unique_host")) return true;
		if(!$oDB->isIndexExists("referer_remote_statistics","unique_remote")) return true;
		if(!$oDB->isIndexExists("referer_uagent_statistics","unique_uagent")) return true;

	    return false;
    }

    /**
     * @brief 업데이트 실행
     **/
    function moduleUpdate() {
	    $oDB = &DB::getInstance();

		if(!$oDB->isColumnExists("referer_log", "idx")
			|| !$oDB->isColumnExists("referer_statistics", "idx")) {
			$oDB->DropTable("referer_log");
			$oDB->DropTable("referer_statistics");
			$oDB->createTableByXmlFile($this->module_path.'schemas/referer_log.xml');
			$oDB->createTableByXmlFile($this->module_path.'schemas/referer_statistics.xml');
		}
		if (!$oDB->isTableExists("referer_remote_statistics")) {
			$oDB->createTableByXmlFile($this->module_path.'schemas/referer_remote_statistics.xml');
		}
		if (!$oDB->isTableExists("referer_uagent_statistics")) {
			$oDB->createTableByXmlFile($this->module_path.'schemas/referer_uagent_statistics.xml');
		}

        return new Object(0, 'success_updated');
    }

	/**
	 * @brief 삭제시 동작
	 */
	function moduleUninstall() {
	    $oDB = &DB::getInstance();

		$oDB->DropTable("referer_log");
		$oDB->DropTable("referer_statistics");
		$oDB->DropTable("referer_remote_statistics");
		$oDB->DropTable("referer_uagent_statistics");
	}

    /**
     * @brief 캐시 파일 재생성
     **/
    function recompileCache() {
    }
}
/* End of file referer.class.php */
/* Location: ./modules/referer/referer.class.php */
