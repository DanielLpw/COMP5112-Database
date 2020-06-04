<h1>Create_user</h1>
<form action="create_user.php" method="GET">
username and password must input <br>
New Username *:
<input type="text" name="new_username"><br>
User's Password *:
<input type="text" name="new_password"><br>
First Name:
<input type="text" name="First_name"><br>
Last Name:
<input type="text" name="Last_name"><br>
<input type="submit" value="Create">
</form>
<input type="button" value="Return" onclick="window.location.href='login.php'"/>

<?php
	//Link to DB
if (isset($_GET["new_username"]) && isset($_GET["new_password"])) {
	session_start();
	$servername = $_SESSION["DBSN"];
	$username = $_SESSION["DBUN"];
	$password = $_SESSION["DBPW"];
	$DBname = $_SESSION["DBname"];
	// Create connection
	$conn = new mysqli($servername, $username, $password, $DBname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
		}
	//echo ("connect success")
?>
<?php
	//The plaintext of the user’s password should not be stored in the database. Instead,
	//the password should be concatenated with a randomly generated string and hashed
	//using the MySQL 8.0 SHA2 function) and stored in your database table. The salt will
	//be stored in the database for password verification during the login process.
	//Allow users to create a new user account by providing a username and password.
	
	//Create USER
	//Random create salt.
	
	//Random generate salt,use the MD5 to generate 32-bit string
	$sql = "set @salt = MD5(RAND() * 10000)";
	$conn->query($sql);
	
	$create = $conn->prepare("INSERT INTO USER (username, password,salt,First_name,Last_name) 
	VALUES (?,sha2(concat(?,@salt),256),@salt,?,?);");
	$create->bind_param("ssss",$_GET["new_username"],$_GET["new_password"],$_GET["First_name"],$_GET["Last_name"]);
	
	//Check whether this Username repeat or not.If it is not existed, create it.
	$repeat = $conn->prepare("SELECT * FROM USER WHERE username=?;");
	$repeat->bind_param("s",$_GET["new_username"]);
	$repeattest = $repeat->execute();
	$repeatSet = $repeat->get_result();
	//Check Username and Password empty or not,if empty,cannot create
	if ((empty($_GET["new_username"])==True) && (empty($_GET["new_password"])==True)){
		echo("Username and Password is empty,please input data!!!");
	}
	elseif(empty($_GET["new_username"])==True){
		echo("Please input username!!!");
	}
	elseif(empty($_GET["new_password"])==True){
		echo("Please input password!!!");
	}
	else{
		if ($repeatSet->num_rows > 0){
			//username existed
			echo ("User_Name existed!!!");
		}
		else{
			$result = $create->execute();
			$resultSet = $create->get_result();
		}
?>

<?php
	//Check Create Success or not
		$check = $conn->prepare("SELECT USERNAME,Password,First_name,Last_name FROM USER WHERE username = ? and password = sha2(concat(?,@salt),256) and First_name = ? and Last_name = ? ");
		$check->bind_param("ssss",$_GET["new_username"],$_GET["new_password"],$_GET["First_name"],$_GET["Last_name"]);
		//execute the prepared statement
		$result = $check->execute();
		$resultSet = $check->get_result();
		if (!$resultSet) {
		trigger_error('Invalid query: ' . $conn->error);
		}

		if ($resultSet->num_rows > 0) {
			echo ("Create Success");
		}
		else{
			echo("Create failed");
	}}
	$conn->close();
}
?>






