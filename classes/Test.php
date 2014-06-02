<?php
/**
 * @SWG\Resource(
 *  apiVersion="1.0",
 *  basePath="http://api.localhost",
 *  description="Testklasse fuer JSON-Funktionstests"
 * )
 */
class Test {
	/**
	 * @SWG\Api(
	 * path="/hello/{name}",
	 * @SWG\Operation(
	 * summary="Return the given name",
	 * method="GET",
	 * type="Name",
	 * @SWG\Parameter(
	 * name="name",
	 * description="Name of e.g. a person that needs to be returned",
	 * paramType="path",
	 * required=true,
	 * type="string"
	 * )
	 * )
	 * )
	 */
	function helloName($name) {
		$data_array = array ();
		$data_array ["Name"] = $name;
		return $data_array;
	}
	function testPost() {
		if (isset ( $_POST )) {
			echo "Deine mitgegebenen Attribute sind ... <br />";
			echo "<pre>";
			print_r ( $_POST );
			echo "</pre>";
		}
	}
}

?>