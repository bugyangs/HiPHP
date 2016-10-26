<?php
/**
 * FileName: index.php
 * Author: zyf
 * Date: 16/9/19 下午12:21
 * Brief:
 */
error_reporting(E_ALL & ~E_NOTICE);
require_once __DIR__ . "/../../system/core/Bootstrap.php";
Core_Bootstrap::instance("test")->run();

