var myevents = (function() {

	var tabName = 'myevents';

	/** Load the list of all of my events */
	function loadMyEvents(callback) {
		page.showLoading();
		dojo.xhrGet({
			url: "page/myevents.php?practices=" + (page.isShowPractices() ? "true" : "false"),
			load: function(data) {
				dojo.byId("myevents").innerHTML = data;				
				dojo.parser.parse('myevents');
				
				if (callback) {
					callback();
				}
				page.hideLoading();
			}
		});
	}
	
	/** Show the full event in the events tab */
	function showEvent(id) {
		// Load the event in the events tab
		events.loadEvent(id, function() {
			// Select the events tab
			var tabs = dijit.byId("mainTabContainer");
			var eventTab = dijit.byId("eventTab");
			tabs.selectChild(eventTab);			
		});
	}
	
	// Load the event list when the page is first loaded
	dojo.addOnLoad(loadMyEvents);
	
	/** Reload a particular event, undoing any modifications */
	function reloadMyEvents() {
		page.unsetEditing();
		loadMyEvents();
	}
	
	/** 
	 * Edit the row for one attendee.
	 *
	 * @param eventKey string The ID of the event to edit
	 * @param memberKey string The ID of the member to edit
	 * @param editable boolean True to edit the field, false to restore it to its original form.
	 * @param added boolean True if the attendee is being added to the event, false if they already have a status.
	 */
	function editAttendee(eventKey, memberKey, editable, added) {
		if (!page.setEditing(tabName)) {
			return;
		}
		page.showLoading();
		
		dojo.xhrGet({
			url: "action/attendeeRow.edit.php?event=" + eventKey + "&member=" + memberKey + "&edit=" + editable + "&added=" + added + "&mode=myevents",
			load: function(data) {
				page.hideLoading();
				var row = dojo.byId("myevent_" + eventKey);
				if (row) {
					var div = document.createElement("DIV");
					div.innerHTML = "<table><tr>" + data + "</tr></table>";
					
					// Get the tr from the table in the div
					var tr = div.getElementsByTagName("tr")[0];

					// Remove the old cells
					while (row.cells.length > 3) {
						row.deleteCell(3);
					}
					
					// Copy each element into each new cell
					for (var i = 0; i < tr.cells.length; i++) {
						var td = tr.cells[i];
						row.appendChild(td.cloneNode(true));
					}
					dojo.parser.parse(row);
				}
			}
		});
	}
	
	/**
	 * Remove this member from one event, or restore this member to the event.
	 *
 	 * @param eventKey string The ID of the event to remove the member from.
	 */
	function removeAttendee(eventKey) {
		if (!page.setEditing(tabName)) {
			return;
		}
		
		var row = dojo.query("#myevent_" + eventKey);
		if (row.length) {
		
			// Flag that the row was removed
			restore = false;
			if (dojo.hasClass(row[0], "deleted")) {
				restore = true;
				row.removeClass("deleted");
			}
			else {
				row.addClass("deleted");
			}
			
			var fields = dojo.query("input, select", row[0]);
			for (var i = 0; i < fields.length; i++) {
				// If the field is a dojo widget, disable it
				var widget = dijit.byNode(fields[i]);
				if (widget) {
					widget.setDisabled(!restore);
				}
				// Enable the hidden field
				else if (fields[i].type && fields[i].type == "hidden") {
					fields[i].disabled = restore;
				}
				// Disable a normal input field
				else {
					fields[i].disabled = !restore;
				}
			}
			var link = dojo.query(".remove", row[0]);
			if (link.length > 0) {
				if (restore) {
					link[0].innerHTML = "Remove";
				} else {
					link[0].innerHTML = "Undo";
				}
			}
		}
	}	
		
	/**
	 * Submit the event attendees, do the server-side processing, and reload the event
	 */
	function saveAttendee() {
		// Reset the editing flag
		page.unsetEditing();
		page.showLoading();
		
		dojo.xhrGet({
			url: "action/setMyEvents.php",
			load: function(data) {
				page.reloadAllEvents();
				page.hideLoading();
			},
			form: "myeventsForm"
		});
	}
		
	// Make some of the functions public
	return {
		loadMyEvents: loadMyEvents,
		showEvent: showEvent,
		reloadMyEvents: reloadMyEvents,
		editAttendee: editAttendee,
		removeAttendee: removeAttendee,
		saveAttendee: saveAttendee
	}
	
})(); // Execute the function and use the result