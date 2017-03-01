=== NanoSupport ===
Contributors: nanodesigns, wzislam
Tags: helpdesk, support, support desk, support plugin, support ticket, ticket plugin, ticket system, ticketing system, help desk, wp support ticketing, tickets, help, support staff, support ticketing, knowledge base, knowledgebase, faq, frequently asked questions
Requires at least: 4.4.0
Tested up to: 4.7.2
Stable tag: 0.3.2
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Create a fully featured Support Center in your WordPress setup without any third party dependency, completely FREE. Get a built-in Knowledgebase too.

== Description ==
Create a fully featured Support Center within your WordPress environment without any third party system dependency, for completely FREE of cost.

**No** 3rd party support ticketing system required, **no** external site/API dependency, **simply** create your own fully featured Support Center within your WordPress environment, and take your support into the next level.

It has built-in Knowledgebase that is integrated to put generalized information for public acknowledgement.

= What is it? =

The plugin is to provide support to your users - the users those are taking product or services from you. So the plugin provides a manageable communication privately in between you and your that specific user only. Visit the 'Installation' tab for more details on how to use the plugin.

= Features =
* OnActivation setup
* Smart templating for nice theme support
* Smartly designed Support Center
* Completely Private ticketing
* Ticket submission with registration
* Ticket submission with login (Beta Feature)
* Rich Text editor for new ticket
* Auto generate user account's username on ticket submission (if chosen)
* Auto generate user account's password on ticket submission (if chosen)
* Knowledgebase (optional)
* Knowledgebase content categories
* Ticket departments
* Make agent from registered users
* Assign ticket to an agent
* Change ticket status (Pending, Open, Under Inspection, Solved)
* Set support priority (Low, Medium, High, Critical)
* Reply ticket from admin panel
* Reply ticket from front end
* ReOpen closed ticket
* Internal Notes in-between support teams
* Shortcode-enabled pages (installed on plugin activation, but modifiable)
* Settings page (WP Settings API)
* Support Seeker user role and privileges
* Dashboard widget with current status in charts
* Dashboard widget with recent activity
* Dashboard widget with personal status for Agents in charts
* Dashboard widget with necessary instruction and links for Support Seekers
* Add ticket on behalf of other user (admin end)
* Customizable HTML Email template
* Email notification to admins on new ticket submission
* Email notification to Support Seeker on account creation on ticket submission
* Email notification to Support Seeker on ticket reply
* Email notification to Support Agent on ticket reply
* NanoSupport page-to-page navigation and page-wise notices (on demand)
* Complete data deletion on uninstallation (if chosen)
* 100% Translation-ready and automatic translation enabled
* Fully responsive and Mobile devices friendly
* Clean and well commented and well documented code

= Contribute =
NanoSupport is an Open Source and GPL licensed Free plugin. Feel free to contribute.

