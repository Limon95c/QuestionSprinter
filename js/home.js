// Initialize

// Redirect
$.ajax({
	url : "./data/applicationLayer.php",
	type : "POST",
	dataType : "json",
	data : 	{
				"context" : "home",
				"action" : "REDIRECT"
			},
	ContentType : "application/json",
	success : function(redirectAnswer) {
		if(redirectAnswer.shouldRedirect) {
			$(location).attr("href", redirectAnswer.where + '.html');
		}
		else {

			// Cargar nombre de usuario
			$.ajax({
				url : "./data/applicationLayer.php",
				type : "POST",
				dataType : "json",
				data : 	{ "action" : "USER" },
				ContentType : "application/json",
				success : function(result) {
					if(result.MESSAGE == "SUCCESS") {
						$("#username").html(result.username);
					}
				},
				error : function(errorMessage) {
					alert(errorMessage.statusText);
				}
			});

			// Update exams
			updateExams();
		}
	},
	error : function(errorMessage) {
		alert(errorMessage.statusText);
	}
});

// Logout
$(document).on('click', "#logout_button", function() {
	$.ajax({
		url : "./data/applicationLayer.php",
		type : "POST",
		dataType : "json",
		ContentType : "application/json",
		data : { "action" : "LOGOUT" },
		success : function(message) {
			$(location).attr("href", "index.html");
		},
		error : function(errorMessage) {
			alert(errorMessage.statusText);
		}
	});
});

// Crear nuevo examen
$(document).on('click', "input[name='crearExamen']", function() {
	var nuevoTextbox = $('input[name="newTestName"]');

	if(nuevoTextbox.val() == ""){
		alert("Recuerda asignar un nombre al examen primero!");
	}
	else {
		$.ajax({
			url : "./data/applicationLayer.php",
			type : "POST",
			dataType : "json",
			ContentType : "application/json",
			data : {
						"action" : "NEW_EXAM",
						"name" : nuevoTextbox.val()
				   },
			success : function(result) {
				if(result.MESSAGE != "SUCCESS") {
					alert("Error en la creacion.");
				}
				else {
					alert("Examen creado!");
					updateExams();
					nuevoTextbox.val("");
				}
			},
			error : function(errorMessage) {
				alert(errorMessage.statusText);
			}
		});
	}
});

