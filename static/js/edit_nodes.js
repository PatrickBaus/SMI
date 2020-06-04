function validURL(str) {
	var pattern = /^((([a-z\d]([a-z\d-]*[a-z\d])*)\.)+[a-z]{2,}|((\d{1,3}\.){3}\d{1,3})|(localhost))$/;
	return pattern.test(str);
}

function addJEditableHostname(className) {
	$('.' + className).editable(jEditableCall, {
		indicator: '<img src="img/indicator.gif">',
		tooltip:'Click to edit...',
		cssclass : "inherit"
	});
}

function addJEditable(className) {
	$('.' + className).editable('database/save_nodes.php', {
		indicator: '<img src="img/indicator.gif">',
		tooltip:'Click to edit...',
		cssclass : "inherit"
	});
}

function jEditableCall(value, settings) {
	// Convert all DNS names to lower case.
	// RFC 2065: https://tools.ietf.org/html/rfc2065
	value = value.toLowerCase();
	// Variable "this" is the textbox, because it is called from the
	// context of the JEditable.
	var textbox = this;
	$.ajax({
		type: "POST",
		url: "database/save_nodes.php",
		data: {id: textbox.id, value: value},
		success: function(data) {
			$(textbox).html(data);
		}
	});
	return (value);
}

function addBtnClickFunction() {
	// Convert all DNS names to lower case.
	// RFC 2065: https://tools.ietf.org/html/rfc2065
	var hostname = $('input#add_hostname').val().toLowerCase();
	if (! validURL(hostname)) {
		$('#message_add').html("Please enter a domain hostname or ip address")
		.removeClass()
		.addClass("validationBox");
		$('input#add_hostname').focus();
		return false;
	}
	var label = $('input#add_label').val();
	if (label === "") {
		label = hostname;
	}
	var port = parseInt($('input#add_port').val(), 10);
	port = port ? port : default_port;
	if (isNaN(port) || (port < 1) || (port > 65535) ) {
		$('#message_add').html("Please enter a port")
		.removeClass()
		.addClass("validationBox");
		$('input#add_port').focus();
		return false;
	}

	var dataString = "hostname=" + hostname + "&label=" + label + "&port=" + port;
	$('#add_button').hide();
	$('#add_loading').show();
	$.ajax({
		type: "POST",
		url: "database/add_node.php",
		data: dataString,
		success: function(data) {
			var result = data.split("&");
			if (result[0] == "0") {
				var idStr = "id="; 
				var id = result[1].substring(result[1].indexOf(idStr) + idStr.length);
				$('#message_add').html("Successfully added new node with id " + id)
				.removeClass()
				.addClass("successBox");
				$('#nodesTable').append(createTableRow(id, hostname, label, port));
				AddAllJEditables();
				$('.deleteButton').click(deleteBtnClickFunction);
			} else {
				$('#message_add').html("Error adding new node: " + data)
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
	var node_id =  $(this).attr('id').split('=')[1];
	var dataString = "type=delete&node_id=" + node_id;

	$.ajax({
		type: "POST",
		url: "database/save_nodes.php",
		data: {id : dataString},
		success: function(data) {
			if (data == "0") {
				row.css("background","#FF3700");
				row.fadeOut(400, function(){
					row.remove();
				});
				$('#message_edit').html("Successfully removed node id " + node_id)
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

function createTableRow(id, hostname, label, port) {
	var html = "\t\t" + '<div class="tableRow">' + "\n";
	var tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=hostname&node_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, hostname);
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=label&node_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, label);
	tableRow = "\t\t\t" + ' <div class="tableCell"><div class="edit editableCell" id="type=port&node_id=%s">%s</div></div>' + "\n";
	html = html + sprintf(tableRow, id, port);
	tableRow = "\t\t\t" + ' <div class="tableCell"><button id="node_id=%s" class="button deleteButton">Delete</button></div>' + "\n";
	html = html + sprintf(tableRow, id);
	html = html + "\t\t</div>\n";

	return html;
}

$(function() {
	$('.addButton').click(addBtnClickFunction);
	$('.deleteButton').click(deleteBtnClickFunction);
});
