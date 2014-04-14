<?php
class YahooDaten {
	private $dbh;
	private $indexe = array ();
	function __construct($dbh) {
		$this->dbh = $dbh;
	}
	public function leseDatenstand() {
		$result = $this->dbh->leseIndexe ();
		$this->app->render ( 200, $result );
	}
	public function aktualisiereIndexe($type) {
		$letztesDatum = "";
		if ($type == "delta") {
			$updatedIndexes = "";
			try {
				$this->indexe = $this->dbh->leseIndexe ();
				
				for($i = 0; $i < count ( $this->indexe ); $i ++) {
					$lastdate = $this->dbh->leseLetztesDatum ( $this->indexe [$i]->id );
					if ($lastdate != 0) {
						$ld = explode ( "-", $lastdate );
						$url = "http://ichart.finance.yahoo.com/table.csv?s=" . $this->indexe [$i]->kuerzel . "&a=" . ($ld [1] - 1) . "&b=" . ($ld [2] + 1) . "&c=" . $ld [0] . "&d=" . (date ( "m" ) - 1) . "&e=" . (date ( "d" ) - 1) . "&f=" . date ( "Y" ) . "&g=d&ignore=.csv";
						$row = 1;
						$data_array = array ();
						if (($handle = fopen ( $url, "r" )) !== FALSE) {
							while ( ($data = fgetcsv ( $handle, 1000, "," )) !== FALSE ) {
								$num = count ( $data );
								for($c = 0; $c < $num; $c ++) {
									$data_array [$row] ['tradedate'] = $data [0];
									$data_array [$row] ['adjclose'] = $data [6];
								}
								$row ++;
							}
							fclose ( $handle );
						}
						if (count($data_array) <= 1) {
							continue;
						}
						array_shift ( $data_array );
						$this->dbh->aktualisiereIndexe ( $data_array, $this->indexe [$i]->id, "delta" );
						$updatedIndexes .= $this->indexe [$i]->name . ", ";						
					}
				}
				if ($updatedIndexes != "") {
					$msg = "Daten von Index(e) " . substr ( $updatedIndexes, 0, - 2 ) . " wurden aktualisiert";
					echo ($this->dbh->schreibeLog ( $msg, 1, 200 ) == "") ? $msg : "";
				} else {
					echo "Keine Indexe wurden aktualisiert.";
				}
			} catch ( PDOException $e ) {
				$message = (DEBUG == true) ? $e->getMessage () : "DB Update fehlgeschlagen!";
				$this->app->render ( 404, array (
						"message" => $message,
						"error" => true 
				) );
				$this->app->stop ();
			}
		}
	}
}

?>