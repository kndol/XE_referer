/* stat 삭제 후 */
function completeDeleteStat(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    var called_from = ret_obj['called_from'];
	var url;
    
    alert(message);

	if (called_from == 'referer') {
    	url = current_url.setQuery('act','dispRefererAdminRanking').setQuery('module', 'admin').setQuery('host','');
    }
	else if (called_from == 'remote') {
    	url = current_url.setQuery('act','dispRefererAdminRemoteRanking').setQuery('module', 'admin').setQuery('remote','');
    }
	else if (called_from == 'uagent') {
    	url = current_url.setQuery('act','dispRefererAdminUAgentRanking').setQuery('module', 'admin').setQuery('uagent','');
    }
	else {
    	url = current_url.setQuery('act','dispRefererAdminIndex').setQuery('module', 'admin').setQuery('page','').setQuery('host','').setQuery('remote','').setQuery('uagent','');
    }
    location.href = url;
}
