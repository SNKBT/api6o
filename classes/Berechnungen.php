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
	private $kapital = 0;
	private $totalStueck = 0;
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
	 * @SWG\Property(name="ID",type="integer",required=true)
	 * @SWG\Property(name="Name",type="string",required=true)
	 */
	public function leseIndexe() {
		$result = $this->dbh->leseIndexe ();
		$this->app->render ( 200, $result );
	}
	/**
	 * @SWG\Api(
	 * path="/berechneRendite",
	 * @SWG\Operation(
	 * summary="Berechnet die Rendite des gewaehlten Index.",
	 * method="POST",
	 * type="Rendite",
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
	 * name="kapital",
	 * description="Startkapital",
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
	 * @SWG\Model(id="Rendite")
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
		$missingPost .= (! isset ( $_POST ['indexID'] )) ? "indexID, " : "";
		$missingPost .= (! isset ( $_POST ['indexID'] )) ? "kapital, " : "";
		
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
		$this->kapital = $this->ueberpruefeKapital ( $_POST ['kapital'] );
		
		$this->indexWerteArray = $this->leseIndexWerte ( $this->indexID, $_POST ['startDatum'], $_POST ['endDatum'] );
		
		$this->berechneInvestitionen ();
		
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
			$cdate = explode ( ".", $datum );
			checkdate ( $cdate [1], $cdate [0], $cdate [2] );
			$now = new DateTime ();
			$date = DateTime::createFromFormat ( 'j.m.Y', $datum );
			if ($date >= $now)
				throw new Exception ( "Datum ist in der Zukunft" );
			if ($startDatum != NULL) {
				$start = DateTime::createFromFormat ( 'j.m.Y', $startDatum );
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
	private function ueberpruefeKapital($kapital) {
		try {
			if (! is_numeric ( $kapital ))
				throw new Exception ( "Kein Integer" );
			$kap = ( int ) $kapital;
			if (! is_int ( $kap ))
				throw new Exception ( "Kein Integer" );
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
	private function berechneInvestitionen() {
		for($i = count ( $this->indexWerteArray ) - 1; $i >= 0; $i --) {
			// for($i = 0; $i < count ( $this->indexWerteArray ); $i ++) {
			if (substr ( $this->indexWerteArray [$i]->tradeDate, - 2 ) == '01') {
				$this->indexWerteArray [$i]->investition = $this->kapital;
				$investition = ($this->kapital / $this->indexWerteArray [$i]->adjClose);
				$this->indexWerteArray [$i]->stueck = $investition;
				$this->totalStueck .= $investition;
				$this->indexWerteArray [$i]->wert = $this->totalStueck * $this->indexWerteArray [$i]->adjClose;
			} else {
				$this->indexWerteArray [$i]->stueck = $this->totalStueck;
				$this->indexWerteArray [$i]->wert = $this->totalStueck * $this->indexWerteArray [$i]->adjClose;
			}
		}
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