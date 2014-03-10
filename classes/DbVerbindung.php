<?php
class DbVerbindung {
	private $conn;
	function __construct() {
	}
	function connect() {
		include_once 'includes/config.php';
		
		try {
			$this->conn = new PDO ( "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD );
			$this->conn->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		} catch ( PDOException $e ) {
			echo '{"error":{"text":' . $e->getMessage () . '}}';
			die ();
		}
		
		return $this->conn;
	}
	
	function error($e) {
		echo '{""error":false,"status":200,"message":"' . $e . '"}';
	}
}

?>