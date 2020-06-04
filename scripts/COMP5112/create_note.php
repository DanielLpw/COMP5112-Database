<h1>Create_note</h1>
<form action="create_note.php" method="GET">
Title *:
<input type="text" name="title"><br>
Content *:
<br>
<textarea type="text" name="content" rows="20" cols="60"></textarea>
<br>
Encryption *:
<input type="radio" name="encryption" value=1>Yes
<input type="radio" name="encryption" value=0>No
<br>
Cipher:
<input type="text" name="cipher"><br>
<input type="submit">
</form>

<?php
	session_start();
	$servername = $_SESSION["DBSN"];
	$username = $_SESSION["DBUN"];
	$password = $_SESSION["DBPW"];
	$DBname = $_SESSION["DBname"];
	$user = $_SESSION["username"];
	// Create connection
	$conn = new mysqli($servername, $username, $password, $DBname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
		}
?>
	
<?php
	//1.The user can input the title and content of the notes
if (isset($_GET["title"]) && isset($_GET["content"])) {
	$_GET['encryption'] = empty($_GET['encryption']) ? '' : $_GET['encryption'];
	//Check Title, Content is null or not.验是否空
	if ((empty($_GET["title"])==True) && (empty($_GET["content"])==True)){
		echo("Please input Title and Content!!!<br>");
	}
	elseif ((empty($_GET["title"])==True)){
		echo("Please input Title!!!<br>");
	}
	elseif ((empty($_GET["content"])==True)){
		echo("Please input Content!!!<br>");
	}
	else{
		//choose encrypted,save data use encrypted
		//2.The user can select whether the notes is encrypted. 
		//2.If so, the user should provide a password to encrypt the note’s content
		if($_GET["encryption"]== 1){
			echo ("encrypted");
			//When encrypted, Check cipher null or not.
			if((empty($_GET["cipher"])==True)){
				echo("Please input cipher");
			}
			else{
				//Encryption and save   
				//echo("Send note to DB,to be continued");
				//SET CBC 128 MODE( For encryption functions, you may assume that the AES
				//encryption/decryption with CBC mode is used.)
				$sql = "SET block_encryption_mode = 'aes-128-cbc'";
				$conn->query($sql);
				//Random generate init_vector
				$sql = "SET @init_vector = RANDOM_BYTES(16)";
				$conn->query($sql);
				$createnote = $conn->prepare("INSERT INTO NOTE(title,content,username,encrypted,init_vector) 
				VALUES(?,HEX(AES_ENCRYPT(?,?,@init_vector)),?,1,@init_vector)");
				$createnote->bind_param("ssss",$_GET["title"],$_GET["content"],$_GET["cipher"],$user);
				$result = $createnote->execute();
				$resultSet = $createnote->get_result();
			}
		}
		//do not choose encrypted,to create note 
		else{
			echo ("no encrypted");
			$createnote = $conn->prepare("INSERT INTO NOTE (title, content,username) VALUES (?,?,?)");
			$createnote->bind_param("sss",$_GET["title"],$_GET["content"],$user);
			$result = $createnote->execute();
			$resultSet = $createnote->get_result();
		}
		echo ("</br>Create Success");
	}
	$conn->close();
}

?>
<input type="button" value="Return" onclick="window.location.href='main.php'"/>
