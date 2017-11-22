// Initialize

// Redirect
$.ajax({
	url : "./data/applicationLayer.php",
	type : "POST",
	dataType : "json",
	data : 	{
				"context" : "exam",
				"action" : "REDIRECT"
			},
	ContentType : "application/json",
	success : function(redirectAnswer) {
		if(redirectAnswer.shouldRedirect) {
			$(location).attr("href", redirectAnswer.where + ".html");
		}
		else {

			// Load page title
			$.ajax({
				url : "./data/applicationLayer.php",
				type : "POST",
				dataType : "json",
				data : 	{ "action" : "SET_EXAM_HEADER" },
				ContentType : "application/json",
				success : function(result) {
					if(result.MESSAGE == "SUCCESS") {
						// Set page title
						$("#pageTitle").text(result.title + " | Question Sprinter");

						// Set header title
						$("#examTitle").text(result.title);

						// Set exam author
						$("#author").text(result.author);
					}
				},
				error : function(errorMessage) {
					alert(errorMessage.statusText);
				}
			});

			// Cargar las preguntas
			loadQuestions();
		}
	},
	error : function(errorMessage) {
		alert(errorMessage.statusText);
	}
});

// Volver al menu de inicio
$(document).on('click', "#volverAInicio", function() {
	$.ajax({
		url : "./data/applicationLayer.php",
		type : "POST",
		dataType : "json",
		data : 	{
					"action" : "LOGOUT"
				},
		ContentType : "application/json",
		success : function(logout) {
			if(logout.result == "SUCCESS") {
				$(location).attr("href", "index.html");
			}
		},
		error : function(errorMessage) {
			alert(errorMessage.statusText);
		}
	});
});

// Evaluar examen
$(document).on('click', "#evaluar", function() {
	$(this).toggleClass('hide');
	$("input[type='radio']").prop('disabled', true);
	$(".swtich").toggleClass('disabledSwitch');
	$(".feedback").toggleClass('hide');
	$("#retry").toggleClass('hide');
});

$(document).on('click', "#retry", function() {
	location.href = location.href;
});

function loadQuestions() {
	$.ajax({
		url : "./data/applicationLayer.php",
		type : "POST",
		dataType : "json",
		data : 	{ "action" : "LOAD_QUESTIONS" },
		ContentType : "application/json",
		success : function(result) {
			$("#questions").html("");
			var newHtml = '';

			if(result.MESSAGE = "SUCCESS") {
				
				if(result.questions.length > 0) {
					for(var q = 0; q < result.questions.length; q++) {
						newHtml += '<div name="questionItem" class="row align-center">';
						newHtml += '<div class="columns">';
						newHtml += '<p class="questionText">' + result.questions[q].question + '</p>';
						newHtml += '<ul class="answers">';

						for(var a = 0; a < result.questions[q].answers.length; a++) {

							newHtml += '<li class="row align-left align-middle">';

							newHtml += '<div class="feedback columns shrink hide">';
							if(result.questions[q].answers[a].correct == "true") {
								newHtml += '<i class="material-icons">check</i>';
							}
							else {
								newHtml += '<i class="material-icons">close</i>';
							}
							newHtml += '</div>';

							newHtml += '<div class="columns shrink">';
							newHtml += '<div class="switch">';
							newHtml += '<input class="switch-input" id="' + result.questions[q].ID + a + '" type="radio" name="' + result.questions[q].ID + '">';
							newHtml += '<label class="switch-paddle" for="' + result.questions[q].ID + a + '"></label>';
							newHtml += '</div>';
							newHtml += '</div>';

							newHtml += '<div class="columns">';
							newHtml += '<p>';
							newHtml += result.questions[q].answers[a].answer;
							newHtml += '</p>';
							newHtml += '</div>';
							newHtml += '</li>';
						}
		                        
						newHtml += '</ul>';
						newHtml += '</div>';
						newHtml += '</div>';

						newHtml += '<hr>';
					}

					newHtml += '<div class="row align-center">';
					newHtml += '<div class="columns shrink">';
					newHtml += '<input type="submit" class="button" value="Volver a inicio" id="volverAInicio">';
					newHtml += '</div>';

					newHtml += '<div class="columns shrink">';
					newHtml += '<input type="submit" class="button secondary" value="Evaluar" id="evaluar">';
					newHtml += '</div>';

					newHtml += '<div class="columns shrink">';
					newHtml += '<input type="submit" class="button secondary hide" value="Reintentar" id="retry">';
					newHtml += '</div>';
					newHtml += '</div>';
				}
				else {
					newHtml += '<div class="row align-center">';
					newHtml += '<div class="columns shrink">';
					newHtml += '<p class="noContent">No se han agregado preguntas.</p>';
					newHtml += '</div>';
					newHtml += '</div>';

					newHtml += '<div class="row align-center">';
					newHtml += '<div class="columns shrink">';
					newHtml += '<input type="submit" class="button" value="Volver a inicio" id="volverAInicio">';
					newHtml += '</div>';
					newHtml += '</div>';
				}

				$("#questions").append(newHtml);
        	}
		},
		error : function(errorMessage) {
			alert(errorMessage.statusText);
		}
	});
}