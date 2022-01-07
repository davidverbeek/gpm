<?php

/*
 * Feed generator class
 * Made by: Jaagers BV
 * Date: 20-11-2012
 */

class feed {
	
	/* Feed vars */
	
	public $feedname;
	public $storeid;
	public $nrofproducts;
	public $allowedfeeds;
	public $ttl;
	public $sqlparams;
	
	/* Database vars */
	
	private $pdo;
	public $host; 
	public $dbname;
	public $username;
	public $password;
	public $tableprefix;
	
	public function feed($feedname, $nrofproducts, $storeid, $sqlparams = null, $allowedfeeds = null, $ttl = 82800, $host, $dbname, $username, $password, $tableprefix = 'mage_', $force = false, $type = 'xml') {
		
		$this->feedname = $feedname;
		$this->storeid = $storeid;
		$this->nrofproducts = $nrofproducts;
		$this->allowedfeeds = $allowedfeeds;
		$this->ttl = $ttl;
		$this->params = $sqlparams;
				
		if($type == 'xml') {
			$this->feedfile = $this->getAbsoluteBasePath() . '/feed_' . $feedname . '.xml';
		} else if($type == 'csv') {
			$this->feedfile = $this->getAbsoluteBasePath() . '/feed_' . $feedname . '.csv';
		}
		
		$this->force = $force;
		$this->trackingurl = null;
		
		/* DB */
		
		$this->host = $host;
		$this->dbname = $dbname;
		$this->username = $username;
		$this->password = $password;
		$this->tableprefix = $tableprefix;
		
		$this->pdo = $this->connectDb();
		
	}
	
	/*
	 * Build the feed
	 */
	
	public function buildFeed() {
		
		$_products = $this->getProducts();
		
		$templatefile 	= $this->getAbsoluteBasePath() . '/feedtemplate/' . $this->feedname . '.php';
			
		if(file_exists($templatefile)) {
			
			ob_start();
			
			include $templatefile;
			
			$data = ob_get_clean();
			
			try {
				file_put_contents($this->feedfile, $data);
				echo $this->feedname . ' : Feed generation done. <br />';
			} catch (Exception $e) {
				var_dump($e); exit;
			}
			
		} else {
			echo 'No template for selected feed';exit;
		}
		
	}
	
	/*
	 * Init database connection (this could be done via external database class)
	*/
	
	private function connectDb() {
	
		$pdo = new PDO(
				'mysql:host=' . $this->host . ';dbname=' . $this->dbname ,
				$this->username,
				$this->password,
				array(
						PDO::MYSQL_ATTR_INIT_COMMAND => 	"SET NAMES utf8",
						PDO::ATTR_ERRMODE =>				PDO::ERRMODE_WARNING,
						PDO::ATTR_CASE =>					PDO::CASE_LOWER
				)
		);
	
		return $pdo;
	
	}
	
	/*
	 * Check stockstatus for specific product
	 */
		
	public function checkStock($qty, $format = null) {
		
		$qty = (int)$qty;
		
		if($qty <= 0) {
			
			if(isset($format)) {
				return $format[0];
			} else {
				return false;
			}
			
		} else {
			
			if(isset($format)) {
				return $format[1];
			} else {
				return true;
			}
			
		}
		
	}
	
	/*
	 * Build specified productfeed
	 */
	
	private function getProducts() {
		
		if($this->checkAllowedFeeds()) {
			
			if(!$this->checkCache()) {
				
				/*$sql = 	"SELECT
				*
				FROM
				" . $this->tableprefix . "customsearchindex WHERE eancode > 0";*/
				
				$sql = 	"SELECT
				*
				FROM
				" . $this->tableprefix . "customsearchindex";
					
				if($this->nrofproducts) {
					$sql.= ' LIMIT 0, ' . $this->nrofproducts;
				}
					
				$products = $this->runQuery($sql);
				
				//echo count($products);exit;
				
				return $products;
				
			} else {
				
				echo 'Valid cachefile found'; exit;
				
			}
			
		} else {
			
			die('Invalid feedname');
			
		}
		
	}
	
	/*
	 * Run database query
	 */
	
	private function runQuery($sql, $mode = PDO::FETCH_ASSOC) {

		try {
			$sth = $this->pdo->prepare($sql);
			$sth->execute();
			$result = $sth->fetchAll($mode);
		} catch(Exception $e) {
			var_dump($e);exit;
		}
		
		return $result;
	}
	
	/*
	 * Check if current feed is within allowed feeds
	 */
	
	private function checkAllowedFeeds() {
		
		/*if(isset($this->allowedfeeds) && is_array($this->allowedfeeds)) {
			
			if(in_array($this->feedname, $this->allowedfeeds)) {
				return true;
			} else {
				return false;
			}
			
		} else {
			return true;
		}*/
		
		return true;
		
	}
	
	/*
	 * Check current cache lifetime for generated feedfile
	 * (Standard is 82800 seconds, or 1 day)
	 */
	
	private function checkCache() {
		
		if($this->force) {
			return false;
		}
		
		$filetime = is_file($this->feedfile) ? filemtime($this->feedfile) : 0;
		
		if((time() - $this->ttl) < $filetime){
				
			return true;
		
		}
		
		return false;
		
	}
	
	/*
	 * Set custom tracking url for deeplink
	 */

	public function setTrackingUrl($url) {

		$this->trackingurl = $url;
		
	}
	
	/*
	 * Get custom tracking url for deeplink (replaces the mask with the supplied URL)
	 */
	
	public function getTrackingUrl($url) {
		
		$pattern 	= '/[\*]{4}/';
		$replace 	= $url;
		$string 	= $this->trackingurl;
		
		$trackingurl = preg_replace($pattern, $replace, $string);
		
		return $trackingurl;
	
	}
	
	
	/*
	 * Fetch current Url Basepath
	 */
	
	private function getUrlBasePath() {
	
		$basepath = 'http://' . $_SERVER['HTTP_HOST'];
	
		return $basepath;
	
	}
	
	/*
	 * Fetch current Absolute Basepath
	 */
	
	private function getAbsoluteBasePath()	{
	
		$absolutepath = getcwd();
	
		return $absolutepath;
	
	}
	
}
