// Redirect
$.ajax({
	url : "./data/applicationLayer.php",
	type : "POST",
	dataType : "json",
	ContentType : "application/json",
	data : {
				"context" : "index",
				"action" : "REDIRECT"
			},
	success : function(redirectAnswer) {
		if(redirectAnswer.shouldRedirect) {
			$(location).attr("href", redirectAnswer.where + ".html");
		}
	},
	error : function(errorMessage) {
		alert(errorMessage.statusText);
	}
});

// Click actions login
$(document).on('click', "#signIn_button", function() {

	var user = $("#username");
	var password = $("#password");

	if(user.val() != "" && password.val() != "") {
		$.ajax({
			url : "./data/applicationLayer.php",
			type : "POST",
			data : {
						"uName" : user.val(),
						"uPassword" : password.val(),
						"action" : "LOGIN"
					},
			ContentType : "application/json",
			dataType : "json",
			success: function(dataReceived) {
				$(location).attr("href", "home.html");
			},
			error : function(errorMessage) {
				alert(errorMessage.statusText);
			}
		});
	}
	else {
		alert("Recuerda llenar los campos primero!");
	}
});

// Click actions register
$(document).on('click', "#register_button", function() {

	var user = $("#newUser");
	var first = $("#newFirst");
	var last = $("#newLast");
	var email = $("#newEmail");
	var password = $("#newPass");
	var confirm = $("#confirmPass");

	var validRegister = $(".registerElement");
	var valid = true;

	// Revisar si los campos estan vacíos
	for(var i = 0; i < 6; i++) {
		if(validRegister.eq(i).val() == "") {
			alert("Please fill the remaining blanks first.")
			valid = false;
		}
	}

	// Si el password no concuerda con la confirmacion, mostrar alerta
	if(valid && password.val() !== confirm.val()) {
		valid = false;
		alert("Password confirmation doesn't match the new password.");
	}

	// Si si es valida la informacion ingresada
	if(valid) {

		// Registrar usuario nuevo
		$.ajax({
			url : "./data/applicationLayer.php",
			type : "POST",
			ContentType : "application/json",
			dataType : "json",
			data : {
						"uName" : user.val(),
						"uPassword" : password.val(),
						"action" : "REGISTER"
					},
			success: function(status) {
				alert("Nuevo maestro creado!");
				$(location).attr("href", "home.html");
			},
			error : function(errorMessage) {
				alert(errorMessage.statusText);
			}
		});
	}
});

// Click start exam button
$(document).on('click', "input[name='submitCode']", function() {
	
	var codeTextbox = $("input[name='examCode']");
	
	if(codeTextbox.val().length != 10) {
		alert("Debes ingresar un codigo de 10 caracteres!");
	}
	else {
		$.ajax({
			url : "./data/applicationLayer.php",
			type : "POST",
			data : {
						"code" : codeTextbox.val(),
						"action" : "START_EXAM"
					},
			ContentType : "application/json",
			dataType : "json",
			success: function(result) {
				if(result.MESSAGE == "SUCCESS") {
					$(location).attr("href", "exam.html");
				}
				else {
					alert("Exam not found.");
				}
			},
			error : function(errorMessage) {
				alert(errorMessage.statusText);
			}
		});
	}
});