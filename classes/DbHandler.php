<?php
class DbHandler {
	private $conn;
	function __construct() {
		require_once 'classes/DbVerbindung.php';
		$db = new DbVerbindung ();
		$this->conn = $db->connect ();
	}
	public function dbTest() {
		$query = $this->conn->prepare ( "SELECT 1 FROM DUAL" );
		if ($query->execute ()) {
			$result = array ();
			$result = $query->fetchAll ( PDO::FETCH_OBJ );
			return $result;
		} else {
			return NULL;
		}
	}
	public function leseIndexe() {
		try {
			$query = $this->conn->prepare ( "SELECT * FROM smi ORDER BY smi_TradeDate" );
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
	function aktualisiereAlleIndexe() {
		
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
					$data_array [$row] ['tradedate'] = $data [0];
					$data_array [$row] ['open'] = $data [1];
					$data_array [$row] ['high'] = $data [2];
					$data_array [$row] ['low'] = $data [3];
					$data_array [$row] ['close'] = $data [4];
					$data_array [$row] ['volume'] = $data [5];
					$data_array [$row] ['adjclose'] = $data [6];
				}
				$row ++;
			}
			fclose ( $handle );
		}
		array_shift ( $data_array );
		// echo "<pre>";
		// print_r ( $data_array );
		// echo "</pre>";
		// $app->render ( 200, $data_array );
		// $data_array = "";
		
		$this->schreibeIndexe ( $data_array );
		/*
		 * SMI_TradeDate				Date NOT NULL, SMI_Open				Double NOT NULL, SMI_High				Double NOT NULL, SMI_Low				Double NOT NULL, SMI_Close				Double NOT NULL, SMI_Volume				Double NOT NULL, SMI_AdjClose				Double NOT NULL,
		 */
	}
	function aktualisiereDeltaIndexe() {
		$letztesDatum = "";
		
		try {
			$query = $this->conn->prepare ( "SELECT * FROM smi ORDER BY smi_TradeDate DESC LIMIT 0,1" );
			$query->execute ();
			$letztesDatum = $query->fetchColumn ();
		} catch ( PDOException $e ) {
			echo '{"error":{"text":' . $e->getMessage () . '}}';
		}
		
		echo $letztesDatum." und heute ist d=".date("d")." und e=".date("m")." und f=".date("Y");
		exit ();
		
		$data_array = array ();
		
		// ACHTUNG MONTH -1 rechnen
		
		$url = "http://ichart.finance.yahoo.com/table.csv?s=%5EIXIC&d=1&e=19&f=2014&g=d&a=1&b=5&c=1971&ignore=.csv";
		
		$row = 1;
		
		if (($handle = fopen ( $url, "r" )) !== FALSE) {
			while ( ($data = fgetcsv ( $handle, 1000, "," )) !== FALSE ) {
				$num = count ( $data );
				// echo "<p> $num Felder in Zeile $row: <br /></p>\n";
				
				for($c = 0; $c < $num; $c ++) {
					// echo $data [$c] . "<br />\n";
					$data_array [$row] ['tradedate'] = $data [0];
					$data_array [$row] ['open'] = $data [1];
					$data_array [$row] ['high'] = $data [2];
					$data_array [$row] ['low'] = $data [3];
					$data_array [$row] ['close'] = $data [4];
					$data_array [$row] ['volume'] = $data [5];
					$data_array [$row] ['adjclose'] = $data [6];
				}
				$row ++;
			}
			fclose ( $handle );
		}
		array_shift ( $data_array );
		// echo "<pre>";
		// print_r ( $data_array );
		// echo "</pre>";
		// $app->render ( 200, $data_array );
		// $data_array = "";
		
		$this->schreibeIndexe ( $data_array );
		/*
		 * SMI_TradeDate				Date NOT NULL, SMI_Open				Double NOT NULL, SMI_High				Double NOT NULL, SMI_Low				Double NOT NULL, SMI_Close				Double NOT NULL, SMI_Volume				Double NOT NULL, SMI_AdjClose				Double NOT NULL,
		 */
	}
	private function schreibeIndexe($data_array) {
		/**
		 * $data_array = array ();
		 * $data_array [0] = array (
		 * 'tradedate' => '2014-01-08',
		 * 'adjclose' => 199
		 * );
		 * $data_array [1] = array (
		 * 'tradedate' => '2014-02-03',
		 * 'adjclose' => 163
		 * );
		 * $data_array [2] = array (
		 * 'tradedate' => '2014-02-04',
		 * 'adjclose' => 143
		 * );
		 * $data_array [3] = array (
		 * 'tradedate' => '2014-02-08',
		 * 'adjclose' => 183
		 * );
		 */
		if (empty ( $data_array )) {
			echo '{"error":{"text":"Array ist leer!"}}';
			exit ();
		}
		
		$this->loescheIndexe ();
		
		try {
			$this->conn->beginTransaction ();
			$query = $this->conn->prepare ( "INSERT INTO smi (smi_TradeDate, smi_AdjClose) VALUES (?, ?)" );
			foreach ( $data_array as $key ) {
				// $query->bindParam ( "tradedate", $key ['tradedate'] );
				// $query->bindParam ( "adjclose", $key ['adjclose'] );
				$query->execute ( array (
						$key ['tradedate'],
						$key ['adjclose'] 
				) );
			}
			$this->conn->commit ();
		} catch ( PDOException $e ) {
			echo '{"error":{"text":' . $e->getMessage () . '}}';
			$this->conn->rollBack ();
		}
		echo "DB INSERT ERFOLGREICH!";
	}
	private function loescheIndexe() {
		try {
			$this->conn->beginTransaction ();
			$query = $this->conn->prepare ( "TRUNCATE TABLE smi" );
			$query->execute ();
			$this->conn->commit ();
		} catch ( PDOException $e ) {
			echo '{"error":{"text":' . $e->getMessage () . '}}';
			$this->conn->rollBack ();
		}
	}
}

?>