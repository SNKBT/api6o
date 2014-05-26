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
			$query = $this->conn->prepare ( "SELECT * FROM indexe ORDER BY id ASC" );
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
	public function schreibeLog($nachricht, $typ, $httpCode) {
		if (empty ( $nachricht ) || ! is_numeric ( $typ ) || ! is_numeric ( $httpCode )) {
			echo '{"error":{"text":"Log kann nicht geschrieben werden!"}}';
			exit ();
		}
		try {
			$this->conn->beginTransaction ();
			$query = $this->conn->prepare ( "INSERT INTO log (nachricht, typ, httpCode, zeitstempel) VALUES ('" . $nachricht . "', " . $typ . ", " . $httpCode . ", NOW())" );
			$query->execute ();
			$this->conn->commit ();
		} catch ( PDOException $e ) {
			echo '{"error":{"text":' . $e->getMessage () . '}}';
			$this->conn->rollBack ();
			exit ();
		}
	}
	public function leseDatenstand() {
		try {
			$query = $this->conn->prepare ( "SELECT nachricht, zeitstempel, httpCode FROM log WHERE typ=1 ORDER BY id DESC LIMIT 1" );
			if ($query->execute () && $query->rowCount () > 0) {
				$result = array ();
				$result ['leseDatenstand'] = $query->fetchAll ( PDO::FETCH_OBJ );
				$this->app->render ( $result ['leseDatenstand'] [0]->httpCode, $result );
			} else {
				throw new PDOException ( 'NO DATA FOUND' );
			}
		} catch ( PDOException $e ) {
			$message = (DEBUG == true) ? $e->getMessage () : "Keine Infos ueber den Datenstand vorhanden";
			$this->app->render ( 404, array (
					"message" => $message,
					"error" => true 
			) );
			$this->app->stop ();
		}
	}
	public function pruefeIndex($indexID = NULL) {
		try {
			$query = $this->conn->prepare ( "SELECT id FROM indexe WHERE id=" . $indexID );
			if ($query->execute () && $query->rowCount () > 0) {
				return intval ( $query->fetchColumn () );
			} else {
				throw new PDOException ( 'NO DATA FOUND' );
			}
		} catch ( PDOException $e ) {
			$message = (DEBUG == true) ? $e->getMessage () : "Index nicht gefunden";
			$this->app->render ( 404, array (
					"message" => $message,
					"error" => true 
			) );
			$this->app->stop ();
		}
	}
	public function leseIndexWerte($indexID, $startDatum, $endDatum) {
		try {
			$query = $this->conn->prepare ( "SELECT tradeDate, adjClose FROM indexe_values WHERE fk_indexe_id=" . $indexID . " AND tradeDate >='" . $startDatum . "' AND tradeDate<='" . $endDatum . "'  ORDER BY tradeDate DESC" );
			if ($query->execute () && $query->rowCount () > 0) {
				$result = array ();
				$result = $query->fetchAll ( PDO::FETCH_OBJ );
				return $result;
			} else {
				throw new PDOException ( 'NO DATA FOUND' );
			}
		} catch ( PDOException $e ) {
			$message = (DEBUG == true) ? $e->getMessage () : "Keine Indexwerte in diesem Zeitraum gefunden";
			$this->app->render ( 404, array (
					"message" => $message,
					"error" => true 
			) );
			$this->app->stop ();
		}
	}
	public function aktualisiereIndexe($data_array, $id, $type) {
		$this->schreibeIndexe ( $data_array, $id, $type );
	}
	public function leseLetztesDatum($id) {
		try {
			$query = $this->conn->prepare ( "SELECT tradeDate FROM indexe_values WHERE fk_indexe_id=" . $id . " ORDER BY tradeDate DESC LIMIT 0,1" );
			if ($query->execute () && $query->rowCount () > 0) {
				$letztesDatum = $query->fetchColumn ();
				
				$n = date ( "Y" ) . "-" . date ( "m" ) . "-" . (date ( "d" ) - 1);
				if (date ( "j", (strtotime ( $n ) - strtotime ( $letztesDatum )) ) < 7) {
					if (date ( 'N', strtotime ( $letztesDatum ) ) >= 5 || date ( 'N', strtotime ( $letztesDatum ) ) == 1) {
						return 0;
					} elseif (date ( 'G' ) < 8 && $letztesDatum == date ( "Y" ) . "-" . date ( "m" ) . "-" . (date ( "d" ) - 1)) {
						return 0;
					}
				}
				return $letztesDatum;
			} else {
				return 0;
			}
		} catch ( PDOException $e ) {
			echo '{"error":{"text":' . $e->getMessage () . '}}';
		}
		return $letztesDatum;
	}
	private function schreibeIndexe($data_array, $id, $type) {
		if (empty ( $data_array ) || ! is_numeric ( $id )) {
			echo '{"error":{"text":"Array ist leer!"}}';
			exit ();
		}
		if ($type == "full")
			$this->loescheIndexe ( $id );
		try {
			$this->conn->beginTransaction ();
			$query = $this->conn->prepare ( "INSERT INTO indexe_values (tradeDate, adjClose,fk_indexe_id) VALUES (?, ?, " . $id . ")" );
			foreach ( $data_array as $key ) {
				$query->execute ( array (
						$key ['tradedate'],
						$key ['adjclose'] 
				) );
			}
			$this->conn->commit ();
		} catch ( PDOException $e ) {
			$message = (DEBUG == true) ? $e->getMessage () : "Es konnten keine Index Werte eingetragen werden.";
			$this->conn->rollBack ();
			$this->app->render ( 404, array (
					"message" => $message,
					"error" => true 
			) );
			$this->app->stop ();
		}
	}
	private function loescheIndexe($id) {
		try {
			$this->conn->beginTransaction ();
			$query = $this->conn->prepare ( "DELETE FROM TABLE indexe_values WHERE fk_indexe_id=" . $id );
			$query->execute ();
			$this->conn->commit ();
		} catch ( PDOException $e ) {
			echo '{"error":{"text":' . $e->getMessage () . '}}';
			$this->conn->rollBack ();
		}
	}
}

?>