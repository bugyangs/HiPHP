<?php 
/*
Used for app test
Original format string: %L: %t [%f:%N] errno[%E] logId[%l] module[%{app}x] uri[%U] refer[%{referer}i] cookie[%{cookie}i] %S %M
*/
function _core_log_fb9e9e9282d2cd020c3bedb342859d15() {

return '' . Core_Log::$current_instance->current_log_level . ': ' . strftime('%y-%m-%d %H:%M:%S') . ' [' . Core_Log::$current_instance->current_file . ':' . Core_Log::$current_instance->current_line . '] errno[' . Core_Log::$current_instance->current_err_no . '] logId[' . Core_Log::genLogID() . '] module[' . Core_Log::getLogPrefix() . '] uri[' . (isset($_SERVER['REQUEST_URI'])? $_SERVER['REQUEST_URI'] : '') . '] refer[' . (isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : '') . '] cookie[' . (isset($_SERVER['HTTP_COOKIE'])? $_SERVER['HTTP_COOKIE'] : '') . '] ' . '' . ' ' . Core_Log::$current_instance->current_err_msg . '' . "\n";
}