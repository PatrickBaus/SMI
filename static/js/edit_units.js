function addJEditable(className) {
	$('.' + className).editable('database/save_units.php', {
		indicator: '<img src="img/indicator.gif">',
		tooltip:'Click to edit...',
		cssclass : "inherit"
	});

}

function addBtnClickFunction() {
	var unit = $('input#add_unit').val();
	if (unit == "") {
		$('#message_add').html("Please enter a unit")
		.removeClass()
		.addClass("validationBox");
		$('input#add_unit').focus();
		return false;
	}

	var dataString = "unit=" + unit;
	$('#add_button').hide();
	$('#add_loading').show();
	$.ajax({
		type: "POST",
		url: "database/add_unit.php",
		data: dataString,
		success: function(data) {
			var result = data.split("&");
			if (result[0] == "0") {
				var idStr = "id="; 
				var id = result[1].substring(result[1].indexOf(idStr) + idStr.length);
				$('#message_add').html("Successfully added new unit with id " + id)
				.removeClass()
				.addClass("successBox");
				$('#unitsTable').append(createTableRow(id, unit));
				AddAllJEditables();
				$('.deleteButton').click(deleteBtnClickFunction);
			} else {
				$('#message_add').html("Error adding new unit: " + data)
				.removeClass()
				.addClass("errorBox");
			}
		},
		complete: function() {
			$('#add_loading').hide();
			$('#add_button').show();
		}
	});
	return false;
}


function deleteBtnClickFunction() {
	var row = $(this).parents('.tableRow');
	var unit_id =  $(this).attr('id').split('=')[1];
	var dataString = "type=delete&unit_id=" + unit_id;

	$.ajax({
		type: "POST",
		url: "database/save_units.php",
		data: {id : dataString},
		success: function(data) {
			if (data == "0") {
				row.css("background","#FF3700");
				row.fadeOut(400, function(){
					row.remove();
				});
				$('#message_edit').html("Successfully removed unit id " + unit_id)
				.removeClass()
				.addClass("successBox");
				AddAllJEditables();
			} else {
				$('#message_edit').html("Error: " + data)
				.removeClass()
				.addClass("errorBox");
			}
		}
	});
	return false;
}

function sprintf(format, etc) {
    var arg = arguments;
    var i = 1;
    return format.replace(/%((%)|s)/g, function (m) { return m[2] || arg[i++] })
}

function createTableRow(id, unit) {
	var html = "\t\t" + '<div class="tableRow">' + "\n";
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=unit&unit_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, unit);
	tableRow = "\t\t\t" + ' <div class="tableCell"><button id="unit_id=%s" class="button deleteButton">Delete</button></div>' + "\n";
	html = html + sprintf(tableRow, id);
	html = html + "\t\t</div>\n";

	return html;
}

$(function() {
	$('.addButton').click(addBtnClickFunction);
	$('.deleteButton').click(deleteBtnClickFunction);
});
