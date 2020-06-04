<h1>View</h1>

<?php
	//Get note_ID from the main.php
	session_start();
	if(isset($_GET['note_id'])){
		$_SESSION["NOTE_ID"] = $_GET['note_id'];
	}
	$note_id = $_SESSION["NOTE_ID"];
	echo("Note_ID:".$note_id."<br/>");
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
	//1.The user is only allowed to view notes created by him
	//E.g. To view the note with note_ID 1234, the user will make
	//a HTTP request to view.php?note_ID=1234 and the user
	//may only view the note if he/she is the creator of the note.
	$user = $_SESSION["username"];
	$checkuser = $conn->prepare("SELECT username FROM NOTE WHERE note_id=?;");
	$checkuser->bind_param("s",$note_id);
	$checkSET = $checkuser->execute();
	$checkSETresult = $checkuser->get_result();
	$usertest = $checkSETresult->fetch_assoc();
	echo ("$user</br>");
	//check the creator of the note is the logined user or not
	if($usertest['username']==$user){
		//2.The page will display the title and the content of the note selected in main.php
		echo("User Right!!</br>");
		$viewnote = $conn->prepare("SELECT * FROM NOTE WHERE note_id = ?");
		$viewnote->bind_param("s",$note_id);
		$view = $viewnote->execute();
		$resultSet = $viewnote->get_result();
		$note = $resultSet->fetch_assoc();
		printf("Title:$note[title]</br>");
		//2.If the page is encrypted
		if($note['encrypted']==1){
			//2.the application should notify that the note is encrypted 
			echo("This Note is private.</br>");
			//2.and display a base 64 encoded version of the encrypted text.
			$encode = base64_encode($note['content']);
			echo ("Content(Base64): $encode.'<br>'");
			
			//3.The system should provide a textbox to allow the user to provide
			//3.a password to decrypted the note.
			echo('<form action="view.php" method="GET">
				  Password of the note:
				  <input type="text" name="cipher"><br>
				  <input type="submit" value="Decrypt">
				  </form>');
			//SET CBC MODE( For encryption functions, you may assume that the AES
			//encryption/decryption with CBC mode is used.)
			if(isset($_GET["cipher"])){
				$sql = "SET block_encryption_mode = 'aes-128-cbc'";
				$conn->query($sql);
				//SET parameter
				$encry_content = $conn->prepare("SET @MIWEN = (select note.content from note where note_id = ?)");
				$encry_content->bind_param("s",$note_id);
				$encry_contentSET = $encry_content->execute();
				$conn->query($encry_contentSET);
				
				$init_vector = $conn->prepare("SET @init_vector = (select note.init_vector from note where note_id =?);");
				$init_vector->bind_param("s",$note_id);
				$init_vectorSET = $init_vector->execute();
				$conn->query($init_vectorSET);
				
				//3-1.The password used for encryption/decryption may not be the same password for login.
				$decrypted = $conn->prepare("SELECT CONVERT (AES_DECRYPT(UNHEX(@MIWEN),?,@init_vector) USING utf8)");
				$decrypted->bind_param("s",$_GET['cipher']);
				//$decrypted = $conn->prepare("SELECT CONVERT (AES_DECRYPT(UNHEX(?),?,?) USING utf8)");
				//$decrypted->bind_param("ssb",$note['content'],$_GET['cipher'],$note['init_vector']);
				$decryptedSET = $decrypted->execute();
				$resultSet_note = $decrypted->get_result();
				$decryptednote = $resultSet_note->fetch_assoc();
				//echo("1: $note[content] 2:$_GET[cipher] 3:$note[init_vector]");
				//print_r($decryptednote);
				//printf($decryptednote['CONVERT (AES_DECRYPT(UNHEX(@MIWEN),?,@init_vector) USING utf8)']);
				
				//3.The plaintext of the note will be shown if the password is correct.
				//When password correct, the select result is not null.
				if($decryptednote['CONVERT (AES_DECRYPT(UNHEX(@MIWEN),?,@init_vector) USING utf8)']!=null){
					echo("Password is right.</br>The content of the note is:</br>");
					printf($decryptednote['CONVERT (AES_DECRYPT(UNHEX(@MIWEN),?,@init_vector) USING utf8)']);
					echo("</br>");
				}
				else{
					//3-2.The application should warn the user if the password fails to 
					//3-2.properly decrypt the encrypted text.
					echo("Password wrong.</br>");
				}
			}
		}
		else{
			//Show the content when this note is not encrypted.
			printf("Content:$note[content]</br>");
			
		}
	}
	else{
		echo("User error!!!");
	
	}
?>
<input type="button" value="Return" onclick="window.location.href='main.php'"/>

