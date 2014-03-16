<?php
class YahooDaten {
	private $dbh;
	function __construct($dbh) {
		$this->dbh = $dbh;
	}
	public function aktualisiereAlleIndexe() {
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
		
		//$url = "http://ichart.finance.yahoo.com/table.csv?s=%5ENDX&d=2&e=16&f=2014&g=d&a=9&b=1&c=1985&ignore=.csv"; //NASDAQ-100
		// $url = "http://ichart.finance.yahoo.com/table.csv?s=%5ESSMI&d=2&e=16&f=2014&g=d&a=10&b=9&c=1990&ignore=.csv"; //SMI
		$url = "http://ichart.finance.yahoo.com/table.csv?s=%5EGDAXI&d=2&e=16&f=2014&g=d&a=10&b=26&c=1990&ignore=.csv"; // DAX
		
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
		$result = $this->dbh->aktualisiereAlleIndexe ( $data_array );
		return $result;
	}
}

?>