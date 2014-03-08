<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 */
class DbHandler {
	private $conn;
	function __construct() {
		require_once 'classes/DbVerbindung.php';
		$db = new DbVerbindung ();
		$this->conn = $db->connect ();
	}
	public function dbTest() {
		$query = $this->conn->prepare ( "SELECT 1 from DUAL" );
		if ($query->execute ()) {
			$result = array ();
			$result = $query->fetchAll ( PDO::FETCH_OBJ );
			return $result;
		} else {
			return NULL;
		}
	}
	public function getSMI() {
		try {
			$query = $this->conn->prepare ( "select * FROM smi ORDER BY smi_TradeDate" );
			if ($query->execute ()) {
				$result = array ();
				$result = $query->fetchAll ( PDO::FETCH_OBJ );
				return $result;
			} else {
				return NULL;
			}
		} catch ( PDOException $e ) {
			echo '{"error":{"text":' . $e->getMessage () . '}}';
		}
	}
	function addHistoricalPrices() {
		
		/**
		 * $request = \Slim\Slim::getInstance ()->request ();
		 * $hisPri = $request->getBody ();
		 *
		 * echo $hisPri->code;
		 * echo $hisPri->startyear;
		 * echo $hisPri->startmonth;
		 * echo $hisPri->startday;
		 *
		 * $code = $hisPri->code;
		 * $fromYear = $hisPri->startyear;
		 * $fromMonth = $hisPri->startmonth;
		 * $fromDay = $hisPri->startday;
		 * $toYear = $hisPri->endyear;
		 * $toMonth = $hisPri->endmonth;
		 * $toDay = $hisPri->endday;
		 *
		 * echo "url=http://ichart.finance.yahoo.com/table.csv?s=" . $code . "&d=" . $toMonth . "&e=" . $toDay . "&f=" . $toYear . "&g=d&a=" . $fromMonth . "&b=" . $fromDay . "&c=" . $fromYear . "&ignore=.csv";
		 */
		$data_array = array ();
		
		$url = "http://ichart.finance.yahoo.com/table.csv?s=%5EIXIC&d=1&e=19&f=2014&g=d&a=1&b=5&c=1971&ignore=.csv";
		
		$row = 1;
		
		if (($handle = fopen ( $url, "r" )) !== FALSE) {
			while ( ($data = fgetcsv ( $handle, 1000, "," )) !== FALSE ) {
				$num = count ( $data );
				// echo "<p> $num Felder in Zeile $row: <br /></p>\n";
				
				for($c = 0; $c < $num; $c ++) {
					// echo $data [$c] . "<br />\n";
					$data_array [$row] ['SMI_TradeDate'] = $data [0];
					$data_array [$row] ['SMI_Open'] = $data [1];
					$data_array [$row] ['SMI_High'] = $data [2];
				}
				$row ++;
			}
			fclose ( $handle );
		}
		array_shift ( $data_array );
		echo "<pre>";
		print_r ( $data_array );
		echo "</pre>";
		$app->render ( 200, $data_array );
		
		/*
		 * SMI_TradeDate				Date NOT NULL, SMI_Open				Double NOT NULL, SMI_High				Double NOT NULL, SMI_Low				Double NOT NULL, SMI_Close				Double NOT NULL, SMI_Volume				Double NOT NULL, SMI_AdjClose				Double NOT NULL,
		 */
	}
}

?>