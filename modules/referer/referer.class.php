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
		if(!$oDB->isColumnExists("referer_log", "member_srl")) return true;
		if(!$oDB->isColumnExists("referer_log", "request_uri")) return true;
		if(!$oDB->isColumnExists("referer_log", "ref_mid")) return true;
		if(!$oDB->isColumnExists("referer_log", "ref_document_srl")) return true;
		if(!$oDB->isColumnExists("referer_log", "country_code")) return true;
		if(!$oDB->isColumnExists("referer_statistics", "idx")) return true;
		if(!$oDB->isColumnExists("referer_remote_statistics", "idx")) return true;
		if(!$oDB->isColumnExists("referer_remote_statistics", "country_code")) return true;
		if(!$oDB->isColumnExists("referer_uagent_statistics", "idx")) return true;
		if(!$oDB->isIndexExists("referer_log","unique_referer_log")) return true;
		if(!$oDB->isIndexExists("referer_statistics","unique_host")) return true;
		if(!$oDB->isIndexExists("referer_remote_statistics","unique_remote")) return true;
		if(!$oDB->isIndexExists("referer_uagent_statistics","unique_uagent")) return true;
		if(!$oDB->isTableExists("referer_user_statistics")) return true;
		if(!$oDB->isTableExists("referer_page_statistics")) return true;
		if(!$oDB->isTableExists("referer_country_statistics")) return true;

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
		if(!$oDB->isColumnExists("referer_log", "member_srl")) {
			$oDB->addColumn("referer_log", "member_srl", 'number', 11);
		}
		if(!$oDB->isColumnExists("referer_log", "request_uri")) {
			$oDB->addColumn("referer_log", "request_uri", 'varchar', 250);
		}
		if(!$oDB->isColumnExists("referer_log", "ref_mid")) {
			$oDB->addColumn("referer_log", "ref_mid", 'varchar', 40);
		}
		if(!$oDB->isColumnExists("referer_log", "ref_document_srl")) {
			$oDB->addColumn("referer_log", "ref_document_srl", 'number', 11);
		}
		if(!$oDB->isColumnExists("referer_log", "country_code")) {
			$oDB->addColumn("referer_log", "country_code", 'char', 2);
		}
		if(!$oDB->isColumnExists("referer_remote_statistics", "country_code")) {
			$oDB->addColumn("referer_remote_statistics", "country_code", 'char', 2);
		}
		if(!$oDB->isTableExists("referer_remote_statistics")) {
			$oDB->createTableByXmlFile($this->module_path.'schemas/referer_remote_statistics.xml');
		}
		if(!$oDB->isTableExists("referer_uagent_statistics")) {
			$oDB->createTableByXmlFile($this->module_path.'schemas/referer_uagent_statistics.xml');
		}
		if(!$oDB->isTableExists("referer_user_statistics")) {
			$oDB->createTableByXmlFile($this->module_path.'schemas/referer_user_statistics.xml');
		}
		if(!$oDB->isTableExists("referer_page_statistics")) {
			$oDB->createTableByXmlFile($this->module_path.'schemas/referer_page_statistics.xml');
		}
		if(!$oDB->isTableExists("referer_country_statistics")) {
			$oDB->createTableByXmlFile($this->module_path.'schemas/referer_country_statistics.xml');
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
		$oDB->DropTable("referer_user_statistics");
		$oDB->DropTable("referer_page_statistics");
		$oDB->DropTable("referer_country_statistics");
	}

	/**
	 * @brief 캐시 파일 재생성
	 **/
	function recompileCache() {
	}
	
	function getMemberSrlFromUserID($user_id) {
		if ($user_id == '_Bots_') {
			$member_srl = -1;
		}
		else if ($user_id == '_Not_Logined_') {
			$member_srl = 0;
		}
		else {
			$oMemberModel = &getModel('member');
			$member_info = $oMemberModel->getMemberInfoByUserID($user_id);
			$member_srl = $member_info->member_srl;
		}
		return $member_srl;
	}

	function getUserIDFromMemberSrl($member_srl) {
		if ($member_srl == -1) {
			$user_id = '_Bots_';
		}
		else if ($member_srl == 0) {
			$user_id = '_Not_Logined_';
		}
		else {
			$oMemberModel = &getModel('member');
			$member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl);
			$user_id = $member_info->user_id;
		}
		return $user_id;
	}

	function getUserStringFromMemberSrl($member_srl, $href="", $title="") {
		$lang = Context::get('lang');

		if ($href) $user = '<a href="' . $href . '" title="'.$title.'">'.$user.'</a>';

		if ($member_srl == -1) {
			$user = $lang->ua_bot;
			if ($href) $user = '<a href="' . $href . '" title="'.$title.'">'.$user.'</a>';
		}
		else if ($member_srl == 0) {
			$user = $lang->not_logged_in;
			if ($href) $user = '<a href="' . $href . '" title="'.$title.'">'.$user.'</a>';
		}
		else {
			$oMemberModel = &getModel('member');
			$member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl);
			$user = $member_info->user_id;
			if ($href) $user = '<a href="' . $href . '" title="'.$title.'">'.$user.'</a>';
			if ($member_info->nick_name!=''||$member_info->email_address!='') {
				$user .= " (";
				if ($member_info->nick_name!='')
					$user .= $member_info->nick_name;
				if($member_info->email_address!='')
					$user .= ' &lt;<a href="mailto:' . $member_info->email_address . '">' . $member_info->email_address . '</a>&gt';
				$user .= ")";
			}
		}
		return $user;
	}
}
/* End of file referer.class.php */
/* Location: ./modules/referer/referer.class.php */
