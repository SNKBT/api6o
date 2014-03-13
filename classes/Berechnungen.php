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
	private $indexArray = array ();
	function __construct($dbh, $app) {
		$this->dbh = $dbh;
		$this->app = $app;
	}
	/**
	 * @SWG\Api(
	 * path="/leseIndexe",
	 * @SWG\Operation(
	 * summary="Gibt die in der DB vorhandenen Indexe zur&uml;ck.",
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
		return $result;
	}
	public function berechneRendite() {
		
		// $_POST['startdatum'] und $_POST['enddatum'] ueberpruefen
		if (isset ( $_POST ['endDatum'] )) {
			$this->ueberpruefeDatum ( $_POST ['endDatum'] );
		}
		$this->app->render ( 200, array (
				"message" => "SEEEEEEEEEELLERI" 
		) );
		
		// ueberpruefeKapital(2014-10-03);
		
		// leseIndexDaten ();
		
		// berechneVeraenderungKapital ();
		
		// berechneKumuliertesKapital ();
		
		// berechneGewinn ();
		
		// berechneDurchbrueche ();
	}
	private function ueberpruefeDatum($datum) {
		try{
			$date = explode ( ".", $datum );
			$now = new DateTime ();
			$user_date = DateTime::createFromFormat ( 'j.m.Y', $datum );
			checkdate ( $date [1], $date [0], $date [2] );
			if ($user_date >= $now)
				throw new Exception("Datum ist in der Zukunft");
		}catch (Exception $e){
			$this->app->render ( 422, array (
					"message" => "Fehlerhaftes Datum",
					"error" => true 
			) );
			$this->app->stop ();
		}
	}
	private function ueberpruefeKapital() {
		// datum ueberpruefen
	}
	private function berechneVeraenderungKapital() {
	}
	private function berechneKumuliertesKapital() {
	}
	private function berechneGewinn() {
	}
	private function berechneDurchbrueche() {
	}
}

?>