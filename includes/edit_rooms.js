function isInt(value) { 
    return !isNaN(parseInt(value, 10)) && (parseFloat(value, 10) == parseInt(value, 10)); 
}

function addJEditableSelect(selectClass, selectData, callback_function) {
	$('.' + selectClass).editable('database/save_rooms.php', {
		indicator: '<img src="img/indicator.gif">',
		tooltip:'Click to edit...',
		data: selectData,
		type: "select",
		callback: window[callback_function],
		submit: "OK",
	});
}

function addJEditable(className) {
	$('.' + className).editable('database/save_rooms.php', {
		indicator: '<img src="img/indicator.gif">',
		tooltip:'Click to edit...',
		cssclass : "inherit"
	});
}

function deleteBtnClickFunction() {
	var row = $(this).parents('.tableRow');
	var room_id =  $(this).attr('id').split('=')[1];
	var dataString = "type=delete&room_id=" + room_id;

	$.ajax({
		type: "POST",
		url: "database/save_rooms.php",
		data: {id : dataString},
		success: function(data) {
			if (data == "0") {
				row.css("background","#FF3700");
				row.fadeOut(400, function(){
					row.remove();
				});
				$('#message_edit').html("Successfully removed room id " + room_id)
				.removeClass()
				.addClass("successBox");
			} else {
				$('#message_edit').html("Error: " + data)
				.removeClass()
				.addClass("errorBox");
			}
		}
	});
	return false;
}

function addBtnClickFunction() {
	var name = $('input#add_name').val();
	if (name == "") {
		$('#message_add').html("Please enter a name")
		.removeClass()
		.addClass("validationBox");
		$('input#add_name').focus();
		return false;
	}
	var low_temp_threshold = $('input#add_low_temp_value').val();
	if (isNaN(parseFloat(low_temp_threshold, 10))) {
		$('#message_add').html("Please enter a temperature threshold")
		.removeClass()
		.addClass("validationBox");
		$('input#add_low_temp_value').focus();
		return false;
	}
	var low_temp_unit = $('select#add_low_temp_unit').val();
	var high_temp_threshold = $('input#add_high_temp_value').val();
	if (isNaN(parseFloat(low_temp_threshold, 10))) {
		$('#message_add').html("Please enter a temperature threshold")
		.removeClass()
		.addClass("validationBox");
		$('input#add_high_temp_value').focus();
		return false;
	}
	var high_temp_unit = $('select#add_high_temp_unit').val();

	var dataString = "name=" + name + "&low_threshold=" + low_temp_threshold + "&low_unit=" + low_temp_unit + "&high_threshold=" + high_temp_threshold + "&high_unit=" + high_temp_unit;
	$('#add_button').hide();
	$('#add_loading').show();
	$.ajax({
		type: "POST",
		url: "database/add_room.php",
		data: dataString,
		success: function(data) {
			var result = data.split("&");
			if (result[0] == "0") {
				var idStr = "id="; 
				var id = result[1].substring(result[1].indexOf(idStr) + idStr.length);
				$('#message_add').html("Successfully added new room with id " + id)
				.removeClass()
				.addClass("successBox");
				$('#roomsTable').append(createTableRow(id, name, low_temp_threshold, getUnitFromId(low_temp_unit), high_temp_threshold, getUnitFromId(high_temp_unit), true));
				AddAllJEditables();
				$('.deleteButton').click(deleteBtnClickFunction);
			} else {
				$('#message_add').html("Error adding new room: " + data)
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

function sprintf(format, etc) {
    var arg = arguments;
    var i = 1;
    return format.replace(/%((%)|s)/g, function (m) { return m[2] || arg[i++] })
}

function createTableRow(id, name, low_threshold, low_unit, high_threshold, high_unit, enabled) {
  var filler = "\t\t\t";

	var html = filler + '<div class="tableRow">' + "\n";
	var tableRow = filler + "\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=name&room_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, name);
	tableRow = filler + "\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=low_threshold&room_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, low_threshold);
	tableRow = filler + "\t" + ' <div class="tableCell"><div class="edit_select_units editableCell" id="type=low_unit&room_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, low_unit);
	tableRow = filler + "\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=high_threshold&room_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, high_threshold);
	tableRow = filler + "\t" + ' <div class="tableCell"><div class="edit_select_units editableCell" id="type=high_unit&room_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, high_unit);
	tableRow = filler + "\t" + ' <div class="tableCell"><div class="edit_select_enabled editableCell %s" id="type=enabled&room_id=%s">%s</div></div>' + "\n";
		if (enabled == true) {
		html = html + sprintf(tableRow, "enabled", id, "enabled");
	} else {
		html = html + sprintf(tableRow, "disabled", id, "disabled");
	}
	tableRow = filler + "\t" + ' <div class="tableCell"><button id="room_id=%s" class="button deleteButton">Delete</button></div>' + "\n";
	html = html + sprintf(tableRow, id);
	html = html + filler + "</div>\n";

	return html;
}

$(function() {
	$('.addButton').click(addBtnClickFunction);
	$('.deleteButton').click(deleteBtnClickFunction);
});
