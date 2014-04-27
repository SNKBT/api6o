<?php
/**
 * @SWG\Resource(
 *  apiVersion="0.1",
 *  basePath="http://api.localhost",
 *  description="API Datenbasis aus Yahoo Finance Daten"
 * )
 */
class YahooDaten {
	private $app;
	private $dbh;
	private $indexe = array ();
	function __construct($dbh, $app) {
		$this->app = $app;
		$this->dbh = $dbh;
	}
	/**
	 * @SWG\Api(
	 * path="/leseDatenstand",
	 * @SWG\Operation(
	 * summary="Gibt den letzten Log Eintrag zum aktuellen Datenstand zurueck.",
	 * method="GET",
	 * type="Index",
	 * @SWG\ResponseMessage(
	 * message="Keine Infos ueber den Datenstand vorhanden",
	 * code=404
	 * )
	 * )
	 * )
	 * @SWG\Model(id="Index")
	 * @SWG\Property(name="leseDatenstand",type="array",required=true,@SWG\Items("leseDatenstand"))
	 * @SWG\Property(name="error",type="boolean",required=true)
	 * @SWG\Property(name="status",type="integer",required=true)
	 * @SWG\Model(id="leseDatenstand")
	 * @SWG\Property(name="nachricht",type="string",format="date",required=true)
	 * @SWG\Property(name="zeitstempel",type="string",required=true)
	 * @SWG\Property(name="httpCode",type="integer",required=true)
	 */
	public function leseDatenstand() {
		$this->dbh->leseDatenstand ();
	}
	/**
	 * @SWG\Api(
	 * path="/aktualisiereDatenstandManuell",
	 * @SWG\Operation(
	 * summary="Aktuallisiert den Datenstand manuell.",
	 * method="PUT",
	 * type="Index",
	 * @SWG\ResponseMessages(
	 * @SWG\ResponseMessage(message="Keine Indexe wurden aktualisiert!",code=404),
	 * @SWG\ResponseMessage(message="Aktualisierung fehlgeschlagen",code=404)
	 * )
	 * )
	 * )
	 * @SWG\Model(id="Index")
	 * @SWG\Property(name="message",type="string",required=true)
	 * @SWG\Property(name="error",type="boolean",required=true)
	 * @SWG\Property(name="status",type="integer",required=true)
	 */
	public function aktualisiereDatenstandManuell() {
		$this->aktualisiereIndexe ( "manuell" );
	}
	public function aktualisiereIndexe($type) {
		$letztesDatum = "";
		if ($type == "delta" || $type == "manuell") {
			$aktualisierteIndexe = array ();
			$fehlgeschlageneIndexe = array ();
			$andereIndexe = "";
			try {
				$this->indexe = $this->dbh->leseIndexe ();
				
				for($i = 0; $i < count ( $this->indexe ); $i ++) {
					if ($this->indexe [$i]->kuerzel == "")
						continue;
					$lastdate = $this->dbh->leseLetztesDatum ( $this->indexe [$i]->id );
					if ($lastdate != 0) {
						$ld = explode ( "-", $lastdate );
						$url = "http://ichart.finance.yahoo.com/table.csv?s=" . $this->indexe [$i]->kuerzel . "&a=" . ($ld [1] - 1) . "&b=" . ($ld [2] + 1) . "&c=" . $ld [0] . "&d=" . (date ( "m" ) - 1) . "&e=" . (date ( "d" ) - 1) . "&f=" . date ( "Y" ) . "&g=d&ignore=.csv";
						$row = 1;
						$data_array = array ();
						try {
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
						} catch ( ErrorException $e ) {
							$fehlgeschlageneIndexe [$this->indexe [$i]->id] = $this->indexe [$i]->name;
							continue;
						}
						if (count ( $data_array ) <= 1) {
							continue;
						}
						array_shift ( $data_array );
						$this->dbh->aktualisiereIndexe ( $data_array, $this->indexe [$i]->id, "delta" );
						$aktualisierteIndexe [$this->indexe [$i]->id] = $this->indexe [$i]->name;
					}
				}
				if (count ( $aktualisierteIndexe ) > 0 || count ( $fehlgeschlageneIndexe ) > 0) {
					$code = 200;
					$err = false;
					$manuell = ($type == "manuell") ? " manuell " : " ";
					$msg = "Daten von Index(e) ";
					foreach ( $aktualisierteIndexe as $ai ) {
						$msg .= $ai . ", ";
					}
					$msg = (count ( $aktualisierteIndexe ) > 0) ? substr ( $msg, 0, - 2 ) : "Keine Daten";
					$msg .= " wurden" . $manuell . "aktualisiert";
					
					if (count ( $fehlgeschlageneIndexe ) > 0) {
						$msg .= " und folgende sind fehlgeschlagen: ";						
						foreach ( $fehlgeschlageneIndexe as $fi ) {
							$msg .= $fi . ", ";
						}
						$msg = substr ( $msg, 0, - 2 );
						$code = 409;
						$err = true;
					}
					$this->dbh->schreibeLog ( $msg, 1, $code );
					$this->app->render ( $code, array (
							"message" => $msg.".",
							"aktualisierteIndexe" => array($aktualisierteIndexe),
							"fehlgeschlageneIndexe" => array($fehlgeschlageneIndexe),
							"error" => $err
					) );
				} else {
					throw new PDOException ();
				}
			} catch ( PDOException $e ) {
				$message = (DEBUG == true) ? $e->getMessage () : "Keine Indexe wurden aktualisiert!";
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