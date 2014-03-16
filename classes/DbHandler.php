<?php
class DbHandler {
	private $conn;
	private $app;
	function __construct($app) {
		$this->app = $app;
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
			$query = $this->conn->prepare ( "SELECT * FROM indexe ORDER BY ID ASC" );
			if ($query->execute () && $query->rowCount () > 0) {
				$result = array ();
				$result = $query->fetchAll ( PDO::FETCH_OBJ );
				$this->app->render ( 200, $result );
			} else {
				throw new PDOException ( 'NO DATA FOUND' );
			}
		} catch ( PDOException $e ) {
			$message = (DEBUG == true) ? $e->getMessage () : "Keine Indexe gefunden";
			$this->app->render ( 404, array (
					"message" => $message,
					"error" => true 
			) );
			$this->app->stop ();
		}
	}
	public function pruefeIndex($indexID = NULL) {
		try {
			$query = $this->conn->prepare ( "SELECT ID FROM indexe WHERE ID=" . $indexID );
			if ($query->execute () && $query->rowCount () > 0) {
				return intval ( $query->fetchColumn () );
			} else {
				throw new PDOException ( 'NO DATA FOUND' );
			}
		} catch ( PDOException $e ) {
			$message = (DEBUG == true) ? $e->getMessage () : "Keine Indexe gefunden";
			$this->app->render ( 404, array (
					"message" => $message,
					"error" => true 
			) );
			$this->app->stop ();
		}
	}
	public function leseIndexWerte($indexID, $startDatum, $endDatum) {
		try {
			$query = $this->conn->prepare ( "SELECT tradeDate, adjClose FROM indexe_values WHERE FK_indexe_ID=" . $indexID . " AND tradeDate >='" . $startDatum . "' AND tradeDate<='" . $endDatum . "'" );
			if ($query->execute () && $query->rowCount () > 0) {
				$result = array ();
				$result = $query->fetchAll ( PDO::FETCH_OBJ );
				return $result;
			} else {
				throw new PDOException ( 'NO DATA FOUND' );
			}
		} catch ( PDOException $e ) {
			$message = (DEBUG == true) ? $e->getMessage () : "Keine Indexe gefunden";
			$this->app->render ( 404, array (
					"message" => $message,
					"error" => true 
			) );
			$this->app->stop ();
		}
	}
	public function aktualisiereAlleIndexe($data_array) {
		$this->schreibeIndexe ( $data_array );
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
		
		echo $letztesDatum . " und heute ist d=" . date ( "d" ) . " und e=" . date ( "m" ) . " und f=" . date ( "Y" );
		exit ();
		
		$data_array = array ();
		
		// ACHTUNG MONTH -1 rechnen
		
		$url = "http://ichart.finance.yahoo.com/table.csv?s=%5EIXIC&d=1&e=19&f=2014&g=d&a=1&b=5&c=1971&ignore=.csv";
		
		$row = 1;
		
		if (($handle = fopen ( $url, "r" )) !== FALSE) {
			while ( ($data = fgetcsv ( $handle, 1000, "," )) !== FALSE ) {
				$num = count ( $data );	
				for($c = 0; $c < $num; $c ++) {
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
		$this->schreibeIndexe ( $data_array );
	}
	private function schreibeIndexe($data_array) {
		if (empty ( $data_array )) {
			echo '{"error":{"text":"Array ist leer!"}}';
			exit ();
		}
		$this->loescheIndexe ();
		try {
			$this->conn->beginTransaction ();
			$query = $this->conn->prepare ( "INSERT INTO indexe_values (tradeDate, adjClose, FK_indexe_ID) VALUES (?, ?, 2)" );
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