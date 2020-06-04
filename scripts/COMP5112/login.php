<h1>Login</h1>
<form action="login.php" method="GET">
Username:
<input type="text" name="user_name"><br>
Password:
<input type="text" name="password"><br>
<input type="submit" value = "submit">
</form>

<?php
	//Link DB
	session_start();
	$_SESSION["DBSN"] = $servername = "localhost:3306";
	$_SESSION["DBUN"] = $username = "root";
	$_SESSION["DBPW"] = $password = "ku89888786";
	$_SESSION["DBname"] = $DBname = "19089654g";
	// Create connection
	$conn = new mysqli($servername, $username, $password, $DBname);
	// Check connection
   if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	if (isset($_GET["user_name"]) && isset($_GET["password"])) {
		//The plaintext of the user’s password should not be stored in the database. Instead,
		//the password should be concatenated with a randomly generated string and hashed
		//using the MySQL 8.0 SHA2 function) and stored in your database table. The salt will
		//be stored in the database for password verification during the login process.

		$user = $_GET["user_name"];
		$pw = $_GET["password"];	
		//Get salt for the specify user.
		$salt = $conn->prepare("set @usersalt = (select salt from USER where username = ?);");
		$salt->bind_param("s",$user);
		$saltresult = $salt->execute();
		$conn->query($saltresult);
		
		//Login User provide his username and password.
		$stmt = $conn->prepare("SELECT USERNAME,Password FROM USER WHERE username = ? and 
			password = sha2(concat(?,@usersalt),256)");
		$stmt->bind_param("ss",$user,$pw);
		$result = $stmt->execute();
		$resultSet = $stmt->get_result();

		if (!$resultSet) {
		trigger_error('Invalid query: ' . $conn->error);
		}
		if ($resultSet->num_rows > 0) {
			$row = $resultSet->fetch_assoc();
			// Successful login,redirected to the main.php,Send param user to be the ID  
			$_SESSION["username"] = $row['USERNAME'];
			header("Location:main.php");
			
		}
	
		
		else {
			// login failed 
			echo "<h2>Invalid username/password!</h2>";
		}
	?>
	<?php
		$conn->close();
	}
//Hyperlink to the create_user.
?>
<p><a href="create_user.php">Create User</p>
