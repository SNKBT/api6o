<?php
/**
 * An example resource
 * @SWG\Resource(
 *  apiVersion="0.2",
 *  basePath="http://example.com/api"
 * )
 *
 * Auto-generated:
 * Uses swaggerVersion="1.2" by default.
 * classname "ResolveController" should resolve to resourcePath "/resolve".
 * 
 */
class Test {
	/**
	 * @SWG\Api(
	 * path="/hello/:name",
	 * @SWG\Operation(
	 * summary="Return the given name",
	 * method="GET",
	 * type="hello[name]"
	 * )
	 * )
	 *
	 * Auto-generated:
	 * nickname resolves to the method name "get_dogs"
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