* [Fork on Github](https://github.com/nanodesigns/nanosupport)
* [Report Bug](https://github.com/nanodesigns/nanosupport/issues)
* [Get Support](https://github.com/nanodesigns/nanosupport/issues)

= Translation =
The plugin is completely translation-ready. You can find the `.pot` file under `i18n/languages/` if you want to translate in your own way. But you can translate it easily from here in [**Translate NanoSupport**](https://translate.wordpress.org/projects/wp-plugins/nanosupport).

Or, you can use software like [POEdit](https://poedit.net/download) ('cross-platform) or [EasyPO](http://www.eazypo.ca/), and using the plugins' `.pot` file you can easily translate the plugin locally.

= Connect with NanoSupport team =
* [Website](http://nanodesignsbd.com?ref=nanosupport)
* [Twitter](https://twitter.com/nanodesigns/)
* [Facebook](https://facebook.com/nanodesignsbd/)
* [LinkedIn](http://www.linkedin.com/company/nanodesigns)
* [Google+](https://google.com/+Nanodesignsbd)

== Installation ==
= Automatic Installation =

1. In WordPress Plugins page, search for NanoSupport
2. Install and activate the plugin
3. Get to the plugin\'s 'Settings' page, and set up the plugin as your choice
4. Wait for your first ticket

= Manual Installation =

1. Visit the plugin page at: https://wordpress.org/plugins/nanosupport
2. Hit the Download button to download the `.zip` file
3. Unzip the archive and cut/paste the folder to `/wp-content/plugins/` directory
4. From admin 'Plugins' page, activate NanoSupport plugin
5. Get to the plugin\'s 'Settings' page, and set up the plugin as you like
6. Wait for your first ticket :)

= How to Use =

1. Provide the **'Submit Ticket'** page\'s URL to your menu link, and ask for Support tickets from your users (Use 'Settings' page for necessary settings)
2. When the user submits ticket you will be notified via email (as per your 'Settings')
3. You can find the tickets in the **'Support Desk'**
4. You can organize your tickets per department
5. You can read the ticket in details and can answer from both front-end and admin-end
6. As the tickets are completely private, for public information that you want to share with your users, you can use **'Knowledgebase'**
7. You can organize Knowledgebase docs in categories
8. And you have many flexibilities (and many more yet to come...) using the plugin\'s 'Settings' page

== Frequently Asked Questions ==

= Q: Is there any User Guide to using NanoSupport? =
**A:** Yes, please consult the [**NanoSupport User Guide**](https://github.com/nanodesigns/nanosupport/wiki).

= Q: How to install the Plugin? =
**A:** Please visit the plugin\'s Installation page to get the details.

= Q: How to set up the Plugin? =
**A:** After activation you can get the 'NanoSupport' admin menu on the left, and under that menu page, you will get the 'Settings' page to set the plugin as your choice.

= Q: What are the shortcodes? =
**A:** On installation the plugin will automatically create its necessary pages with related shortcodes. But if it misses by any chance, here are the shortcodes:

* Submit Ticket page: `[nanosupport_submit_ticket]`
* Support Desk page: `[nanosupport_desk]`
* Knowledgebase page: `[nanosupport_knowledgebase]`

= Q: How to disable the page-to-page navigation and notice =
**A:** On plugin\'s 'Settings' page, in 'General' tab, you can **uncheck** the *Enable Notices and Navigation on NanoSupport pages* to disable them.

= Q: How the plugin can be well integrated with my theme? =
**A:** NanoSupport has smart templating. You can find all its templates inside the plugin's `/templates/` directory. To make your theme completely integrated with the plugin\'s templates you can simply make a directory in your theme named `nanosupport/` and can override the plugin\'s templates with your custom one.

= Q: How to make a Support Agent? =
**A:** Edit any WordPress user in WordPress admin panel and at the bottom, check the checkmark saying: *Yes, make this user a Support Agent* to make the user a support agent.

== Screenshots ==
1. NanoSupport | Support Desk (front end)
2. NanoSupport | Support Tickets (back end)
3. NanoSupport | Submit Ticket page for Visitors
4. NanoSupport | Fully featured Settings page for complete customization
5. NanoSupport | Knowledgebase
6. NanoSupport | Ticketing at a glance with NanoSupport dashboard widget
7. NanoSupport | Customizable HTML email template
8. NanoSupport | Easy navigation, and notification for easy ticketing using Admin bar

== Changelog ==

= 0.3.2 - 2017-MAR-02 =
* Ticket update date fixed syncing with replies
* Bug Fixed: pending ticket link was not working in agent notifying email

= 0.3.1 - 2017-JAN-24 =
* Fixed: Assigning ticket to Support Agent was not triggering any email
* New filter hook: `nanosupport_notify_agent_assignment`
* Fixed: Users can set themselves as a Support Agent
* Priority assignment by public made dynamic using Settings API
* Various i18n functions fixed
* Several other minor fixes all over the plugin code and texts

= 0.3.0 - 2016-DEC-24 =
**Major changes**

* Rich Text editor for ticket body text
* New filter hook: `ns_wp_editor_specs`, `ns_back_to_knowledgebase`
* Pending ticket notification using admin menu bubble
* Fixed translation strings to strip out dumped HTML and made most of them SQL injection proof
* Composer, npm dependency and Grunt incorporated for easy development collaboration
* Default assignment of 'Support' department is made deprecated
* User can choose Support Department on submitting new ticket (Settings available)
* CSS is changed from LESS to Sass
* System Status admin page

**Minor changes**

* Reorganized third party libraries
* contributing.md added
* Github issue template added
* A warning on upgrading process fixed - props @prionkor
* jshint error fixed with procedural code
* Agent email was not sending - fixed
* Ticket response content added to email body
* Back link to Knowledgebase added after Knowledgebase contents

= 0.2.2 - 2016-SEP-03 =
* Ticket character limit can be set by user
* Response character limit is deprecated
* User can close ticket without submitting any response
* Ticket content is added to the new ticket notification email
* Fixed: Ticket with registration was not working on WordPress registration settings
* Some translation strings are fixed

= 0.2.1 - 2016-AUG-22 =
* `wp_kses()` is implemented to secure site from bad user input
* New filter hook: `ns_allowed_html`
* Tooltip made wider for large bunch of texts
* Stripping 'private' and 'protected' from ticket titles i18n-friendly
* Translation added: Bengali (*Bengali*) - `bn_BD`

= 0.2.0 - 2016-AUG-15 =
* Minimum WordPress version upgraded to 4.4.0
* Knowledgebase made optional
* UI added for selecting Knowledgebase Category Icons
* Added feature for closing a ticket from front end
* Tooltip added to Submit form for better UX
* Submit Form is made dynamic for adding feature to the `<form>` element
* Select2 Plugin updated to 4.0.3
* Knowledgebase CSS revamped
* Fixed a conflict with Yoast SEO and Select2 plugin in admin areas
* File organization, some CSS files made LESS for easy compilation
* JavaScript fallback plan implemented for front end
* Other fixes/UI improvements

= 0.1.1 - 2016-JUN-21 =
* A bug on Submit ticket is fixed

= 0.1.0 - 2016-JUN-17 =
* Plugin initiated

== Upgrade Notice ==

= 0.3.1 =
2 bug fixed and 1 dynamic feature added.
