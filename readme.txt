=== AlieneilA Event Calendar ===
Contributors: alieneila
Donate Link: http://area51.alieneila.net
Tags: events, calendar
Requires at least: 2.9.2
Stable tag: 1.9.91b

== Description ==
A year and a half ago when I first created this plugin, I knew nothing of Wordpress. I created it simply because a friend of mine asked me to due to the lack of good event calendar plugins.

Frankly, this plugin sucks compared to what it could and should be.

Now that I've created nearly 100 Wordpress driven sites since then, I've learned a lot about the ins and outs of how Wordpress functions. I've hacked plugins, created special plugins, created templates... etc etc... And now it is time for me to completely rewrite this plugin in order to make it as awesome and it should be.

As of today, 3/23/2011, I am halting updating this version of the plugin. I highly suggest using http://wordpress.org/extend/plugins/events-manager/

= Features =

* Language Support
* Creates Event Calendar Page
* Large Calendar
* Mini-Calendar Sidebar Widget
* Event List Sidebar Widget
* Uses Post Categories for Event Categories
* Embeds a Google Map if an address is provided.
* Add event from Add/Edit post page.
* Admin Add events:
	Title, Description, Category, Location or URL for online event, Contact Name, Contact Email, Address, City, State, Zip, Phone, Price, Date of event,
	Reoccurring Events - daily, weekly, monthly, years - , End Date, Start Time, Duration of Event, RSVP, RSVP Guest Limits, Invitations, 
	Select members to send notifications to.
* Edit/Delete Events
* Attach Media - Currently supports images and flash movies, also adds media to media library
* Create a Post using event details
* Admin Settings:
	Email throttle - set how many emails are sent per a set time
	Categories - Uses the post categories for event categories, so you can select which categories you want to include on the events page.
	Auto Removal - Set how many days after an event ends to remove from the list and calendar.
	Max length for event titles and descriptions
	# of events to list per day in the full page calendar
	Char Length of event title to show in full page calendar
	Custom CSS.
	

== Installation ==

1. Unpack alieneila-event-calendar.zip

1. Upload folder to the '/wp-content/plugins/' directory

2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
None yet, view the forums at http://area51.alieneila.net

== Changelog ==
= 1.9.91b =
* Fixed a line that was making the RSVP count show up when it wasn't supposed to.
* Possibly fixed a PHP Warning in the Event List Widget
* Added an option to include an excerpt in the mail notifications sent out.

= 1.9.9b =
* Not sure why, but when I uploaded the new files last night, the widget file uploaded without all of the data in it =/ So here it is again.

= 1.9.8b =
* Fixed a <? in one of the files that should have been an <?php

= 1.9.7b =
* Added 0 option to remove after so many days to disable automatically deleting the events.
* Fixed a bug that prevented child and grandchild events showing up in the backend so that the events could not be edited.

= 1.9.6b =
* Added target setting for click on events in the full calendar.

= 1.9.5b =
* Attempt to fix a bug with category names breaking the display of the events in the admin when using ampersand symbols
* Fixed some typos in the language file that I had played with to make sure the language file was working.

= 1.9.4b =
* There was a bug introduced where one time events were not automatically being deleted, this should be fixed.
* You should now be able to use shortcodes of other plugins in your event posts.
* Finally figured out the language thing (I think). You can find the language folder in the wp-content>plugins>alieneila-event-calendar folder

= 1.9.1b =
* Thanks to Chris Respeto, the new capability access is now compatible with the multisites roles

= 1.9b =
* Added ability for administrator to add access to editors, authors and contributors to access adding and editing events.
* Fixed some CSS issues
* Fixed a couple link issues in full calendar
* Added money sign settings in Internationalization section
* Fixed a bug with duration not properly setting when editing an event
* Time/Date on the full calendar and widget calendar "should" now be using the Word Press time offset based on GMT/UTC time
* Redid an algorythm for repeating dates and auto updating the next day an event should occur on. THis should speed up some page load times if your site has a lot of events in the database.
* Added the shortccode [event-cal-mini] to display the mini calendar wherever you want.
* Fixed a bug with daily repeating events showing up on days outside of their scope
* Added an "All" to the category drop down list to reset the category filters
* I think I did some more things, but I can't rememeber. If I missed any suggestions/updates/bugs please post on the forum or submit a contact form request, as things have been pretty hectic.


