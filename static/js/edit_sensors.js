function addJEditableSelect(selectClass, selectData, callback_function) {
	$('.' + selectClass).editable('database/save_sensors.php', {
		indicator: '<img src="img/indicator.gif">',
		tooltip:'Click to edit...',
		data: selectData,
		type: "select",
		callback: window[callback_function],
		submit: "OK",
	});
}

function addJEditable(className) {
	$('.' + className).editable('database/save_sensors.php', {
		indicator: '<img src="img/indicator.gif">',
		tooltip:'Click to edit...',
		cssclass : "inherit"
	});
}

function deleteBtnClickFunction() {
	var row = $(this).parents('.tableRow');
	var sensor_id =  $(this).attr('id').split('=')[1];
	var dataString = "type=delete&sensor_id=" + sensor_id;

	$.ajax({
		type: "POST",
		url: "database/save_sensors.php",
		data: {id : dataString},
		success: function(data) {
			if (data == "0") {
				row.css("background","#FF3700");
				row.fadeOut(400, function(){
					row.remove();
				});
				$('#message_edit').html("Successfully removed sensor id " + sensor_id)
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
	var uid = $('input#add_uid').val();
	var pattern = /^[123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ]+$/;
	if (!pattern.test(uid)) {
		$('#message_add').html("\"" + uid + "\" is not a valid uid. Please enter the sensors uid")
		.removeClass()
		.addClass("validationBox");
		$('input#add_uid').focus()
		return false;
	}
	var unit = $('#add_unit').val();
	// If the user has not set a callback period, use the default supplied by the PHP server.
	var callback_period = $('#add_callback').val() ? $('#add_callback').val(): default_callback_period;
	if (!parseInt(callback_period, 10) > 0) {
		$('#message_add').html("Please enter the meassurement intervall in ms")
		.removeClass()
		.addClass("validationBox");
		$('#add_callback').focus();
		return false;
	}
	var room = $('#add_room').val();
	var node = $('#add_node').val();
		var dataString = "name=" + name + "&uid=" + uid + "&unit=" + unit + "&callback=" +callback_period + "&room=" + room + "&node=" +node;
	$('#add_button').hide();
	$('#add_loading').show();
	$.ajax({
		type: "POST",
		url: "database/add_sensor.php",
		data: dataString,
		success: function(data) {
			var result = data.split("&");
			if (result[0] == "0") {
				var idStr = "id="; 
				var id = result[1].substring(result[1].indexOf(idStr) + idStr.length);
				$('#message_add').html("Successfully added new sensor with id " + id)
				.removeClass()
				.addClass("successBox");
				$('#sensorsTable').append(createTableRow(id, name, uid, getUnitFromId(unit), callback_period, getRoomFromId(room), getNodeFromId(node), true));
				AddAllJEditables();
				$('.deleteButton').click(deleteBtnClickFunction);
			} else {
				$('#message_add').html("Error adding new sensor: " + data)
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

function createTableRow(id, name, uid, unit, callback_period, room, master_node, enabled) {
	var html = "\t\t" + '<div class="tableRow">' + "\n";
	var tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=name&sensor_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, name);
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=uid&sensor_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, uid);createTableRow
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit_select_units editableCell" id="type=unit&sensor_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, unit);
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=callback_period&sensor_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, callback_period);
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit_select_rooms editableCell" id="type=room&sensor_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, room);
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit_select_nodes editableCell" id="type=nodes&sensor_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, master_node);
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit_select_enabled editableCell %s" id="type=enabled&sensor_id=%s">%s</div></div>' + "\n";
	if (enabled == true) {
		html = html + sprintf(tableRow, "enabled", id, "enabled");
	} else {
		html = html + sprintf(tableRow, "disabled", id, "disabled");
	}
	tableRow = "\t\t\t" + ' <div class="tableCell"><button id="sensor_id=%s" class="button deleteButton">Delete</button></div>' + "\n";
	html = html + sprintf(tableRow, id);
	html = html + "\t\t</div>\n";

	return html;
}

$(function() {
	$('.addButton').click(addBtnClickFunction);
	$('.deleteButton').click(deleteBtnClickFunction);
});
