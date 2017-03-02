function sensorPanelHeaderClickFunction(event) {
	if ($(event.target).is('div')) {
		var parent = $(this).closest('.sensorPanel')
		var contentPanel = parent.find('.sensorPanelContent');
		if (parent.hasClass('sensorPanelCollapsed')) {
			var panelHeight = contentPanel.height();
			contentPanel.height('0px');
			parent.toggleClass('sensorPanelCollapsed');
			contentPanel.animate({height:panelHeight},200, function() {
			contentPanel.height('');
		});
		} else {
			contentPanel.animate({height:0},200, function() {
				// On complete
				parent.toggleClass('sensorPanelCollapsed');
				contentPanel.height('');
			});
		}
	}
}

function selectAllBtnClickFunction() {
	$('#exportTable').find('input:checkbox').prop('checked', true);
}

function selectNoneBtnClickFunction() {
	$('#exportTable').find('input:checkbox').prop('checked', false);
}

function roomCheckboxChangedFunction() {
	var parent = $(this).closest('.sensorPanel')
	var checkboxes = parent.find('.sensorPanelContent').find('input:checkbox');
	checkboxes.prop('checked', this.checked);
}

function sensorCheckboxChangedFunction() {
	var parent = $(this).closest('.sensorPanel');
	var roomCheckbox = parent.find('.sensorPanelHeader').find(':checkbox');
	var checkedSensorCheckboxes = parent.find('.sensorPanelContent').find('input:checkbox:checked');
	var sensorCheckboxes = parent.find('.sensorPanelContent').find('input:checkbox');
	roomCheckbox.prop('checked', checkedSensorCheckboxes.length == sensorCheckboxes.length);
}

function exportBtnClickFunction() {
	// Clear old warnings and errors
	$('#message_export').html("")
	.removeClass();
	var pattern = /^20\d\d[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01]) ([01][0-9]|2[0123]):([0-5][0-9])$/;

	var startDate = $('input#start_date').val();
	if (!pattern.test(startDate)) {
		$('#message_export').html("Please enter a start date")
		.removeClass()
		.addClass("validationBox");
		$('input#start_date').focus();
		return false;
	}
	var endDate = $('input#end_date').val();
	if (!pattern.test(endDate)) {
		$('#message_export').html("Please enter an end date")
		.removeClass()
		.addClass("validationBox");
		$('input#end_date').focus();
		return false;
	}
	var outputType = $('select#output_type').val()
	sensors = new Array();
	$('#exportTable').find('input:checkbox[name=sensorCheckbox]:checked').each(function() {
		sensors.push($(this).attr('id'));
	});
	if (sensors.length == 0) {
		$('#message_export').html("Please select at least one sensor")
		.removeClass()
		.addClass("validationBox");
		return false;
	}
	sensors.sort();
	sensors = JSON.stringify(sensors);

	if (outputType.substring(0, 5) == "file_") {
		$('#input_startDate').val(startDate);
		$('#input_endDate').val(endDate);
		$('#input_outputType').val(outputType);
		$('#input_sensors').val(sensors);
		$('#download_form').submit();
	} else {
		$('#export_button').hide();
		$('#export_loading').show();
		var dataString = "startDate=" + startDate + "&endDate=" + endDate + "&outputType=" + outputType + "&sensors=" + sensors;
		$.ajax({
			type: "POST",
			url: "database/export.php",
			data: dataString,
			success: function(data) {
				var result = data.split("&");
				if (result[0] == "0") {
					var idStr = "id="; 
					var id = result[1].substring(result[1].indexOf(idStr) + idStr.length);
					$('#message_add').html("Successfully added new room with id " + id)
					.removeClass()
					.addClass("successBox");
					$('#roomsTable').append(createTableRow(id, name, low_temp_threshold, getUnitFromId(low_temp_unit), high_temp_threshold, getUnitFromId(high_temp_unit)));
					AddAllJEditables();
					$('.deleteButton').click(deleteBtnClickFunction);
				} else {
					$('#message_export').html("Error exporting sensor data: " + data)
					.removeClass()
					.addClass("errorBox");
				}
			},
			complete: function() {
				$('#export_loading').hide();
				$('#export_button').show();
			}
		});
	}
	return false;
}

function sprintf(format, etc) {
    var arg = arguments;
    var i = 1;
    return format.replace(/%((%)|s)/g, function (m) { return m[2] || arg[i++] })
}

function createTableRow(id, name, low_threshold, low_unit, high_threshold, high_unit) {
	var html = "\t\t" + '<div class="tableRow">' + "\n";
	var tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=name&room_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, name);
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=low_threshold&room_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, low_threshold);
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit_select_units editableCell" id="type=low_unit&room_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, low_unit);
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=high_threshold&room_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, high_threshold);
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit_select_units editableCell" id="type=high_unit&room_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, high_unit);
	tableRow = "\t\t\t" + ' <div class="tableCell"><button id="room_id=%s" class="button deleteButton">Delete</button></div>' + "\n";
	html = html + sprintf(tableRow, id);
	html = html + "\t\t</div>\n";

	return html;
}

$(function() {
	$('.sensorPanelHeader').click(sensorPanelHeaderClickFunction);
	$('#select_all_button').click(selectAllBtnClickFunction);
	$('#select_none_button').click(selectNoneBtnClickFunction);
	$('#export_button').click(exportBtnClickFunction);
	$(':checkbox[name=roomCheckbox]').change(roomCheckboxChangedFunction);
	$(':checkbox[name=sensorCheckbox]').change(sensorCheckboxChangedFunction);
	$('input#start_date').appendDtpicker({
		"closeOnSelected": true,
		"firstDayOfWeek": 1,
	});
	$('input#end_date').appendDtpicker({
		"closeOnSelected": true,
		"firstDayOfWeek": 1,
	});
});
