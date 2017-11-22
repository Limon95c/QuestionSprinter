<?php
	# Header files
	header('Content-type: application/json');
	header('Accept: application/json');
	require_once __DIR__ . '/dataLayer.php';
	
	# Save action in a variable
	$action = $_POST["action"];

	# Action filtering
	switch ($action) {

		case "LOAD_EXAMS":
			loadExamsService();
			break;

		case "LOAD_QUESTIONS":
			loadQuestionsService();
			break;

		case "LOGIN":
			loginService();
			break;

		case "LOGOUT":
			logoutService();
			break;

		case "NEW_EXAM":
			newExamService();
			break;

		case "NEW_QUESTION":
			newQuestionService();
			break;		

		case "REDIRECT":
			redirectService();
			break;

		case "REGISTER":
			registerService();
			break;

		case "SET_EXAM_HEADER":
			setExamHeader();
			break;

		case "START_EXAM":
			startExamService();
			break;

		case "USER":
			userService();
			break;

		default:
			# code...
			break;
	}

	# Error function
	function genericErrorFunction($errorCode) {
		switch($errorCode) {
			case "500" : header("HTTP/1.1 500 Bad connection, portal down");
						 die("The server is down, we couldn't stablish the data base connection.");
						 break;

			case "406" : header("HTTP/1.1 406 Usuario o contraseña inválida.");
						 die("Wrong credentials provided.");
						 break;

			case "409" : header("HTTP/1.1 409 Username provided already exists, please provide a new one.");
						die("El nombre de usuario ya existe.");
						break;
			default:
			# code...
			break;
		}
	}

	# Encryption function
	function getEncryptedPassword($password) {
		# For encryption purposes
		$key = pack('H*', "B374A26A71490437AA024E4FADD5B497FDFF1A8EA6FF12F6FB65AF2720B59CCF");
		$iv = pack('H*', "7E892875A52C59A3B588306B13C31FBD");

		$password_enc = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv);
		$password_enc = $password_enc . ':' . $iv;

	    return $password_enc;
	}

	# Decryption function
	function getDecryptedPassword($encryptedPassword) {
		# For decryption purposes
		$key = pack('H*', "B374A26A71490437AA024E4FADD5B497FDFF1A8EA6FF12F6FB65AF2720B59CCF");

		$parts = explode(':', $encryptedPassword);
		$pass_dec = openssl_decrypt($parts[0], 'aes-256-cbc', $key, 0, $parts[1]);

		return $pass_dec;
	}

	# Function that fills the questions for each received exam
	function addQuestions($examsResponse) {
		if(!empty($examsResponse['exams'])) {
			foreach($examsResponse['exams'] as &$exam) {
				$questionsArray = array();

				# Get the questions related to that exam
				$questionsFetched = attemptFetchQuestions($exam['id']);

				if($questionsFetched["MESSAGE"] == "SUCCESS") {
					$exam['questions'] = $questionsFetched['questions'];
				}
				else {
					genericErrorFunction($questionsFetched["MESSAGE"]);
				}
			}
		}

		return $examsResponse;
	}

	# -------------- Services --------------

	function loadExamsService() {
		session_start();

		# Fetch required data
		$currentUID = $_SESSION['current'];
		$search = $_POST['search'];

		# Launch data layer execution attempt
		$examsFetched = fetchExams($currentUID, $search);
			
		# If the message is success...
		if($examsFetched["MESSAGE"] == "SUCCESS") {
				
			# Add the questions to each exam
			$examsFetched = addQuestions($examsFetched);

			# Return exams data
			echo json_encode($examsFetched);
		}
		# If attempt failed...
		else {
			# Error message
			genericErrorFunction($examsFetched["MESSAGE"]);
		}
	}

	function loadQuestionsService() {
		session_start();

		# Fetch required data
		$examID = $_SESSION['exam'];

		# Launch data layer execution attempt
		$questionsFetched = attemptFetchQuestions($examID);

		# If the message is success...
		if($questionsFetched["MESSAGE"] == "SUCCESS") {
			
			# New questions array to fill
			$responseQuestions = array();

			if(count($questionsFetched["questions"]) > 0) {
				shuffle($questionsFetched["questions"]);

				foreach ($questionsFetched["questions"] as &$q) {

					# Save answers in separate array to shuffle them
					$answers = array();
					$answers[] = array("answer" => $q["answer"],
									   "correct" => "true");
					$answers[] = array("answer" => $q["wrong1"],
									   "correct" => "false");
					$answers[] = array("answer" => $q["wrong2"],
									   "correct" => "false");
					$answers[] = array("answer" => $q["wrong3"],
									   "correct" => "false");
					shuffle($answers);

					# Insert question and answers into new questions array
					$responseQuestions[] = array("ID" => $q["ID"],
												 "question" => $q["question"],
												 "answers" => $answers);
				}
			}

			$response = array("MESSAGE" => "SUCCESS",
							  "questions" => $responseQuestions);

			# Return shuffled questions and answers
			echo json_encode($response);
		}
		# If attempt failed...
		else {
			# Error message
			genericErrorFunction($questionsFetched["MESSAGE"]);
		}
	}
	
	function loginService() {

		# Fetch data in local variables
		$uName = $_POST["uName"];
		$uPassword = $_POST["uPassword"];

		# Launch data layer execution attempt
		$fetchloginInfoOutcome = fetchloginInfo($uName);

		# If attempt is successful...
		if($fetchloginInfoOutcome["MESSAGE"] == "SUCCESS") {

			# Decrypt real password and compare with input
			$pass_dec = getDecryptedPassword($fetchloginInfoOutcome["PASS_ENC"]);

			if($pass_dec === $uPassword) {

				# Start and set session variables
				session_start();
				$_SESSION['current'] = $fetchloginInfoOutcome["ID"];

				# Return successful message to presentation layer
				$response = array("result" => "$uName logged in successfully");
				echo json_encode($response);
			}
			else {
				# Wrong credentials error message
				genericErrorFunction("406");
			}
		}
		# If attempt failed...
		else {
			# Error message
			genericErrorFunction($fetchloginInfoOutcome["MESSAGE"]);
		}
	}
	
	function logoutService() {
		session_start();
		unset($_SESSION['current']);
		unset($_SESSION['exam']);
		session_destroy();
		setcookie("PHPSESSID", "", time() - 1, "/", "", 0);

		# Return successful message to presentation layer
		$response = array("result" => "SUCCESS");
		echo json_encode($response);
	}
	
	function newExamService() {

		session_start();

		# Fetch required data
		$creator = $_SESSION['current'];
		$name = addslashes($_POST["name"]);

		# Get random public string
		$seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
		shuffle($seed);
		$rand = '';
		foreach (array_rand($seed, 10) as &$k) {
			$rand .= $seed[$k];
		}

		# Verify that desired username doesn't exists
		$verificationOutcome = verifyExamKeyExistence($rand);

		while($verificationOutcome["MESSAGE"] != "NO") {
			# Get random public string
			$seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
			shuffle($seed);
			$rand = '';
			foreach (array_rand($seed, 10) as $k) {
				$rand .= $seed[$k];
			}

			# Verify that desired username doesn't exists
			$verificationOutcome = verifyExamKeyExistence($rand);
		}

		# Attempt to create a new ocean
		$newExamOutcome = attemptCreateExam($creator, $name, $rand);

		# If it was successful...
		if ($newExamOutcome['MESSAGE'] == "SUCCESS") {
			$response = array("MESSAGE" => "SUCCESS");
					
			echo json_encode($response);
		}
		# If insertion fails...
		else {
			# Error message
			genericErrorFunction($newExamOutcome["MESSAGE"]);
		}
	}

	function newQuestionService() {
		session_start();

		# Fetch required data
		$examID = addslashes($_POST['exam_ID']);
		$question = addslashes($_POST['question']);
		$answer = addslashes($_POST['answer']);
		$wrong1 = addslashes($_POST['wrong1']);
		$wrong2 = addslashes($_POST['wrong2']);
		$wrong3 = addslashes($_POST['wrong3']);

		# Attempt to create a new question
		$newQuestionOutcome = attemptCreateQuestion($examID, $question, $answer, $wrong1, $wrong2, $wrong3);

		# If it was successful...
		if ($newQuestionOutcome['MESSAGE'] == "SUCCESS") {
			$response = array("MESSAGE" => "SUCCESS");
					
			echo json_encode($response);
		}
		# If insertion fails...
		else {
			# Error message
			genericErrorFunction($newQuestionOutcome["MESSAGE"]);
		}
	}
	
	function redirectService() {

		$context = $_POST["context"];

		$response = array("shouldRedirect" => false);

		session_start();
		
		if($context === "home") {
			if(isset($_SESSION['current'])) {
				$response = array("shouldRedirect" => false);
			}
			else if(isset($_SESSION['exam'])) {
				$response = array("shouldRedirect" => true,
								  "where" => "exam");
			}
			else {
				$response = array("shouldRedirect" => true,
								  "where" => "index");
				session_destroy();
				setcookie("PHPSESSID", "", time() - 1, "/", "", 0);
			}
		}
		else if($context === "index"){
			if(isset($_SESSION['current'])) {
				$response = array("shouldRedirect" => true,
								  "where" => "home");
			}
			else if(isset($_SESSION['exam'])) {
				$response = array("shouldRedirect" => true,
								  "where" => "exam");
			}
			else {
				$response = array("shouldRedirect" => false);
				session_destroy();
				setcookie("PHPSESSID", "", time() - 1, "/", "", 0);
			}
		}
		else if($context === "exam") {
			if(isset($_SESSION['current'])) {
				$response = array("shouldRedirect" => true,
								  "where" => "home");
			}
			else if(isset($_SESSION['exam'])) {
				$response = array("shouldRedirect" => false);
			}
			else {
				$response = array("shouldRedirect" => true,
								  "where" => "index");
				session_destroy();
				setcookie("PHPSESSID", "", time() - 1, "/", "", 0);
			}
		}

		echo json_encode($response);
	}
	
	function registerService() {

		$uName = $_POST["uName"];
		$uPassword = $_POST["uPassword"];

		# Verify that desired username doesn't exists
		$verificationOutcome = verifyUserExistence($uName);

		if($verificationOutcome["MESSAGE"] == "NO") {

			# Encrypt password
			$pass_enc = getEncryptedPassword($uPassword);

			# Attempt to create new user
			$newTeacherOutcome = attemptCreateTeacher($uName, $pass_enc);

			if($newTeacherOutcome["MESSAGE"] == "SUCCESS") {

				# Get new user's id
				$getIdOutcome = getUserID($uName);

				if($getIdOutcome["MESSAGE"] == "SUCCESS") {

					session_start();

					$_SESSION['current'] = $getIdOutcome["UID"];

					$response = array("MESSAGE" => "Nuevo maestro creado!");
					
					echo json_encode($response);
				}
				else {
					# Error message
					genericErrorFunction($getIdOutcome["MESSAGE"]);
				}
			}
			# If insertion fails...
			else {
				# Error message
				genericErrorFunction($newUserOutcome["MESSAGE"]);
			}
		}
		# If user exists or query fails...
		else {
			# Error message
			genericErrorFunction($verificationOutcome["MESSAGE"]);
		}
	}

	function setExamHeader() {
		session_start();

		#Fetch required data
		$examID = $_SESSION['exam'];

		# Attempt to database query
		$fetchHeaderOutcome = fetchExamHeader($examID);

		if($fetchHeaderOutcome["MESSAGE"] = "SUCCESS") {

			echo json_encode($fetchHeaderOutcome);
		}
		else {
			genericErrorFunction($fetchHeaderOutcome["MESSAGE"]);
		}
	}

	function startExamService() {
		# Fetch data in local variables
		$examCode = $_POST["code"];

		# Launch data layer execution attempt
		$verifyExamExistsOutcome = verifyExamKeyExistence($examCode);

		# If attempt is successful...
		if($verifyExamExistsOutcome["MESSAGE"] == "YES") {

			# Get exam ID and set it as a cookie
			$getUserIDOutcome = getExamID($examCode);

			if($getUserIDOutcome["MESSAGE"] == "SUCCESS") {
				# Start and set session variables
				session_start();
				$_SESSION['exam'] = $getUserIDOutcome["EID"];

				# Return successful message to presentation layer
				$response = array("MESSAGE" => "SUCCESS");
				echo json_encode($response);
			}
			else {
				genericErrorFunction($getUserIDOutcome["MESSAGE"]);
			}
		}
		else if($verifyExamExistsOutcome["MESSAGE"] == "NO") {
			$response = array("MESSAGE" => "NOT_FOUND");
			echo json_encode($response);
		}
		# If attempt failed...
		else {
			# Error message
			genericErrorFunction($verifyExamExistsOutcome["MESSAGE"]);
		}
	}

	function userService() {
		session_start();

		# Fetch required data
		$currentUID = $_SESSION['current'];

		# Launch data layer execution attempt
		$userFetched = fetchUsername($currentUID);

		# If the message is success...
		if($userFetched['MESSAGE'] == "SUCCESS") {
				
			# Return profile data
			echo json_encode($userFetched);
		}
		# If attempt failed...
		else {
			# Error message
			genericErrorFunction($userFetched["MESSAGE"]);
		}
	}

?>