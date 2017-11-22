<?php
	
	# Function to initialize database connection
	function databaseConnection() {
		$url = parse_url(getenv("CLEARDB_DATABASE_URL"));

		$server = $url["host"];
		$username = $url["user"];
		$password = $url["pass"];
		$db = substr($url["path"], 1);

		$conn = new mysqli($server, $username, $password, $db);

		# Return $conn
		if ($conn -> connect_error)
		{
			return null;
		}
		else
		{
			return $conn;
		}
	}

	# -------------- Services --------------

	# Attempt to create an exam
	function attemptCreateExam($creator, $name, $examKey) {
		# Obtain null or actual reference of connection
		$connection = databaseConnection();

		# If connection exists...
		if($connection != null) {

			# Query forumlation
			$sql = "INSERT INTO Exams(teacher_ID, name, examKey) VALUES ('$creator', '$name', '$examKey')";

			# Query execution
			$examCreated = $connection -> query($sql);

			# If user is created successfully...
			if ($examCreated === true) {

				$response = array("MESSAGE" => "SUCCESS");
				
				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
			# Return an error message if database insertion failed
			else {
				return array("MESSAGE" => "500");
			}
		}
		# Return an error message if connection is null
		else {
			return array("MESSAGE" => "500");
		}
	}

	# Attempt to create a question
	function attemptCreateQuestion($examID, $question, $answer, $wrong1, $wrong2, $wrong3) {
		# Obtain null or actual reference of connection
		$connection = databaseConnection();

		# If connection exists...
		if($connection != null) {

			# Query forumlation
			$sql = "INSERT INTO Questions(exam_ID, question, answer, wrong1, wrong2, wrong3)
					VALUES ('$examID', '$question', '$answer', '$wrong1', '$wrong2', '$wrong3')";

			# Query execution
			$questionCreated = $connection -> query($sql);

			# If user is created successfully...
			if ($questionCreated === true) {

				$response = array("MESSAGE" => "SUCCESS");
				
				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
			# Return an error message if database insertion failed
			else {
				return array("MESSAGE" => "500");
			}
		}
		# Return an error message if connection is null
		else {
			return array("MESSAGE" => "500");
		}
	}

	# Attempt to create new user
	function attemptCreateTeacher($uName, $uPassword) {

		# Obtain null or actual reference of connection
		$connection = databaseConnection();

		# If connection exists...
		if($connection != null) {

			# Query forumlation
			$sql = "INSERT INTO Teachers(username, passwrd)
					VALUES ('$uName', '$uPassword')";

			# Query execution
			$userCreated = $connection -> query($sql);

			# If user is created successfully...
			if ($userCreated === true) {

				$response = array("MESSAGE" => "SUCCESS");
				
				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
			# Return an error message if database insertion failed
			else {
				return array("MESSAGE" => "500");
			}
		}
		# Return an error message if connection is null
		else {
			return array("MESSAGE" => "500");
		}
	}

	# Attempt to fetch questions for a given examID
	function attemptFetchQuestions($examID) {
		# Obtain null or actual reference of connection
		$connection = databaseConnection();

		# If connection exists...
		if($connection != null) {

			# Query forumlation
			$sql = "SELECT ID, exam_ID, question, answer, wrong1, wrong2, wrong3
					FROM Questions
					WHERE exam_ID = '$examID';";

			# Query execution
			$queryResult = $connection -> query($sql);

			$questions = array();

			# If information was fetch successfully...
			if ($queryResult != null) {
				if ($queryResult -> num_rows > 0) {

					# Fetching various questions if any
					while ($row = $queryResult -> fetch_assoc()) {
						$questions[] = array("ID" => $row["ID"],
											 "question" => $row["question"],
										 	 "answer" => $row["answer"],
										 	 "wrong1" => $row["wrong1"],
										 	 "wrong2" => $row["wrong2"],
										 	 "wrong3" => $row["wrong3"]);
					}
				}

				$response = array("MESSAGE" => "SUCCESS",
								  "questions" => $questions);

				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
			# Return an error message if query failed
			else {
				return array("MESSAGE" => "500");
			}
		}
		# Return an error message if connection is null
		else {
			return array("MESSAGE" => "500");
		}
	}

	# Attempt to login function
	function fetchloginInfo($userName) {
		# Obtain null or actual reference of connection
		$connection = databaseConnection();

		# If connection exists...
		if($connection != null) {

			# Query forumlation
			$sql = "SELECT ID, passwrd
					FROM Teachers
					WHERE username = '$userName'";

			# Query execution
			$queryResult = $connection -> query($sql);

			# If query is successful
			if($queryResult -> num_rows > 0) {
				# Fetching if single result
				$user = $queryResult -> fetch_assoc();
				$response = array("ID" => $user["ID"],
								  "MESSAGE" => "SUCCESS",
								  "PASS_ENC" => $user["passwrd"]);
				
				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
			# Return an error message for invalid credentials
			else {
				$connection -> close();
				return array("MESSAGE" => "406");
			}
		}
		# Return an error message if connection is null
		else {
			return array("MESSAGE" => "500");
		}
	}

	# Attempt to fetch exam header for a given exam ID
	function fetchExamHeader($examID) {
		# Create a database connection
		$connection = databaseConnection();

		if($connection != null) {
			# Query forumaltion
			$sql = "SELECT Ex.ID AS EID, Te.ID, Te.username AS author, name, teacher_ID
					FROM Exams AS Ex INNER JOIN Teachers AS Te
					ON teacher_ID = Te.ID
					WHERE Ex.ID = '$examID'";

			# Query execution
			$queryResult = $connection -> query($sql);

			# If successfull...
			if($queryResult != null) {

				if($queryResult -> num_rows > 0) {
					$row = $queryResult -> fetch_assoc();

					$response = array("MESSAGE" => "SUCCESS",
									  "title" => $row["name"],
									  "author" => $row["author"]);

					$connection -> close();
					return $response;
				}

			}
			# Return an error message if query failed
			else {
				return array("MESSAGE" => "500");
			}
		}
		# Return an error message if query failed
		else {
			return array("MESSAGE" => "500");
		}
	}

	# Attempt to fetch exams
	function fetchExams($currentUID, $search) {
		# Obtain null or actual reference of connection
		$connection = databaseConnection();

		# If connection exists...
		if($connection != null) {

			# Query forumlation
			$sql = "SELECT ID, teacher_ID, name, examKey
					FROM exams
					WHERE teacher_ID = '$currentUID'
					AND name REGEXP '^$search'";

			# Query execution
			$queryResult = $connection -> query($sql);

			$exams = array();

			# If information was fetch successfully...
			if ($queryResult != null) {
				if ($queryResult -> num_rows > 0) {

					# Fetching various exams if any
					while ($row = $queryResult -> fetch_assoc()) {
						$exams[] = array("id" => $row["ID"],
										 "name" => $row["name"],
										 "key" => $row["examKey"]);
					}
				}

				# Add questions...

				$response = array("MESSAGE" => "SUCCESS",
								  "exams" => $exams);

				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
			# Return an error message if query failed
			else {
				return array("MESSAGE" => "500");
			}
		}
		# Return an error message if connection is null
		else {
			return array("MESSAGE" => "500");
		}
	}

	# Get profile information from a user
	function fetchUsername($currentUID) {
		# Obtain null or actual reference of connection
		$connection = databaseConnection();

		# If connection exists...
		if($connection != null) {

			# Query forumlation
			$sql = "SELECT ID, username
					FROM Teachers
					WHERE ID = '$currentUID'";

			# Query execution
			$queryResult = $connection -> query($sql);

			# If information was fetch successfully...
			if ($queryResult -> num_rows > 0) {

				# Fetching single result
				$row = $queryResult -> fetch_assoc();
				$response = array("MESSAGE" => "SUCCESS",
								  "username" => $row["username"]);
				
				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
			# Return an error message if query failed
			else {
				return array("MESSAGE" => "500");
			}
		}
		# Return an error message if connection is null
		else {
			return array("MESSAGE" => "500");
		}
	}

	# Verify that a exam key exists
	function getExamID($ExamKey) {
		# Obtain null or actual reference of connection
		$connection = databaseConnection();

		# If connection exists...
		if($connection != null) {

			# Query forumlation
			$sql = "SELECT ID, examKey
					FROM Exams
					WHERE examKey = '$ExamKey'";

			# Query execution
			$result = $connection -> query($sql);

			# If exam exists...
			if ($result -> num_rows > 0) {

				# Fetching single result
				$row = $result -> fetch_assoc();
				# Return exam id
				$response = array("MESSAGE" => "SUCCESS",
								  "EID" => $row["ID"]);
				
				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
		}
		# Return an error message if connection is null
		else {
			return array("MESSAGE" => "500");
		}
	}

	# Get user's id in the database
	function getUserID($uName) {
		# Obtain null or actual reference of connection
		$connection = databaseConnection();

		# If connection exists...
		if($connection != null) {

			# Query forumlation
			$sql = "SELECT ID
					FROM Teachers
					WHERE username = '$uName'";

			# Query execution
			$userId = $connection -> query($sql);

			# If user is created successfully...
			if ($userId -> num_rows > 0) {

				# Fetching single result
				$row = $userId -> fetch_assoc();
				$response = array("MESSAGE" => "SUCCESS",
								  "UID" => $row["ID"]);
				
				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
			# Return an error message if database query
			else {
				return array("MESSAGE" => "500");
			}
		}
		# Return an error message if connection is null
		else {
			return array("MESSAGE" => "500");
		}
	}

	# Verify that a user exists
	function verifyUserExistence($uName) {
		# Obtain null or actual reference of connection
		$connection = databaseConnection();

		# If connection exists...
		if($connection != null) {

			# Query forumlation
			$sql = "SELECT username
					FROM Teachers
					WHERE username = '$uName'";

			# Query execution
			$result = $connection -> query($sql);

			# If user didn't exist...
			if ($result -> num_rows == 0) {

				# Return no as an answer
				$response = array("MESSAGE" => "NO");
				
				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
			# Return yes if user already existed
			else {
				$response = array("MESSAGE" => "YES");
				
				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
		}
		# Return an error message if connection is null
		else {
			return array("MESSAGE" => "500");
		}
	}

	# Verify that a exam key exists
	function verifyExamKeyExistence($ExamKey) {
		# Obtain null or actual reference of connection
		$connection = databaseConnection();

		# If connection exists...
		if($connection != null) {

			# Query forumlation
			$sql = "SELECT examKey
					FROM Exams
					WHERE examKey = '$ExamKey'";

			# Query execution
			$result = $connection -> query($sql);

			# If user didn't exist...
			if ($result -> num_rows == 0) {

				# Return no as an answer
				$response = array("MESSAGE" => "NO");
				
				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
			# Return yes if user already existed
			else {
				$response = array("MESSAGE" => "YES");
				
				# Cerrar la conexion a base de datos
				$connection -> close();
				# Regresar respuesta
				return $response;
			}
		}
		# Return an error message if connection is null
		else {
			return array("MESSAGE" => "500");
		}
	}

?>