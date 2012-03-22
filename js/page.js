var page = (function() {

	// Keep track of how many pages are currently loading
	var loadingCount = 0;
	
	// Keep track of whether an edit has not yet been committed
	var editing = false;
	
	// Keep track of whether to show all weekly practices
	var practices = false;

	/** 
	 * If edits have been made, this method asks the user whether they wish to discard them before navigating
	 * away from their current event.  If the navigation should proceed, returns true; false if the navigation
	 * should not occur.
	 */
	function isDoneEditing(tab) {
		if (editing == tab) {
			var response = confirm("Are you sure want to discard the changes you've made?");	
			if (response) {
				unsetEditing();
			}
			return response;
		}
		return true;
	}
	
	/** Set a flag to indicate that the page is being edited */
	function setEditing(tab) {
		if (editing && editing != tab) {
			alert("You are currently making changes in a different tab.  You must complete those changes first.");
			return false;
		}
		editing = tab;
		return true;
	}
	
	/** Set a flag to indicate that the page is no longer being edited */
	function unsetEditing() {
		editing = false;
	}
	
	/**
	 * A page has started loading, so show the loading banner
	 */
	function showLoading() {
		if (loadingCount == 0) {
			var loading = dojo.byId("loading");
			loading.style.display = "block";
			loading.style.visibility = "visible";
		}
		loadingCount++;
	}
	
	/**
	 * A page has finished loading, so if there are no more loading pages, hide the loading banner
	 */
	function hideLoading() {
		loadingCount--;
		if (loadingCount < 0) {
			loadingCount = 0;
		}
		if (loadingCount == 0) {
			var loading = dojo.byId("loading");
			loading.style.display = "none";
			loading.style.visibility = "hidden";
		}
	}
	
	/** Hide weekly practices and refresh the events list */
	function hidePractices() {
		if (isDoneEditing()) {
			practices = false;
			reloadAllEvents(true);
		}
	}

	/** Show weekly practices and refresh the events list */
	function showPractices() {
		if (isDoneEditing()) {
			practices = true;
			reloadAllEvents(true);
		}
	}
	
	/** Returns true if practices should be shown */
	function isShowPractices() {
		return practices;
	}
	
	/** Reload all the events lists.  This is called when the view status of practices has been changed */
	function reloadAllEvents(all) {
		if (events) {
			if (all) {
				events.loadEventList(events.loadEvent);
			}
			else {
				events.loadEvent();
			}
		}
		if (myevents) {
			myevents.loadMyEvents();
		}	
	}
	
	/** Setup the tabs */
	function setupTabs() {
		var tabs = dijit.byId('mainTabContainer');
		if (tabs) {
			dojo.connect(tabs, 'selectChild', function(child) {
				window.location.hash = child.title.replace(' ', '+');
			});
			
			// On the first page load, select the tab with the given hash
			var hash = window.location.hash.substr(1).replace('+', ' ');
			dojo.forEach(tabs.getChildren(), function(child) {
				if (child.title == hash) {
					tabs.selectChild(child);
				}
			});
		}
	}
	
	dojo.addOnLoad(setupTabs);
	
	return {
		showLoading: showLoading,
		hideLoading: hideLoading,
		isDoneEditing: isDoneEditing,
		setEditing: setEditing,
		unsetEditing: unsetEditing,
		showPractices: showPractices,
		hidePractices: hidePractices,
		isShowPractices: isShowPractices,
		reloadAllEvents: reloadAllEvents
	}

})();
