<?php
	//param.User get from the login.php   该页面将在用户名的顶部（页面顶部）显示用户名，其后是所有笔记的标题
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
	//1.The page will show at the top the user’s username (on the top of the page of the page) 
	$user = $_SESSION["username"];
	echo("Username:".$user."<br/>");
?>

<h1>Note</h1>    
<?php
	//If do not input anything of the key_word, the keyword will be null.So,it will display all the note under this user.
	$key_word="";
	if(isset($_GET['key_word'])){
		$key_word = $_GET['key_word'];
	}
?>



<?php	
	//1.followed by the titles of all his notes and for each note, whether it is encrypted
	//1.The notes should be sorted in reverse chronological order from the latest to the earliest.
	$stmt = $conn->prepare("SELECT * FROM NOTE WHERE username = ? ORDER BY note_id DESC");
	$stmt->bind_param("s",$user);
    $result = $stmt->execute();
	$resultSet_all = $stmt->get_result();
?>

<?php	
	
	if(isset($_GET['key_word'])&& empty($key_word)==False){
		$sql = "SET block_encryption_mode = 'aes-128-cbc'";
		$conn->query($sql);
		echo ("Key word: $key_word<br>");
		echo ("With the password:$_GET[cipher]</br>");
		if ($resultSet_all->num_rows > 0) {
			while($row_all = $resultSet_all->fetch_assoc()) {
				//4.The page should provide a textbox which allow user to specify a
				//string for searching the notes based on the notes’ content 
				//4.and the page will only display the titles of the notes containing the
				//keywords; 
				$select_keyword = $conn->prepare("SELECT * FROM NOTE WHERE username = ? and note_id = ? and CONTENT LIKE concat('%',?,'%')");
				$select_keyword->bind_param("sss",$user,$row_all['note_id'],$key_word);
				$result = $select_keyword->execute();
				$resultSet_keyword = $select_keyword->get_result();
				//4.Optionally, the user may provide a password so that
				//notes encrypted with the corresponding password may also be
				//included in the search result. 
				$encry_content = $conn->prepare("SET @MIWEN = (select note.content from note where note_id = ?)");
				$encry_content->bind_param("s",$row_all['note_id']);
				$encry_contentSET = $encry_content->execute();
				$conn->query($encry_contentSET);
				
				$init_vector = $conn->prepare("SET @init_vector = (select note.init_vector from note where note_id =?);");
				$init_vector->bind_param("s",$row_all['note_id']);
				$init_vectorSET = $init_vector->execute();
				$conn->query($init_vectorSET);
					
				$decrypted = $conn->prepare("SELECT CONVERT (AES_DECRYPT(UNHEX(@MIWEN),?,@init_vector) USING utf8)
				WHERE CONVERT (AES_DECRYPT(UNHEX(@MIWEN),?,@init_vector) USING utf8) like concat('%',?,'%')");
				$decrypted->bind_param("sss",$_GET['cipher'],$_GET['cipher'],$key_word);
				$decryptedSET = $decrypted->execute();
				$resultSet_note = $decrypted->get_result();
				$decryptednote = $resultSet_note->fetch_assoc();
				if($decryptednote!=null){
					if($decryptednote['CONVERT (AES_DECRYPT(UNHEX(@MIWEN),?,@init_vector) USING utf8)']!=null){
						?>

					<a href = "<?php echo"view.php?note_id=".$row_all['note_id']?>">
				<?php
				
						printf("Title: $row_all[title] </br><a>Create Time: $row_all[create_time] Note_ID: $row_all[note_id]    ");	
						printf("Encryption: Yes</br></br>");
					}
				}
			
				elseif ($resultSet_keyword->num_rows > 0) {
			// output data of each row
						while($row_all = $resultSet_keyword->fetch_assoc()) {
							//2.The user can click the title of the note to view the notes (by
							//2.directing the user to view.php)
							//echo ("With the content:$_GET[key_word]</br>");
			?>
<a href = "<?php echo"view.php?note_id=".$row_all['note_id']?>">
			<?php
							printf("Title: $row_all[title] </br><a>Create Time: $row_all[create_time] Note_ID: $row_all[note_id]    ");	
							if($row_all['encrypted']==True){
								printf("Encryption: Yes</br></br>");
							}
							else
							{
								printf("Encryption: No</br></br>");
							}
			
						}
					}				
				}
			}
	
		}
	else{
		$resultSet = $resultSet_all;
		if ($resultSet->num_rows > 0) {
			//output data of each row
			echo ("All the note of user '$user':</br>");
			while($row = $resultSet->fetch_assoc()) {
				//2.The user can click the title of the note to view the notes (by
				//2.directing the user to view.php)
			?>
<a href = "<?php echo"view.php?note_id=".$row['note_id']?>">
			<?php
				printf("Title: $row[title] </br><a>Create Time: $row[create_time]  ");	
				if($row['encrypted']==True){
					printf("Encryption: Yes</br></br>");
				}
				else
				{
					printf("Encryption: No</br></br>");
				}
			
			}
		}
	}

?>
<form action="main.php" method="get">
You can type the keyword of the notes' content in this textbox:
</br>
<input type="text" name="key_word"><br>
You can input the password of the note in this textbox:
</br>
<input type="text" name="cipher"><br>
<input type="submit" value="search">
</form>




<input type="button" value="Create Note" onclick="window.location.href='create_note.php'"/>
<input type="button" value="Login out" onclick="window.location.href='login.php'"/>