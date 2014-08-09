<?php
    if(!defined("__ZBXE__")) exit();
	if(!defined("__XE__")) exit();

    /**
     * @file referer.addon.php 
     * @author haneul (haneul0318@gmail.com)
	 * @editer Wincomi (wincomi@wincomi.com)
	 * @enhanced by KnDol (kndol@kndol.net)
     **/

	// called_position가 before_module_init 이고 module이 admin이 아닐 경우
	if($called_position == 'before_module_init' && Context::get('module') != 'admin' && !$GLOBALS['__referer_addon_called__']
		&& ($addon_info->include_direct_access == "yes" || !empty($_SERVER["HTTP_REFERER"])))
	{
		$GLOBALS['__referer_addon_called__'] = true;

		if($logged_info->is_admin == "Y" && $addon_info->admin != "yes") return;

		$exclude_uagent = trim($addon_info->exclude_uagent);
		$exclude_host   = trim($addon_info->exclude_host);
		$referer        = parse_url($_SERVER["HTTP_REFERER"]);
		$uagent         = $_SERVER["HTTP_USER_AGENT"];
		if ($addon_info->exclude_bot == 'yes' && !strstr($uagent, 'SocialXE ClientBot')
			&& (preg_match("/bot|spider|crawler|slurp|yeti|daumoa|FeedDemon|Aboundex|NewsGatorOnline|DigExt|inboundscore|feedfetcher|Teoma|siteexplorer|recruitingpoint|NetcraftSurveyAgent/i", $uagent)
				|| strstr($uagent, 'MSIE or Firefox mutant; not on Windows server;')
				|| strstr($uagent, 'Google favicon'))) return;
		if ($exclude_uagent && preg_match("/$exclude_uagent/i", $uagent)) return;
		if ($exclude_host   && ((!empty($_SERVER["HTTP_REFERER"]) && preg_match("/$exclude_host/i", $referer['host'])) || preg_match("/$exclude_host/i", $_SERVER["REMOTE_ADDR"]))) return;
		
		$oController = &getController('referer');
		if ($oController) $oController->procRefererExecute((int)($addon_info->delete_olddata));
	}
/* End of file referer.addon.php */
/* Location: ./addons/referer/referer.addon.php */
