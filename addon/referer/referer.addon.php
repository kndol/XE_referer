<?php
	if(!defined("__ZBXE__")) exit();
	if(!defined("__XE__")) exit();

	/**
	 * @file referer.addon.php 
	 * @author haneul (haneul0318@gmail.com)
	 * @editer Wincomi (wincomi@wincomi.com)
	 * @enhanced by KnDol (kndol@kndol.net)
	 **/

	if(Context::getResponseMethod() == 'XMLRPC' or $this->module == 'admin') return;
	if(preg_match("/\.(jpg|gif|png|js|css)$/", $_SERVER['REQUEST_URI'])) return;

	// called_position가 before_module_init 이고 module이 admin이 아닐 경우
	if($called_position == 'before_module_init' && Context::get('module') != 'admin' && !$GLOBALS['__referer_addon_called__']
		&& ($addon_info->include_direct_access == "yes" || !empty($_SERVER["HTTP_REFERER"])))
	{
		$GLOBALS['__referer_addon_called__'] = true;

		// 리퍼러 모듈이 정상적으로 설치되어 있는지 검사
		// 리퍼러 모듈 3.8.0 이전 버전에서는 isBot 함수가 없으므로 사이트가 정상적으로 나오게 그냥 리턴
		$oRefererModel = &getModel('referer');
		if(!$oRefererModel || !method_exists($oRefererModel, 'isBot')) return;
		$oController = &getController('referer');
		if(!$oController || !method_exists($oController, 'procRefererExecute')) return;

		if($logged_info->is_admin == "Y" && $addon_info->admin != "yes") return;

		$exclude_uagent = trim($addon_info->exclude_uagent);
		$exclude_host   = trim($addon_info->exclude_host);
		$referer        = parse_url($_SERVER["HTTP_REFERER"]);
		$uagent         = $_SERVER["HTTP_USER_AGENT"];
		
		if($addon_info->exclude_bot == 'yes' && $oRefererModel->isBot($uagent)) return;
		if($exclude_uagent && preg_match("/$exclude_uagent/i", $uagent)) return;
		if($exclude_host   && ((!empty($_SERVER["HTTP_REFERER"]) && preg_match("/$exclude_host/i", $referer['host'])) || preg_match("/$exclude_host/i", $_SERVER["REMOTE_ADDR"]))) return;
		
		if($oController) $oController->procRefererExecute((int)($addon_info->delete_olddata));
	}
/* End of file referer.addon.php */
/* Location: ./addons/referer/referer.addon.php */
