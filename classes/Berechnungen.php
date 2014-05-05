<?php
/**
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
	private $totalStartkapital = 0;
	private $startkurs = 0;
	private $fistAdjClose = 0;
	private $lastAdjClose = 0;
	private $rente_auszahlung = 0;
	private $total_rente_auszahlung = 0;
	private $totalAnteile = 0;
	private $totalBargeld = 0;
	private $gesamtrenditeIndex = 0;
	private $gesamtrenditeKapital = 0;
	private $veraenderungStartkapitalProzent = 0;
	private $veraenderungStartkapital = 0;
	private $totalRenteeinzahlungen = 0;
	private $buySMA = 0;
	private $sellSMA = 0;
	private $buyModus = true;
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
		$result = array ();
		$result ['leseIndexe'] = $this->dbh->leseIndexe ();
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
	 * type="double"
	 * ),
	 * @SWG\Parameter(
	 * name="rente_auszahlung",
	 * description="Rente oder Auszahlung",
	 * paramType="form",
	 * required=true,
	 * type="double"
	 * ),
	 * @SWG\Parameter(
	 * name="buySMA",
	 * description="Buy SMA",
	 * paramType="form",
	 * type="integer"
	 * ),
	 * @SWG\Parameter(
	 * name="sellSMA",
	 * description="Sell SMA",
	 * paramType="form",
	 * type="integer"
	 * )
	 * ),
	 * @SWG\ResponseMessages(
	 * @SWG\ResponseMessage(message="Bitte folgende Parameter mitliefern",code=404),
	 * @SWG\ResponseMessage(message="Index nicht gefunden",code=404),
	 * @SWG\ResponseMessage(message="Keine Indexwerte in diesem Zeitraum gefunden",code=404),
	 * @SWG\ResponseMessage(message="Fehlerhaftes Datum",code=422),
	 * @SWG\ResponseMessage(message="Fehlerhafte Eingabe des Kapitals",code=422),
	 * @SWG\ResponseMessage(message="Fehlerhafte Eingabe des SMA Wertes",code=422)
	 * )
	 * )
	 * )
	 * @SWG\Model(id="berechneRendite")
	 * @SWG\Property(name="message",type="string",required=true)
	 * @SWG\Property(name="gesamtrenditeIndex",type="double",required=true)
	 * @SWG\Property(name="gesamtrenditeKapital",type="double",required=true)
	 * @SWG\Property(name="veraenderungStartkapital",type="double",required=true)
	 * @SWG\Property(name="veraenderungStartkapitalGeld",type="double",required=true)
	 * @SWG\Property(name="totalRenteeinzahlungen",type="double",required=true)
	 * @SWG\Property(name="indexID",type="integer",required=true)
	 * @SWG\Property(name="indexWerte",type="array",required=true,@SWG\Items("indexWerte"))
	 * @SWG\Model(id="indexWerte")
	 * @SWG\Property(name="tradeDate",type="string",format="date",required=true)
	 * @SWG\Property(name="adjClose",type="double",required=true)
	 * @SWG\Property(name="anteile",type="double",required=true)
	 * @SWG\Property(name="wert",type="double",required=true)
	 * @SWG\Property(name="buySMA",type="double")
	 * @SWG\Property(name="sellSMA",type="double")
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
		$missingPost .= (! isset ( $_POST ['indexID'] ) || ! is_numeric ( $_POST ['indexID'] )) ? "indexID, " : "";
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
		$this->totalStartkapital = $this->startkapital;
		
		$this->buySMA = (isset ( $_POST ['buySMA'] )) ? $this->ueberpruefeSMA ( $_POST ['buySMA'] ) : null;
		$this->sellSMA = (isset ( $_POST ['sellSMA'] )) ? $this->ueberpruefeSMA ( $_POST ['sellSMA'] ) : null;
		
		$this->indexWerteArray = $this->leseIndexWerte ( $this->indexID, $_POST ['startDatum'], $_POST ['endDatum'] );
		
		$this->totalAnteile = 0;
		
		if ($this->rente_auszahlung >= 0) {
			$this->berechneZahlungen ( "einzahlung" );
		} else {
			$this->berechneZahlungen ( "auszahlung" );
		}
		
		// ---- START AUSGABE ----
		$this->app->render ( 200, array (
				"message" => "Performance Index",
				"gesamtrenditeIndex" => $this->berechneGesamtrenditeIndex (),
				"gesamtrenditeKapital" => $this->berechneGesamtrenditeKapital (),
				"veraenderungStartkapital" => $this->berechneVeraenderungStartkapital (),
				"veraenderungStartkapitalGeld" => $this->berechneVeraenderungStartkapitalGeld (),
				"totalRenteeinzahlungen" => $this->totalRenteeinzahlungen,
				"totalBargeld" => $this->totalBargeld,
				"indexID" => $this->indexID,
				"indexWerte" => $this->indexWerteArray 
		) );
		// ---- ENDE AUSGABE ----
	}
	private function ueberpruefeDatum($datum, $startDatum = NULL) {
		try {
			$cdate = explode ( "-", $datum );
			if (checkdate ( $cdate [1], $cdate [2], $cdate [0] ) != true)
				throw new Exception ( "Fehlerhaftes Datum" );
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
	private function ueberpruefeSMA($sma) {
		try {
			if (! is_numeric ( $sma ))
				throw new Exception ( "Kein Integer" );
			$intsma = ( int ) $sma;
			if (! is_int ( $intsma ))
				throw new Exception ( "Kein Integer" );
			if (strpos ( $sma, "." ) || strpos ( $sma, "," ))
				throw new Exception ( "Kein Integer" );
			if (($sma < 2) || ($sma > 500))
				throw new Exception ( "Ungueltiger SMA Wert" );
			return $sma;
		} catch ( Exception $e ) {
			$this->app->render ( 422, array (
					"message" => "Fehlerhafte Eingabe des SMA Wertes",
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
			
			if ($this->buyModus == true) {
				if ((date ( 'N', strtotime ( $this->indexWerteArray [$i]->tradeDate ) ) == 1) && ($this->startkurs == 0) && ($this->startkapital > 0)) {
					$this->startkurs = $this->indexWerteArray [$i]->adjClose;
					$this->zahleEin ( $i, $startKap );
					$this->startkapital = ($this->startkapital - $startKap);
				} elseif ((date ( 'N', strtotime ( $this->indexWerteArray [$i]->tradeDate ) ) == 1) && ($this->indexWerteArray [$i]->adjClose >= $this->startkurs) && ($this->startkapital > 0)) {
					$this->zahleEin ( $i, $startKap );
					$this->startkapital = ($this->startkapital - $startKap);
				}
			}elseif($this->buyModus == false){
				$this->verkaufeAlleAnteile ( $i );
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
				$this->berechneAnteilUndWert ( $i );
			}
			if ($i == (count ( $this->indexWerteArray ) - 1)) {
				$this->firstAdjClose = $this->indexWerteArray [$i]->adjClose;
			}
			if ($i == 0) {
				$this->lastAdjClose = $this->indexWerteArray [$i]->adjClose;
			}
			if ($this->buySMA != null)
				$this->indexWerteArray [$i]->buySMA = $this->berechneSMA ( $i, "buy" );
			if ($this->sellSMA != null)
				$this->indexWerteArray [$i]->sellSMA = $this->berechneSMA ( $i, "sell" );
			
			$this->berechneSignale($i);
		}
	}
	private function zahleAus($i) {
		if ((($this->totalAnteile * $this->indexWerteArray [$i]->adjClose) + $this->rente_auszahlung) > 0) {
			$this->indexWerteArray [$i]->desinvestition = $this->rente_auszahlung;
			$deinvestition = round ( ($this->rente_auszahlung / $this->indexWerteArray [$i]->adjClose), 4 );
			$this->totalAnteile = ($this->totalAnteile + $deinvestition);
		}
		$this->berechneAnteilUndWert ( $i );
	}
	private function zahleEin($i, $kapital) {
		if ($kapital >= 0) {
			$this->indexWerteArray [$i]->investition = $kapital;
			$this->totalRenteeinzahlungen += $kapital;
			$investition = round ( ($kapital / $this->indexWerteArray [$i]->adjClose), 4 );
			$this->totalAnteile = ($this->totalAnteile + $investition);
			$this->berechneAnteilUndWert ( $i );
		}
	}
	private function verkaufeAlleAnteile($i) {
		echo "Verkaufe " . $this->totalAnteile . "Anteile zu einem Kurs von ". $this->indexWerteArray [$i]->adjClose . " ergibt ein Barbetrag von ";
		echo ($this->totalAnteile * $this->indexWerteArray [$i]->adjClose);
		exit;
	}
	private function berechneAnteilUndWert($i) {
		$this->indexWerteArray [$i]->anteile = $this->totalAnteile;
		$this->indexWerteArray [$i]->bargeld = $this->totalBargeld;
		$this->indexWerteArray [$i]->wert = round ( $this->totalAnteile * $this->indexWerteArray [$i]->adjClose, 4 );
		$this->indexWerteArray [$i]->vermoegen = ($this->indexWerteArray [$i]->bargeld + $this->indexWerteArray [$i]->wert);
	}
	private function berechneGesamtrenditeIndex() {
		$return = ($this->firstAdjClose > 0) ? round ( (($this->lastAdjClose / $this->firstAdjClose) * 100), 2 ) : null;
		return $return;
	}
	private function berechneGesamtrenditeKapital() {
		$return = ($this->firstAdjClose > 0) ? round ( ((($this->totalAnteile * $this->lastAdjClose) / ($this->totalRenteeinzahlungen + $this->totalStartkapital)) * 100), 2 ) : null;
		return $return;
	}
	private function berechneVeraenderungStartkapital() {
		$return = ($this->totalStartkapital > 0) ? round ( ((($this->totalAnteile * $this->lastAdjClose) / $this->totalStartkapital) * 100), 2 ) : null;
		return $return;
	}
	private function berechneVeraenderungStartkapitalGeld() {
		$return = ($this->totalStartkapital > 0) ? round ( (($this->totalAnteile * $this->lastAdjClose) - $this->totalStartkapital), 2 ) : null;
		return $return;
	}
	private function berechneSMA($i, $type) {
		$result = 0;
		$sum = 0;
		$count = 0;
		$typeArray = ($type == "buy") ? $this->buySMA : $this->sellSMA;
		for($n = 0; $n < $typeArray; $n ++) {
			if ((count ( $this->indexWerteArray ) - 1 - $i - $n) >= 0) {
				$arr = ($i + $n);
				$count ++;
				$sum += $this->indexWerteArray [$arr]->adjClose;
			}
		}
		$result = ($sum / $count);
		return $result;
	}
	private function berechneSignale($i) {
		$close = $this->indexWerteArray [$i]->adjClose;
		//$closeVortag = ($i < (count($this->indexWerteArray)-1)) ? $this->indexWerteArray [$i-1]->adjClose : $close;
		$closeVortag = ($i > 0) ? $this->indexWerteArray [$i-1]->adjClose : $close;
		$buy = $this->indexWerteArray [$i]->buySMA;
		$sell = $this->indexWerteArray [$i]->sellSMA;
		
		if($this->totalAnteile > 0 && $closeVortag > $sell && $close < $sell && $sell < $buy) {
			$this->indexWerteArray[$i]->signal = "sell";
			$desinvestition = round(($this->totalAnteile * $this->indexWerteArray [$i]->adjClose),4);
			$this->indexWerteArray [$i]->desinvestition = $desinvestition;
			$this->totalBargeld = $desinvestition;
			$this->totalAnteile = 0;
				
		}
		
	}
}

?>