<?php 
/*
Used for app test
Original format string: %L: %t [%f:%N] errno[%E] logId[%l] %M
*/
function _core_log_a608f41c42dcfdada3fe8f22edad8de1() {

return '' . Core_Log::$current_instance->current_log_level . ': ' . strftime('%y-%m-%d %H:%M:%S') . ' [' . Core_Log::$current_instance->current_file . ':' . Core_Log::$current_instance->current_line . '] errno[' . Core_Log::$current_instance->current_err_no . '] logId[' . Core_Log::genLogID() . '] ' . Core_Log::$current_instance->current_err_msg . '' . "\n";
}