= 1.8.1b =
* Pretty sure I fixed the floating div issue on the full calendar in 1.8.1b.

= 1.8b =
* I've been fixing a couple bugs here and there in between my busy work schedule, so I've forgotten everything I've fixed lol. Sorry.

= 1.7.8b =
* Fixed a CSS issue.

= 1.7.8b =
* Fixed some link issues (helps if I put an echo in to actually add the url to the link... duh!)
* Added a span id to the event details page. In the CSS the myEventTitle will now effect the section titles. In the Layout Form, this is the My Event Title field.
* Added an additional field for Urls instead of using the location field for both the location and the url
* Fixed a bug when selecting to view events by category.

= 1.7.7b =
* Fixed a typo with the plugin url and added another setting to set the page_id in the links if you're using a different page than the one the plugin creates.

= 1.7.6b =
* Fixed a couple lines of code I missed in 1.7.5b

= 1.7.5b =
* Since quite a few people were reporting issues with Word Press 3.0, I added another subdomain to my site and did a fresh WP 3.0 install. I then figured out the issues and got the plugin to work. I also 
added a new setting in the Main Settings area for those who may be having problems with the plugin not linking correctly if Wordpress is installed in a subdirectory where you can edit the url for your site. There are a ton 
of places in the plugin where links are generated, so if you find a link that is not correctly using this new setting that I may have missed, please let me know.

= 1.7.4b =
* Another attempt at fixing some problems with WP 3.0
* More code cleanup

= 1.7.3b =
* Some Minor code cleanup.

= 1.7.2b =
* An attempt to fix wp_register_widget_control() errors with WP 3.0

= 1.7.1b =
* An attempt to fix a "You do not have sufficient permissions to access this page." someone is getting.

= 1.7b =
* 
* Fixed a bug in the new event add from the form page that messed up things when descriptions or titles had special characters.

= 1.6.1b && 1.6.2b =
* Fixed some linking issues.

= 1.6b =

* Added a box below the post form to add an event from the post form page. (You may have to scroll down depending on how many other plugins have added meta boxes to the form)
* Added option to settings: List Date: Current or All - will display in the event list either the events matching the current date, or all events.
* Added option to settings: # of events: Will limit the number of events displayed in the event list.
* Added a counter to the event view for admins to view. Note: I have noticed that if you have a cache plugin, the counter will increment by 2. I'm guessing because the cache plugin does a query of the event as well?
* Added a view of the users who have RSVP's to events to the admin event view.
* Fixed a bug where IE displayed a broken image if no media was added to the event.

= 1.5b =

* Added option to settings to display category list as a drop down menu.
* Added an option to settings to select the start of the week to be Sunday or Monday.

= 1.4b =
* Fixed a bug I introduced with 1.3.4b that wouldn't allow to view the event details.
* Added some international settings for provinces and countries and disabling the auto format for phone numbers.

= 1.3.6b =
Floating popup on mouseover for Full Calendar

= 1.3.5b =
Started adding functionality for language files.

= 1.3.4b =
* Added a setting to set the Event List or Full Calendar to default view on the Event Page
* Added a nested list to the event list widget for events on the same day
* Fixed the default selection for certain day of week/month when editing an event.

= 1.3.3b =
Fixed a typo that was preventing the RSVP link from showing up on event details.

= 1.3.2b =
Fixed a bug in the list widget.

= 1.3.1b =
Edited the links in the widget calendar so that they should still work on installs that are in folders.

= 1.3b =
Added a widget to display events in a list. See release notes.

= 1.2b =
Added function to browse full calendar by categories.

= 1.1b =
Attempt to fix a PHP function that appears to not work on some versions of PHP

= 1.0b =
Released for Testing

== Screenshots ==

None yet, view http://area51.alieneila.net

== To Do ==
* Add color picker to new form styling system
* Make it more SEO compliant