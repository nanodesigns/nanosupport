=== NanoSupport ===
Contributors: nanodesigns, wzislam
Tags: helpdesk, support, support desk, support plugin, support ticket. ticket plugin, ticket system, ticketing system, help desk, wp support ticketing, tickets, help, support staff, support ticketing
Requires at least: 4.4.0
Tested up to: 4.6
Stable tag: 0.2.0
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Create a fully featured Support Center within your WordPress environment without any third party system dependency, completely FREE

== Description ==
Create a fully featured Support Center within your WordPress environment without any third party system dependency, for completely FREE of cost.

**No** 3rd party support ticketing system required, **no** external site/api dependency, **simply** create your own fully featured Support Center within your WordPress environment, and take your support into the next level.

= What is it? =

The plugin is to provide support to your users - the users those are taking product or services from you. So the plugin provides a managable communication privately in between you and your that specific user only. Visit the 'Installation' tab for more details on how to use the plugin.

= Features =
* OnActivation setup
* Smart templating for nice theme support
* Smartly designed Support Center
* Completely Private ticketing
* Ticket submission with registration
* Ticket submission with login (Beta Feature)
* Auto generate user account's username on ticket submission (if chosen)
* Auto generate user account's password on ticket submission (if chosen)
* Knowledgebase
* Knowledgebase content categories
* Ticket departments
* Default ticket department 'Support'
* Make agent from registered users
* Assign ticket to an agent
* Change ticket status (Pending, Open, Under Inspection, Solved)
* Set support priority (Low, Medium, High, Critical)
* Reply ticket from admin panel
* Reply ticket from front end
* ReOpen closed ticket
* Internal Notes in-between support teams
* Shortcode-enabled pages (installed on Plugin activation)
* Settings page (Settings API)
* Support Seeker user role and privileges
* Dashboard widget with current status
* Dashboard widget with recent activity
* Dashboard widget with personal status for Agents
* Dashboard widget with necessary instruction and links for Support Seekers
* Add ticket on behalf of other user (admin end)
* Customizable Email template
* Email notification to admins on new ticket submission
* Email notification to Support Seeker on account creation on ticket submission
* Email notification to Support Seeker on ticket reply
* Email notification to Support Agent on ticket reply
* NanoSupport page-to-page navigation and pagewise notices
* Complete data deletion on uninstallation (if chosen)
* 100% Translation-ready
* Fully responsive and Mobile devices friendly
* Clean and well commented code

= Contribute =
NanoSupport is an Open Source and GPL licensed Free plugin. Feel free to contribute.

* [Fork on Github](https://github.com/nanodesigns/nanosupport)
* [Report Bug](https://github.com/nanodesigns/nanosupport/issues)
* [Get Support](http://wordpress.org/support/plugin/nanosupport)

= Translation =
The plugin is completely translation-ready. You can find the `.pot` file under `i18n/languages/` if you want to translate in your own way. But you can translate it easily from here in [**Translate NanoSupport**](https://translate.wordpress.org/projects/wp-plugins/nanosupport).

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
3. Get to the Plugin\'s 'Settings' page, and set up the plugin as your choice
4. Wait for your first ticket

= Manual Installation =

1. Visit the plugin page at: https://wordpress.org/plugins/nanosupport
2. Hit the Download button to download the `.zip` file
3. Unzip the archive and cut/paste the folder to `/wp-content/plugins/` directory
4. From admin 'Plugins' page, activate NanoSupport plugin
5. Get to the Plugin\'s 'Settings' page, and set up the plugin as you like
6. Wait for your first ticket

= How to Use =

1. Provide the **'Submit Ticket'** page's URL to your menu link, and ask for Support tickets from your users (Use 'Settings' page for necessary settings)
2. When the user submits ticket you will be notified via email (as per your 'Settings')
3. You can find the tickets in the **'Support Desk'**
4. You can organize your tickets per department
5. You can read the ticket in details and can answer from both front-end and admin-end
6. As the tickets are completely private, for public information that you want to share with your users, you can use **'Knowledgebase'**
7. You can organize knowledgebase docs in categories
8. And you have many flexibilities (and many more yet to come...) using the plugins 'Settings' page

== Frequently Asked Questions ==

= Q: How to install the Plugin? =
**A:** Please visit the plugin's Installation page to get the details.

= Q: How to set up the Plugin? =
**A:** After activation you can get the 'NanoSupport' admin menu on the left, and under that menu page, you will get the 'Settings' page to set up the plugin.

= Q: What are the shortcodes? =
**A:** On installation the plugin will automatically create its necessary pages with related shortcodes. But if it misses by any chance, here are the shortcodes:

* Submit Ticket page: `[nanosupport_submit_ticket]`
* Support Desk page: `[nanosupport_desk]`
* Knowledebase page: `[nanosupport_knowledgebase]`

= Q: How to disable the page-to-page navigation and notice =
**A:** On plugin's 'Settings' page, in 'General' tab, you can **uncheck** the *Enable Notices and Navigation on NanoSupport pages to disable them.

= Q: How the plugin can be well integrated with my theme? =
**A:** NanoSupport has smart templating. You can find all its templates inside the `/templates/` directory. To make your theme completely integrated with the plugin\'s templates you can simply make a directory in your theme named `nanosupport/` and can overrite the plugin's templates with your custom one.

== Screenshots ==
1. NanoSupport | Support Desk (front end)
2. NanoSupport | Support Tickets (back end)
3. NanoSupport | Submit Ticket page for Visitors
4. NanoSupport | Fully featured Settings page for complete customization
5. NanoSupport | Knowledgebase
6. NanoSupport | Ticketing at a glance with NanoSupport dashboard widget
7. NanoSupport | Customizable email template
8. NanoSupport | Easy navigation, and noticiation for easy ticketing using Admin bar

== Changelog ==

= 0.2.0 - 2016-AUG-31 =
=== Major changes ===
* Knowledgebase made optional
* UI added for selecting Knowledgebase Icons
* Closing a ticket from front end
* CSS Tooltip added to Submit form for better UX
* Submit Form is made dynamic for adding feature to the `<form>` element
* Ticket Attachment feature is added for Questions

=== Minor changes/fixes ===
* Knowledgebase CSS revamped
* Fixed a conflict with Yoast SEO and Select2 plugin in admin areas
* File organization, some CSS files made LESS for easy compilation
* JavaScript fallback plan implemented for front end
* 3 other fixes/UI improvements

= 0.1.1 - 2016-JUN-21 =
* A bug on Submit ticket is fixed

= 0.1.0 - 2016-JUN-17 =
* Plugin initiated

== Upgrade Notice ==

= 0.2.0 =
Minimum WordPress Version 4.4.0 required. 3 new major features added