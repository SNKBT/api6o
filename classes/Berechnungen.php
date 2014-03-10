<?php
class Berechnungen {
	
	private $indexArray = array ();
	
	public function berechneRendite() {
		
		// $_POST['startdatum'] und $_POST['enddatum'] ueberpruefen
		if (!isset ($_POST['enddatum'])) {
			die;
		}
		return $this->ueberpruefeDatum($_POST['enddatum']);
		
		// ueberpruefeKapital(2014-10-03);
		
		// leseIndexDaten ();
		
		// berechneVeraenderungKapital ();
		
		// berechneKumuliertesKapital ();
		
		// berechneGewinn ();
		
		// berechneDurchbrueche ();
	}
	private function ueberpruefeDatum($name) {
		// datum ueberpruefen
		$test = array();
		$test["Name"] = $name;
		return $test;
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