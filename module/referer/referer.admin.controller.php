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
		    $output = executeQuery('referer.deleteRefererStat', $args);
            if(!$output->toBool()) return $output;

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

			$oDB->commit();

	        $this->setMessage('success_reset');
		}
    }
?>
