=== NanoSupport ===
Contributors: nanodesigns, wzislam
Tags: helpdesk, support desk, support ticket, ticket plugin, ticket system, ticketing system, help desk, wp support ticketing, support staff, support ticketing, knowledge base, support plugin
Requires at least: 4.4.0
Tested up to: 4.9.5
Requires PHP: 5.4.0
Stable tag: 0.4.0
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
* Copy ticket content into Knowledgebase document
* Ticket departments
* E-Commerce Support - Support to products of Easy Digital Downloads (EDD) and WooCommerce (WC)
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
* A11y (web accessibility) compatible

= E-Commerce Support =

To get E-Commerce Support, you will need either:

* WooCommerce (WC) v2.5+ - Tested up to v3.3.5, or
* Easy Digital Downloads (EDD) v2.2+ - Tested up to v2.9.1

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
3. Get to the plugin's 'Settings' page, and set up the plugin as your choice
4. Wait for your first ticket

= Manual Installation =

1. Visit the plugin page at: https://wordpress.org/plugins/nanosupport
2. Hit the Download button to download the `.zip` file
3. Unzip the archive and cut/paste the folder to `/wp-content/plugins/` directory
4. From admin 'Plugins' page, activate NanoSupport plugin
5. Get to the plugin's 'Settings' page, and set up the plugin as you like
6. Wait for your first ticket :)

= How to Use =

1. Provide the **'Submit Ticket'** page's URL to your menu link, and ask for Support tickets from your users (Use 'Settings' page for necessary settings)
2. When the user submits ticket you will be notified via email (as per your 'Settings')
3. You can find the tickets in the **'Support Desk'**
4. You can organize your tickets per department
5. You can read the ticket in details and can answer from both front-end and admin-end
6. As the tickets are completely private, for public information that you want to share with your users, you can use **'Knowledgebase'**
7. You can organize Knowledgebase docs in categories
8. And you have many flexibilities (and many more yet to come...) using the plugin's 'Settings' page

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

= Q: How I can provide support for my e-commerce products? =
**A:** With the version 0.5.0+ the plugin has support for WooCommerce and Easy Digital Downloads (EDD). Simply activate support for E-Commerce in NanoSupport Settings, and there would be 2 new fields available for providing Support for E-Commerce products that will integrate with the active WC/EDD products, and their receipts. You can exclude certain products from the settings also.

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

= 0.5.1 - 2018-SEP-30 =
* New filter hook: `ns_ticket_responses_arg`

= 0.5.0 - 2018-MAY-11 =
* New Feature: E-commerce &mdash; Support to products of Easy Digital Downloads (v2.5+) and WooCommerce (v2.2+)
* Additional Feature: Let the admin end users filter tickets based on Priority and/or Status and/or Agent
* 7 new icons to NanoSupport Icons
* New filter hooks: 'ns_date_time_format', 'ns_mandate_product_fields'
* Issue Fixed: Email Template background was repeating
* Issue Fixed: Date Time format was not changeable
* Issue Fixed: Throwing javascript error in browser console due to old focus-ring.js
* Minor fixes on A11y issues

= 0.4.1 - 2017-JUL-23 =
* Bug fixed: Ticket additional info was affecting on show/hide in smaller devices
* Bug fixed: Ticket assignment on behalf of support seeker was getting only administrators
* Code of Conduct added for contributors
* Some other minor fixes

= 0.4.0 - 2017-JUN-09 =
* New Feature: Ticket can be copied into a Knowledgebase document
* Requested Feature: ReEngineering Knowledgebase Doc URL strucuture
* Bug fixed: Ticket cannot be added on behalf of client
* New filter hook: `ns_nanodoc_arguments`, `nanosupport_copied_content`, `nanosupport_assigned_user_role`

[See changelog for all versions](https://github.com/nanodesigns/nanosupport/blob/master/CHANGELOG.txt).

== Upgrade Notice ==

= 0.5.1 =
Modified the 'content-single-ticket' template file to add new filter hook.
