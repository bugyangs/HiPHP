<?php
class Core_Log_File
{
	const NORMAL = 'normal';
	const WARNING = 'warning';
	const NUM_DIV_MEM = 5;
	const WRITER_BUF = 'buf';
	const WRITER_NOBUF = 'nobuf';
	
	private $_arrLog = array(
		self::NORMAL => '',
		self::WARNING => '',
	);
	
	private $_arrLogFile = array(
		self::NORMAL => '',
		self::WARNING => '',
	);
	
	private $_intMemLimit = 0 ;		// 0 = not limit
	private $_strWriterHandler = self::WRITER_BUF;	// default write with buf
	
	public function __construct($strFilePath , $strWriterHandler = self::WRITER_BUF )
	{
		$strFilePath = strval($strFilePath);
		$strWriterHandler = strval($strWriterHandler);
		
		// mkdir
		$strDir = dirname($strFilePath);
		$bolIsDir = false ;
		
		if( function_exists('bd_lazy_is_dir') ){
			$bolIsDir = bd_lazy_is_dir($strDir);
		}
		else{
			$bolIsDir = is_dir($strDir);
		}
		
		if( ! $bolIsDir ){
			@mkdir($strDir, 0755, true);
		}
		
		// set memory limit , default not limit
		$strMemLimit = ini_get('memory_limit');
		if( 'M' == substr($strMemLimit, -1)){
			$this->_intMemLimit = intval( intval( substr($strMemLimit, 0 , strlen($strMemLimit) -1) * 1024 * 1024 ) / self::NUM_DIV_MEM );
		}
		else{
			$this->_intMemLimit = intval( intval($strMemLimit) / self::NUM_DIV_MEM );
		}
		
		// set writer handler
		$this->_strWriterHandler = $strWriterHandler ;
		
		// set log file name
		$this->_arrLogFile[self::NORMAL] = $strFilePath ;
		$this->_arrLogFile[self::WARNING] = $strFilePath . '.wf' ;
		$this->_arrLog[self::NORMAL] = '';
		$this->_arrLog[self::WARNING] = '';
	}
	
	public function __destruct()
	{
		$this->flush();
	}
	
	/**
	 * @desc set option
	 * @param array $arrConf
	 */
	public function setOptions( $arrConf )
	{
		// kv conf
		if( isset($arrConf['arrLogFile'])
			&& is_array($arrConf['arrLogFile'])
		){
			foreach ($arrConf['arrLogFile'] as $strLevel => $strFile)
			{
				$strLevel = strval($strLevel);
				$strFile = strval($strFile);
				$this->_arrLogFile[$strLevel] = $strFile ;
				if(isset($this->_arrLog[$strLevel]))
				{
					$this->_arrLog[$strLevel] .= '';
				}
				else
				{
					$this->_arrLog[$strLevel] = '';
				}
			}
		}
		
		if( isset($arrConf['strWriterHandler']) )
		{
			$this->_strWriterHandler = $arrConf['strWriterHandler'] ;
		}
	}
	
	/**
	 * @desc check if out of memory
	 * @return boolean
	 */
	protected function isOOM()
	{
		$intLogSize = 0;
		
		foreach ($this->_arrLog as $strLevel => $strLog ){
			$intLogSize += strlen($strLog);
		}
		
		if( 0 == $this->_intMemLimit
			|| $intLogSize < intval( $this->_intMemLimit )
		){
			return false;
		}
		
		return true ;
	}
	
	/**
	 * @desc clean log
	 */
	public function clean()
	{
		foreach ($this->_arrLog as $strLevel => $strLog ){
			$this->_arrLog[$strLevel] = '';
		}
	}
	
	/**
	 * @desc flush log
	 */
	public function flush()
	{	
		foreach ($this->_arrLog as $strLevel => $strLog ){
			if( ! empty($strLog) ){
				if( function_exists('bd_write_log')){
					bd_write_log($this->_arrLogFile[$strLevel], $strLog);
				}
				else{
					file_put_contents($this->_arrLogFile[$strLevel], $strLog , FILE_APPEND);
				}
			}
		}
		
		$this->clean();
	}
	
	/**
	 * @desc write log
	 * @param string $strLog
	 * @param string $strLevel
	 * @return boolean|number
	 */
	public function log($strLog , $strLevel = self::NORMAL )
	{
		$strLog = strval($strLog);
		$strLevel = strval($strLevel);
		
		if( $this->isOOM() ){
			$this->flush();
		}
		
		if( ! isset($this->_arrLogFile[$strLevel])){
			return false ;
		}
		
		if( self::WRITER_BUF == $this->_strWriterHandler ){
			$this->_arrLog[$strLevel] .= strval($strLog) ;
			return true ;
		}
		else{
			if( function_exists('bd_write_log')){
				return bd_write_log( $this->_arrLogFile[$strLevel] , $strLog );
			}
			else{
				return file_put_contents( $this->_arrLogFile[$strLevel] , $strLog , FILE_APPEND );
			}
		}
	}
	

}