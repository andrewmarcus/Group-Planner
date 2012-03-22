var members = {};
members.byKey = {};

// Load the list of all events
members.loadMemberList = function () {
	page.showLoading();
	dojo.xhrGet({
		url: "page/members.php",
		load: function(data) {
			dojo.byId("member-list").innerHTML = data;

			page.hideLoading();

			// Parse any nested Dojo widgets in the event page
			dojo.parser.parse("member-list");			
		}
	});
}

// Load the event list when the page is first loaded
dojo.addOnLoad(members.loadMemberList);

members.getMemberDetails = function (name, func) {
	dojo.xhrGet({
		'url': "action/getMemberDetails.json.php?name=" + name,
		'handleAs': "json",
		'load': function(data) {
			members.byKey[data.key] = data;
			
			if (func) {
				func(data);
			}
		}
	});
}

members.highlightMe = function (name) {
	var select = dijit.byId("find_member");
	select.setValue(name);
}

members.highlightMember = function (select) {
	var value = select.getValue();
	
	// Lookup all member listings
	var listings = dojo.query(".member-listing");
	for (var i = 0; i < listings.length; i++) {
		var listing = listings[i];

		var name = dojo.query(".name", listing)[0].innerHTML;
		if (name == value) {
			listing.className = "member-listing selected";
		} else {
			listing.className = "member-listing";
		}
	}
	select.focus();
}

members.showAddDialog = function (name, callback) {
	var dialog = dijit.byId("member_dialog");

	// Attach a callback to the submit action in the dialog
	dialog.callback = callback;
	
	var nameBox = dojo.query("input[name='name']", dialog.domNode)[0];
	var emailBox = dojo.query("input[name='email']", dialog.domNode)[0];
	var phoneBox = dojo.query("input[name='phone']", dialog.domNode)[0];
	var cellBox = dojo.query("input[name='cell']", dialog.domNode)[0];
	var addressBox = dojo.query("textarea[name='address']", dialog.domNode)[0];
	var adminBox = dojo.query("input[name='admin']", dialog.domNode)[0];
	var inactiveBox = dojo.query("input[name='inactive']", dialog.domNode)[0];
	
	dijit.byNode(nameBox).setValue(name ? name : "");
	dijit.byNode(emailBox).setValue("");
	dijit.byNode(phoneBox).setValue("");
	dijit.byNode(cellBox).setValue("");
	addressBox.value = "";
	adminBox.checked = false;
	inactiveBox.checked = false;
	
	dijit.focus(nameBox);
	dialog.show();
}

members.showEditDialog = function (name, callback) {
	members.getMemberDetails(name, function(data) {
		var dialog = dijit.byId("member_edit_dialog");
		
		// Attach a callback to the submit action in the dialog
		dialog.callback = callback;

		var memberKeyBox = dojo.query("input[name='memberKey']", dialog.domNode)[0];
		var nameBox = dojo.query("input[name='name']", dialog.domNode)[0];
		var emailBox = dojo.query("input[name='email']", dialog.domNode)[0];
		var phoneBox = dojo.query("input[name='phone']", dialog.domNode)[0];
		var cellBox = dojo.query("input[name='cell']", dialog.domNode)[0];
		var addressBox = dojo.query("textarea[name='address']", dialog.domNode)[0];
		var adminBox = dojo.query("input[name='admin']", dialog.domNode)[0];
		var inactiveBox = dojo.query("input[name='inactive']", dialog.domNode)[0];
	
		dijit.byNode(nameBox).setValue(data.name);
		dijit.byNode(emailBox).setValue(data.email);
		dijit.byNode(phoneBox).setValue(data.phone);
		dijit.byNode(cellBox).setValue(data.cell);
		addressBox.value = data.address;
		memberKeyBox.value = data.key;
		
		adminBox.checked = (data.admin ? true : false);
		inactiveBox.checked = (data.inactive ? true : false);
		
		dialog.show();
	});
}

members.hideAddDialog = function () {
	var dialog = dijit.byId("member_dialog");
	dialog.hide();
	return false;
}

members.hideEditDialog = function () {
	var dialog = dijit.byId("member_edit_dialog");
	dialog.hide();
	return false;
}

members.editMember = function () {
	var dialog = dijit.byId("member_edit_dialog");		

	var form = dojo.query("form", dialog.domNode)[0];
	dojo.xhrPost ({
		url: "action/editMember.json.php",
		handleAs: "json",
		load: dojo.hitch(dialog, function(data) {
			members.byKey[data.key] = data;

			if (this.callback) {
				this.callback(data);
			}
		}),
		form: form
	});
	dialog.hide();
}

// Create a new member
members.addNewMember = function () {
	var dialog = dijit.byId("member_dialog");			

	var form = dojo.query("form", dialog.domNode)[0];
	dojo.xhrGet ({
		url: "action/editMember.json.php",
		handleAs: "json",
		load: dojo.hitch(dialog, function(data) {
			members.byKey[data.key] = data;				

			if (this.callback) {
				this.callback(data);
			}
		}),
		form: form
	});
	dialog.hide();
}


