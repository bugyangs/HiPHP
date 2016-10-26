<?php
# 日志级别 level
#  1：打印FATAL
#  2：打印FATAL和WARNING
#  4：打印FATAL、WARNING、NOTICE（线上程序正常运行时的配置）
#  8：打印FATAL、WARNING、NOTICE、TRACE（线上程序异常时使用该配置）
# 16：打印FATAL、WARNING、NOTICE、TRACE、DEBUG（测试环境配置）

# auto_rotate
# 是否按小时自动分日志，设置为1时，日志被打在some-app.log.2011010101

# use_sub_dir
# 日志文件路径是否增加一个基于app名称的子目录，例如：log/some-app/some-app.log
# 该配置对于unknown-app同样生效
$config = array(
    "level" => 16,
    "auto_rotate" => 1,
    "use_sub_dir" => 1,
    "format" => "%L: %t [%f:%N] errno[%E] logId[%l] uri[%U] refer[%{referer}i] cookie[%{cookie}i] %S %M",
    "log_path" => "/Users/baidu/mysite/php/HiPHP/log",
);

