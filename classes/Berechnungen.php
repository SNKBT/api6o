<?php
/**
 * An example resource
 * @SWG\Resource(
 *  apiVersion="0.1",
 *  basePath="http://api.localhost",
 *  description="API Berechnungen"
 * )
 */
class Berechnungen {
	private $dbh;
	private $app;
	private $indexID = 0;
	private $indexWerteArray = array ();
	private $startkapital = 0;
	private $startkurs = 0;
	private $rente_auszahlung = 0;
	private $totalStueck = 0;
	private $kapital = 0;
	private $smaArray = array ();
	function __construct($dbh, $app) {
		$this->dbh = $dbh;
		$this->app = $app;
	}
	/**
	 * @SWG\Api(
	 * path="/leseIndexe",
	 * @SWG\Operation(
	 * summary="Gibt die in der DB vorhandenen Indexe zurueck.",
	 * method="GET",
	 * type="Index",
	 * @SWG\ResponseMessage(
	 * message="Keine Indexe gefunden",
	 * code=404
	 * )
	 * )
	 * )
	 * @SWG\Model(id="Index")
	 * @SWG\Property(name="leseIndexe",type="array",required=true,@SWG\Items("leseIndexe"))
	 * @SWG\Property(name="error",type="boolean",required=true)
	 * @SWG\Property(name="status",type="integer",required=true)
	 * @SWG\Model(id="leseIndexe")
	 * @SWG\Property(name="id",type="string",format="date",required=true)
	 * @SWG\Property(name="name",type="string",required=true)
	 * @SWG\Property(name="kuerzel",type="string",required=true)
	 */
	public function leseIndexe() {
		$result = array();
		$result['leseIndexe'] = $this->dbh->leseIndexe ();
		$this->app->render ( 200, $result );
	}
	/**
	 * @SWG\Api(
	 * path="/berechneRendite",
	 * @SWG\Operation(
	 * summary="Berechnet die Rendite des gewaehlten Index.",
	 * method="POST",
	 * type="berechneRendite",
	 * @SWG\Parameters(
	 * @SWG\Parameter(
	 * name="startDatum",
	 * description="Start Datum",
	 * paramType="form",
	 * required=true,
	 * type="string",
	 * format="date"
	 * ),
	 * @SWG\Parameter(
	 * name="endDatum",
	 * description="End Datum",
	 * paramType="form",
	 * required=true,
	 * type="string",
	 * format="date"
	 * ),
	 * @SWG\Parameter(
	 * name="indexID",
	 * description="ID des Indexes",
	 * paramType="form",
	 * required=true,
	 * type="integer"
	 * ),
	 * @SWG\Parameter(
	 * name="startkapital",
	 * description="Startkapital",
	 * paramType="form",
	 * required=true,
	 * type="integer"
	 * ),
	 * @SWG\Parameter(
	 * name="rente_auszahlung",
	 * description="Rente oder Auszahlung",
	 * paramType="form",
	 * required=true,
	 * type="integer"
	 * )
	 * ),
	 * @SWG\ResponseMessages(
	 * @SWG\ResponseMessage(message="Bitte folgende Parameter mitliefern",code=404),
	 * @SWG\ResponseMessage(message="Fehlerhaftes Datum",code=422),
	 * @SWG\ResponseMessage(message="Fehlerhafte Eingabe des Kapitals",code=422)
	 * )
	 * )
	 * )
	 * @SWG\Model(id="berechneRendite")
	 * @SWG\Property(name="message",type="string",required=true)
	 * @SWG\Property(name="indexID",type="integer",required=true)
	 * @SWG\Property(name="indexWerte",type="array",required=true,@SWG\Items("indexWerte"))
	 * @SWG\Model(id="indexWerte")
	 * @SWG\Property(name="tradeDate",type="string",format="date",required=true)
	 * @SWG\Property(name="adjClose",type="integer",required=true)
	 */
	public function berechneRendite() {
		if ($_SERVER ['REQUEST_METHOD'] != 'POST') {
			die ();
		}
		
		// $this->app->contentType ( 'application/json' );
		// $request = $this->app->request ()->getBody ();
		// $wine = json_decode($request);
		// $return = json_decode ( $request );
		
		$missingPost = "";
		$missingPost .= (! isset ( $_POST ['startDatum'] )) ? "startDatum, " : "";
		$missingPost .= (! isset ( $_POST ['endDatum'] )) ? "endDatum, " : "";
		$missingPost .= (! isset ( $_POST ['indexID'] ) || ! is_numeric ($_POST ['indexID'])) ? "indexID, " : "";
		$missingPost .= (! isset ( $_POST ['startkapital'] )) ? "startkapital, " : "";
		$missingPost .= (! isset ( $_POST ['rente_auszahlung'] )) ? "rente_auszahlung, " : "";
		
		if ($missingPost != "") {
			$this->app->render ( 404, array (
					"message" => "Bitte folgende Parameter mitliefern: " . $missingPost,
					"error" => true 
			) );
			$this->app->stop ();
		}
		
		$this->ueberpruefeDatum ( $_POST ['startDatum'] );
		$this->ueberpruefeDatum ( $_POST ['endDatum'], $_POST ['startDatum'] );
		$this->indexID = $this->ueberpruefeIndex ( $_POST ['indexID'] );
		$this->startkapital = $this->ueberpruefeKapital ( $_POST ['startkapital'], "startkapital" );
		
		$this->rente_auszahlung = $this->ueberpruefeKapital ( $_POST ['rente_auszahlung'], "rente_auszahlung" );
		$this->kapital = $this->startkapital;
		
		$this->indexWerteArray = $this->leseIndexWerte ( $this->indexID, $_POST ['startDatum'], $_POST ['endDatum'] );
		
		$this->totalStueck = 0;
		
		//$this->berechneStartkapital ();
		
		if ($this->rente_auszahlung >= 0) {
			$this->berechneZahlungen ( "einzahlung" );
		} else {
			$this->berechneZahlungen ( "auszahlung" );
		}
		
		// $this->smaArray = $this->berechneSMA ();
		// "smaWerte" => $this->smaArray
		
		// leseIndexDaten ();
		
		// berechneVeraenderungKapital ();
		
		// berechneKumuliertesKapital ();
		
		// berechneGewinn ();
		
		// berechneDurchbrueche ();
		
		$this->app->render ( 200, array (
				"message" => "SCOOOOOOOOORE",
				"indexID" => $this->indexID,
				"indexWerte" => $this->indexWerteArray 
		) );
	}
	private function ueberpruefeDatum($datum, $startDatum = NULL) {
		try {
			$cdate = explode ( "-", $datum );
			checkdate ( $cdate [1], $cdate [2], $cdate [0] );
			
			$now = new DateTime ();
			$date = DateTime::createFromFormat ( 'Y-m-j', $datum );
			if ($date >= $now)
				throw new Exception ( "Datum ist in der Zukunft" );
			if ($startDatum != NULL) {
				$start = DateTime::createFromFormat ( 'Y-m-j', $startDatum );
				if ($date <= $start) {
					throw new Exception ( "Enddatum ist frueher als Startdatum" );
				}
			}
		} catch ( Exception $e ) {
			$this->app->render ( 422, array (
					"message" => "Fehlerhaftes Datum",
					"error" => true 
			) );
			$this->app->stop ();
		}
	}
	private function ueberpruefeIndex($indexID) {
		$result = $this->dbh->pruefeIndex ( $indexID );
		return $result;
	}
	private function ueberpruefeKapital($kapital, $test = "") {
		try {
			if (! is_numeric ( $kapital ))
				throw new Exception ( "Kein Integer" );
			$kap = ( int ) $kapital;
			if (! is_int ( $kap ))
				throw new Exception ( "Kein Integer" );
			if (($test == "startkapital" && $kapital < 0) || $test == "startkapital" && $kapital > 100000)
				throw new Exception ( "Ungueltiges Startkapital" );
			if (($test == "rente_auszahlung" && $kapital < - 10000) || ($test == "rente_auszahlung" && $kapital > 10000))
				throw new Exception ( "Ungueltiges Rente-/Auszahlungskapital" );
			return $kapital;
		} catch ( Exception $e ) {
			$this->app->render ( 422, array (
					"message" => "Fehlerhafte Eingabe des Kapitals",
					"error" => true 
			) );
			$this->app->stop ();
		}
	}
	public function leseIndexWerte($indexID, $startDatum, $endDatum) {
		$result = $this->dbh->leseIndexWerte ( $indexID, date ( "Y-m-d", strtotime ( $startDatum ) ), date ( "Y-m-d", strtotime ( $endDatum ) ) );
		return $result;
	}
	private function berechneStartkapital() {
		$zahlungen = ($this->startkapital / 4);
		
		for($i = count ( $this->indexWerteArray ) - 1; $i >= 0; $i --) {
			if ($this->startkapital <= 0) {
				break;
			}
			if ((date ( 'N', strtotime ( $this->indexWerteArray [$i]->tradeDate ) ) == 1) && ($this->startkurs == 0)) {
				$this->startkurs = $this->indexWerteArray [$i]->adjClose;
				$this->zahleEin ( $i, $zahlungen );
				$this->startkapital = ($this->startkapital - $zahlungen);
			} elseif ((date ( 'N', strtotime ( $this->indexWerteArray [$i]->tradeDate ) ) == 1) && ($this->indexWerteArray [$i]->adjClose >= $this->startkurs)) {
				$this->zahleEin ( $i, $zahlungen );
				$this->startkapital = ($this->startkapital - $zahlungen);
			}
		}
	}
	private function berechneZahlungen($zahlung) {
		
		$startKap = ($this->startkapital / 4);
		
		for($i = count ( $this->indexWerteArray ) - 1; $i >= 0; $i --) {
			
			if ((date ( 'N', strtotime ( $this->indexWerteArray [$i]->tradeDate ) ) == 1) && ($this->startkurs == 0)  && ($this->startkapital > 0)) {
				$this->startkurs = $this->indexWerteArray [$i]->adjClose;
				$this->zahleEin ( $i, $startKap );
				$this->startkapital = ($this->startkapital - $startKap);
			} elseif ((date ( 'N', strtotime ( $this->indexWerteArray [$i]->tradeDate ) ) == 1) && ($this->indexWerteArray [$i]->adjClose >= $this->startkurs) && ($this->startkapital > 0)) {
				$this->zahleEin ( $i, $startKap );
				$this->startkapital = ($this->startkapital - $startKap);
			}
			
			$tag = substr ( $this->indexWerteArray [$i]->tradeDate, - 2 );
			$monat = substr ( $this->indexWerteArray [$i]->tradeDate, 5, 2 );
			$letzterMonat = ($i < (count ( $this->indexWerteArray ) - 1)) ? substr ( $this->indexWerteArray [$i + 1]->tradeDate, 5, 2 ) : $monat;
			
			if ($letzterMonat != $monat) {
				if ($zahlung == "auszahlung") {
					$this->zahleAus ( $i );
				} elseif ($zahlung == "einzahlung") {
					$this->zahleEin ( $i, $this->rente_auszahlung );
				}
			} else {
				$this->berechneStueckUndWert ( $i );
			}
		}
	}
	private function zahleAus($i) {
		if ((($this->totalStueck * $this->indexWerteArray [$i]->adjClose) + $this->rente_auszahlung) > 0) {
			$this->indexWerteArray [$i]->desinvestition = $this->rente_auszahlung;
			$investition = round ( ($this->rente_auszahlung / $this->indexWerteArray [$i]->adjClose), 4 );
			$this->totalStueck = ($this->totalStueck + $investition);
		}
		$this->berechneStueckUndWert ( $i );
	}
	private function zahleEin($i, $kapital) {
		if ($kapital >= 0) {
			$this->indexWerteArray [$i]->investition = $kapital;
			$investition = round ( ($kapital / $this->indexWerteArray [$i]->adjClose), 4 );
			$this->totalStueck = ($this->totalStueck + $investition);
			$this->berechneStueckUndWert ( $i );
		}
	}
	private function berechneStueckUndWert($i) {
		$this->indexWerteArray [$i]->stueck = $this->totalStueck;
		$this->indexWerteArray [$i]->wert = round ( $this->totalStueck * $this->indexWerteArray [$i]->adjClose, 4 );
	}
	private function berechneVeraenderungKapital() {
	}
	private function berechneKumuliertesKapital() {
	}
	private function berechneGewinn() {
	}
	private function berechneSMA() {
		$result = array ();
		$ar = count ( $this->indexWerteArray );
		for($i = 0; $i < $ar; $i ++) {
			$a = 0;
			for($e = $i; $e < 5; $e ++) {
				$a += $this->indexWerteArray [$e]->adjClose;
			}
			$result [$i] = (($a) / 5);
		}
		return $result;
	}
	private function berechneDurchbrueche() {
	}
}

?>