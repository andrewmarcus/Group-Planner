var events = (function() {

	// Maintain the selected event
	var eventId = null;
	
	var tabName = 'events';
	
	/** Load the list of all of my events */
	function loadEventList(callback) {
		page.showLoading();
		dojo.xhrGet({
			url: "page/events.php?practices=" + (page.isShowPractices() ? "true" : "false"),
			load: function(data) {
				dojo.byId("event-list").innerHTML = data;
				if (eventId) {
					highlightEvent(eventId);
				}
				if (callback) {
					callback();
				}
				page.hideLoading();
			}
		});
	}

	// Load the event list when the page is first loaded
	dojo.addOnLoad(loadEventList);
	
	// Set a timer that will reload the event list every 10 minutes
	setInterval('events.loadEventList()', 600000);

	/** Reload a particular event, undoing any modifications */
	function reloadEvent() {
		page.unsetEditing();
		loadEvent();
	}

	/** Load the event with the given id */
	function loadEvent(id, callback) {
		if (!page.isDoneEditing(tabName)) {
			return;
		}
		if (id == null) {
			id = eventId;
		}
		if (id == null) {
			return;
		}
	
		page.showLoading();
		eventId = id;
		
		dojo.xhrGet({
			url: "page/event.php?event=" + id,
			load: function(data) {
				dojo.byId("event").innerHTML = data;
	
				// Parse any nested Dojo widgets in the event page
				dojo.parser.parse("event");
				
				highlightEvent(id);
				if (callback) {
					callback();
				}
				loadEventTotal();
				page.hideLoading();
				
			}
		});
	}

	/** Load the summary of the number of attendees */
	function loadEventTotal(callback) {
		page.showLoading();
		
		dojo.xhrGet({
			url: "page/eventTotal.php?event=" + eventId,
			load: function(data) {
				dojo.byId("event-total").innerHTML = data;
	
				if (callback) {
					callback();
				}
				page.hideLoading();
			}
		});
	}

	/** Highlight the event with the given ID, since it is selected */
	function highlightEvent(id) {
		var table = dojo.byId("eventsTable");
		dojo.forEach(table.rows,
			function(tr) {
				if (tr.id) {
					if (tr.id == "event_" + id) {
						dojo.addClass(tr, "selected");
					}
					else {
						dojo.removeClass(tr, "selected");
					}
				}
			}
		);
	}

	/** 
	 * Edit the row for one attendee.
	 *
	 * @param memberKey string The ID of the member to edit
	 * @param editable boolean True to edit the field, false to restore it to its original form.
	 * @param added boolean True if the attendee is being added to the event, false if they already have a status.
	 */
	function editAttendee(memberKey, editable, added) {
		if (!page.setEditing(tabName)) {
			return;
		}
		page.showLoading();
		
		dojo.xhrGet({
			url: "action/attendeeRow.edit.php?event=" + eventId + "&member=" + memberKey + "&edit=" + editable + "&added=" + added,
			load: function(data) {
				page.hideLoading();
				var row = dojo.byId("attendee_" + memberKey);
				if (row) {
					// Hack for IE
					if (dojo.isIE) {
						var div = document.createElement("DIV");
						div.innerHTML = "<table><tr>" + data + "</tr></table>";
						
						// Get the tr from the table in the div
						var tr = div.getElementsByTagName("tr")[0];
	
						// Remove the old cells
						while (row.cells.length > 0) {
							row.deleteCell(0);
						}
						
						// Copy each element into each new cell
						for (var i = 0; i < tr.cells.length; i++) {
							var td = tr.cells(i);
							row.appendChild(td.cloneNode(true));
						}
					}
					// Otherwise, do it the easy way
					else {
						row.innerHTML = data;
					}
					dojo.parser.parse(row);
				}
			}
		});
	}

	/**
	 * Remove one member from this event, or restore the member to the event.
	 *
 	 * @param memberKey string The ID of the member to remove.
	 */
	function removeAttendee(memberKey) {
		if (!page.setEditing(tabName)) {
			return;
		}
	
		var row = dojo.query("#attendee_" + memberKey);
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

	/** Submit the event attendees, do the server-side processing, and reload the event */
	function saveAttendees() {
		// Reset the editing flag
		page.unsetEditing();
		page.showLoading();
		
		dojo.xhrGet({
			url: "action/setAttendees.php?event=" + eventId,
			load: function(data) {
				page.reloadAllEvents();				
				page.hideLoading();
			},
			form: "attendeeForm"
		});
		
		// Returns false so that the form is not automatically submitted
		return false;
	}

	/** Add a new atteendee to this event, as selected from the selection widget */
	function addNewAttendee(select) {
		if (dojo.isString(select)) {
			var value = select;
		} else {
			var value = select.getValue();
			// Clear the form so we can use it again
			select.setValue("");
		}
		if (!value) {
			return;
		}
		
		if (!page.setEditing(tabName)) {
			return;
		}
		page.showLoading();
	
		members.getMemberDetails(value, function(member) {
			page.hideLoading();
			
			// If there is no member information, open the new member dialog
			if (!member.name) {
				members.showAddDialog(value, addAttendeeToList);
			}
			else {
				addAttendeeToList(member);
			}
		});
	}

	/** Add the member to the event */
	function addAttendeeToList(member) {
		var table = dojo.byId("attendeeTable");
	
		var tr = table.insertRow(0);
		tr.id = "attendee_" + member.key;
		tr.className = "added";
		
		editAttendee(member.key, true, true);
	}

	/** Store the members-only details for this event */
	function setPrivateDetails() {
		page.showLoading();
	
		var detailsBox = dojo.byId("private-details-box");
		var details = detailsBox.value;
		
		dojo.xhrPost({
			url: "action/details.save.php",
			content: {'details': details, 'eventKey': eventId},
			load: function(data) {
				page.hideLoading();
				page.unsetEditing();
			}
		});	
	}
	
	// Make some of the functions public
	return {
		loadEventList: loadEventList,
		reloadEvent: reloadEvent,
		loadEvent: loadEvent,
		editAttendee: editAttendee,
		removeAttendee: removeAttendee,
		saveAttendees: saveAttendees,
		addNewAttendee: addNewAttendee,
		setPrivateDetails: setPrivateDetails
	}

})();
