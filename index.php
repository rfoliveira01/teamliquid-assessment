<?php

/*
 * Please write robust and secure code, we will use different test csv files in evaluating the code
 * We have set up a MySQL server to use for this project, the database layout can be found in the
 * sql file and the relevant data to access said server is set in the import() function
 */

class CSVHandler {
	private $fileName;
	private $importedData;
	private $PDOConnection;

	const VALID_CSV_HEADER = ['id','author','title'];
	public function __construct($file) {
		/*
		 * Load the file
		 */
		$this->fileName = $file;
		try{
			$this->PDOConnection = new PDO("mysql:host=".$_ENV['DB_SERVER'].":".$_ENV['DB_SERVER_PORT'].";dbname=".$_ENV['MYSQL_DATABASE'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD']);
			/**
			* Use PDO::ERRMODE_EXCEPTION, to capture errors and write them to
			* a log file for later inspection instead of printing them to the screen.
			*/
			$this->PDOConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $pe) {
			die("Could not connect to the database {$_ENV['MYSQL_DATABASE']} :" . $pe->getMessage());
		}
		$this->readCSV();
		
	}
	private function insertIntoDb($author, $title){
		$query = "INSERT INTO `teamliquid`.`testtable` (`thing_name`,`thing_title`)	VALUES (:author,:title);";
		$sth = $this->PDOConnection->prepare($query);
		
		// Bind parameters to statement variables.
		$sth->bindParam(':author', $author);
		$sth->bindParam(':title', $title);

		// Execute statement.
		$sth->execute();

	}

	private function fetchFromDB(){
		$query = "SELECT `testtable`.`thing_id`,
			`testtable`.`thing_name`,
			`testtable`.`thing_title`
		FROM `teamliquid`.`testtable`;";
		$sth = $this->PDOConnection->prepare($query);
		// Execute statement.
		$sth->execute();
		// Set fetch mode to FETCH_ASSOC to return an array indexed by column name.
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		// Fetch result.
		$result = $sth->fetchAll();
		return $result;
	}
	
	private function arrayToHmtlTable($array){
		if( count($array) > 0 ){
			$table  = "
			<table>
				<thead>
					<tr>";
			foreach(array_keys($array[0]) as $header){
				$table.= "<th>" . $header . "</th>";
			}
			$table.= "</tr>
			</thead>
				<tbody>
					";
			foreach($array as $values){
				$table .= "<tr><td>".implode("</td><td>", $values)."</td></tr>";
			}
			$table.= "</tbody>";

		}
		echo $table;
	}

	private function validateHeader($header){
		$validFields = array_intersect($header,self::VALID_CSV_HEADER);

		if(count($validFields) == count(self::VALID_CSV_HEADER)){
			return true;
		}else{
			die("Invalid file");
		}

	}
	private function readCSV(){
		if(file_exists($this->fileName)) {
			$stream = fopen($this->fileName,"r");
			$this->importedData = [];
			if($stream != null){
				$header  = fgetcsv($stream,0,";");
				$this->validateHeader($header);
				while(!feof($stream)){
					$data  = fgetcsv($stream,0,";");
					$row = [];
					foreach($data as $i => $value){
						$row[$header[$i]] = $value;
					}
					array_push($this->importedData,$row);
				}
			}
		}else{
			die("File " . $this->fileName . " not found");
		}
	}
	public function show() {
		/*
		 * Output the file into a textarea
		 * Use the data to produce a html table
		 */
		$this->arrayToHmtlTable($this->importedData);
		
	}
	public function import() {
		/*
		 * Import data into a SQL database
		 */
		foreach($this->importedData as $data){
			$this->insertIntoDb($data['author'], $data['title']);
		}
	}
	public function makeTableFromDB() {
		/*
		 * Read data from SQL database and output as html table again
		 */
		$result = $this->fetchFromDB();

		$this->arrayToHmtlTable($result);
	}
}

?><!DOCTYPE html>
<html>
<head><title>CSV Handler</title>
<style>
table {
  font-family: "Times New Roman", Times, serif;
  border: 1px solid #FFFFFF;
  width: 350px;
  height: 200px;
  text-align: center;
  border-collapse: collapse;
}
table td, table th {
  border: 1px solid #FFFFFF;
  padding: 3px 2px;
}
table tbody td {
  font-size: 13px;
}
table tr:nth-child(even) {
  background: #D0E4F5;
}
table thead {
  background: #0B6FA4;
  border-bottom: 5px solid #FFFFFF;
}
table thead th {
  font-size: 17px;
  font-weight: bold;
  color: #FFFFFF;
  text-align: center;
  border-left: 2px solid #FFFFFF;
}
table thead th:first-child {
  border-left: none;
}
</style>

</head>

<body>
<?php
if(isset($_GET['file'])) {
	$csvHandler = new CSVHandler($_GET['file']);
	$csvHandler->show();
	$csvHandler->import();
	$csvHandler->makeTableFromDB();
} else {
	echo '<ul>';
	$files = glob('./*.csv');
	foreach($files as $file){
		echo sprintf('<li><a href="?file=%s">%s</a></li>',$file, str_replace('./','',$file));
	}
	echo '</ul>';
}
?>
</body>
</html>