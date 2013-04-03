/* stat 삭제 후 */
function completeDeleteStat(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    alert(message);

    var url = current_url.setQuery('act','dispRefererAdminIndex').setQuery('host','');
    location.href = url;
}