// Cargar todos los examenes
function updateExams() {

	$.ajax({
		url : "./data/applicationLayer.php",
		type : "POST",
		dataType : "json",
		ContentType : "application/json",
		data : {
				"action" : "LOAD_EXAMS",
				"search" : $("#searchBar").val()
			   },
		success : function(result) {
			if(result.MESSAGE == "SUCCESS") {
				
				$("#Exams").html("");
				var newHtml = "";

				if(result.exams.length > 0) {
					for(var i = 0; i < result.exams.length; i++) {
						newHtml += '<div name="' + result.exams[i].id + '" class="examItem">';
						newHtml += '<div class="row align-justify align-middle" name="testData">';
						newHtml += '<div class="columns">';
						newHtml += '<h2>';
						newHtml += '<span><i class="material-icons largeSize">assignment</i></span>';
						newHtml += '<span name="examName">' + result.exams[i].name + '</span>';
						newHtml += '</h2>';
						newHtml += '</div>';
						newHtml += '<div class="columns shrink">';
						newHtml += '<p>Código de examen: <span name="examKey">' + result.exams[i].key + '</span></p>';
						newHtml += '</div>';
						newHtml += '</div>';

						newHtml += '<div name="questions" class="row align-left align-middle">';

						// Agregar preguntas
						if(result.exams[i].questions.length > 0) {
							for(var p = 0; p < result.exams[i].questions.length; p++) {
								newHtml += '<div class="questionItem column shrink">';
								newHtml += '<h4>' + result.exams[i].questions[p].question + '</h4>';
								newHtml += '<p>';
								newHtml += '<span>';
								newHtml += '<i class="material-icons">done</i>';
								newHtml += '</span>';
								newHtml += result.exams[i].questions[p].answer;
								newHtml += '</p>';
								newHtml += '<p>';
								newHtml += '<span>';
								newHtml += '<i class="material-icons">clear</i>';
								newHtml += '</span>';
								newHtml += result.exams[i].questions[p].wrong1;
								newHtml += '</p>';
								newHtml += '<p>';
								newHtml += '<span>';
								newHtml += '<i class="material-icons">clear</i>';
								newHtml += '</span>';
								newHtml += result.exams[i].questions[p].wrong2;
								newHtml += '</p>';
								newHtml += '<p>';
								newHtml += '<span>';
								newHtml += '<i class="material-icons">clear</i>';
								newHtml += '</span>';
								newHtml += result.exams[i].questions[p].wrong3;
								newHtml += '</p>';
								newHtml += '</div>';
							}
						}

						newHtml += '<div class="newQuestion column shrink">';
						newHtml += '<div class="row">';
						newHtml += '<div class="columns">';
						newHtml += '<input type="text" name="newQ" placeholder="Nueva pregunta..."/>';
						newHtml += '</div>';
						newHtml += '</div>';

						newHtml += '<div class="row align-middle">';
						newHtml += '<div class="columns shrink zeroPadding offset-left-mini">';
						newHtml += '<span><i class="material-icons">done</i></span>';
						newHtml += '</div>';
						newHtml += '<div class="columns">';
						newHtml += '<input type="text" name="newRight" placeholder="Respuesta correcta"/>';
						newHtml += '</div>';
						newHtml += '</div>';

						newHtml += '<div class="row align-middle">';
						newHtml += '<div class="columns shrink zeroPadding offset-left-mini">';
						newHtml += '<span><i class="material-icons">clear</i></span>';
						newHtml += '</div>';
						newHtml += '<div class="columns">';
						newHtml += '<input name="newWrong1" type="text" placeholder="Respuesta incorrecta"/>';
						newHtml += '</div>';
						newHtml += '</div>';

						newHtml += '<div class="row align-middle">';
						newHtml += '<div class="columns shrink zeroPadding offset-left-mini">';
						newHtml += '<span><i class="material-icons">clear</i></span>';
						newHtml += '</div>';
						newHtml += '<div class="columns">';
						newHtml += '<input name="newWrong2" type="text" placeholder="Respuesta incorrecta"/>';
						newHtml += '</div>';
						newHtml += '</div>';

						newHtml += '<div class="row align-middle">';
						newHtml += '<div class="columns shrink zeroPadding offset-left-mini">';
						newHtml += '<span><i class="material-icons">clear</i></span>';
						newHtml += '</div>';
						newHtml += '<div class="columns">';
						newHtml += '<input name="newWrong3" type="text" placeholder="Respuesta incorrecta"/>';
						newHtml += '</div>';
						newHtml += '</div>';

						newHtml += '<div class="row align-center">';
						newHtml += '<div class="columns shrink zeroPadding offset-left-mini">';
						newHtml += '<input type="submit" class="button secondary" name="addQuestion" value="Agregar">';
						newHtml += '</div>';
						newHtml += '</div>';
						newHtml += '</div>';
						newHtml += '</div>';
						newHtml += '</div>';
					}
				}
				else {
					newHtml += '<p class="noContent">No se encontraron examenes.</p>';
				}

				$("#Exams").html(newHtml);
			}
		},
		error : function(errorMessage) {
			alert(errorMessage.statusText);
		}
	});
}

// Crear nueva pregunta
$(document).on('click', "input[name='addQuestion']", function() {
	
	var examID = $(this).parents('.examItem').attr('name');
	var questionTextbox = $('div[name="' + examID + '"] input[name="newQ"]');
	var answerTextbox = $('div[name="' + examID + '"] input[name="newRight"]');
	var wrong1Textbox = $('div[name="' + examID + '"] input[name="newWrong1"]');
	var wrong2Textbox = $('div[name="' + examID + '"] input[name="newWrong2"]');
	var wrong3Textbox = $('div[name="' + examID + '"] input[name="newWrong3"]');

	if(questionTextbox.val() == "" ||
	   answerTextbox.val() == "" ||
	   wrong1Textbox.val() == "" ||
	   wrong2Textbox.val() == "" ||
	   wrong3Textbox.val() == ""){
		alert("No olvides llenar todos los campos!");
	}
	else {

		$.ajax({
			url : "./data/applicationLayer.php",
			type : "POST",
			dataType : "json",
			ContentType : "application/json",
			data : {
						"action" : "NEW_QUESTION",
						"exam_ID" : examID,
						"question" : questionTextbox.val(),
						"answer" : answerTextbox.val(),
						"wrong1" : wrong1Textbox.val(),
						"wrong2" : wrong2Textbox.val(),
						"wrong3" : wrong3Textbox.val()
				   },
			success : function(result) {
				if(result.MESSAGE == "SUCCESS") {
					alert("Pregunta agregada!");
					updateExams();
				}
			},
			error : function(errorMessage) {
				alert(errorMessage.statusText);
			}
		});
	}
});

// Actualizar examenes mostrados
$(document).on('change', '#searchBar', function() {
  updateExams();
});