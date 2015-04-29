=== Bookly - Responsive Appointment Booking Application (Lite Version) ===

Contributors: Ladela
Tags: appointment booking, booking calendar, booking, business, responsive, hair salon, personal trainer
Requires at least: 3.7
Tested up to: 4.0.1
Stable tag: 3.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Bookly is a easy-to-use and easy-to-manage booking tool for Service providers who think about their customers.

== Description ==

Bookly Plugin – is a great easy-to-use and easy-to-manage booking tool for service providers who think about their customers. Plugin supports wide range of services, provided by business and individuals service providers offering reservations through websites. Setup any reservations quickly, pleasantly and easy with Bookly!

= Key features: =

* User friendly responsive touch optimized front end design
* Comprehensive admin area with intuitive interface
* Easy WordPress integration
* Multi languages support: language files can be uploaded to WordPress
* Dozens of options to fit your business model
* Weekly calendar view for each service provider
* Unlimited colors
* Translation ready
* Unlimited number of services
* Variable timeslot length
* Personalized provider week schedule with unlimited number of breaks
* Configurable holidays and days-off
* Importable and manageable list of customers
* Customizable appearance settings
* Export of bookings to CSV

= Features available in full version: =

* Google calendar integration
* Daily calendar overview for all or selected service providers
* Unlimited number of service providers
* Personalized price of service for a provider
* PayPal payments
* Editable email notifications to customers and service providers
* Configurable email reminders
* Configurable payments report

= Plugins Resources =

[Home Page](http://booking-wp-plugin.com/  "WP Appointment Booking Plugin" ) |
[Documentation](http://booking-wp-plugin.com/documentation/ "WP Appointment Booking Plugin Docs" ) |
[Full version demo](http://demo.booking-wp-plugin.com/ "WP Appointment Booking Plugin Demo" ) 

= How it works =

After a simple installation process, the website owner can start creating services, which can be provided to the customer upon request: events, meetings, lessons, consultations etc. Using integral managing tools, administrators can easily change and customize settings such as set individual schedule for each service provider, set individual service prices, define working days and hours for the whole company and for each service provider separately, manage services, customers, service providers and their availability, view billing information and payment reports with filters on multiple criteria, add and edit appointments, etc. The plugin also has an option to reserve appointments manually through the WP admin panel.

Bookly offers interesting solutions to make booking process pleasant for customers: the only thing that is required from the client is to complete 5 easy steps. The intuitive interface, as well as clear and attractive design make this process extremely easy. Customers have the possibility to find time convenient for appointments, select service provider, book time and pay for services. One more thing that will please your clients is the ability to view the available booking time intervals in their own time zone.

Let’s say you have installed the Bookly plugin, but its default design doesn’t correspond to the appearance of your website. Our developers took care of everything: you can easily modify the booking form design according to your own needs. It’s possible to select the main color and change the fields’ titles and descriptions.

The plugin offers easy and comfortable way of communication between service providers and customers by using e-mail notifications (available in pro version). There are several types of notifications for different cases: reminders, confirmations, follow-ups, agendas etc. You may also send notifications to your staff members, for example to give some information about the next agenda or appointment details. Website owner can manage all notifications settings in the WP admin panel.

Customizable calendar allows looking through appointments schedule for particular service provider and can be displayed in two modes: by weeks and by days. Administrators can also create new appointments and edit the existing ones. There is also an option to assign the color for each category of services for visual convenience.


== Frequently Asked Questions ==

= How can I embed the booking form into a post/page? =

You can find a button “Add Booking Form” above content editor for a post or a page.

== Screenshots ==

1. Booking process for a customer

2. Backend features

== Installation ==

= Minimum Requirements =

* WordPress 3.6 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install of Bookly, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "Bookly" and click Search Plugins. Once you have found our plugin you can install it by simply clicking Install Now. After clicking that link you will be asked if you are sure you want to install the plugin. Click yes and WordPress will automatically complete the installation.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your web server via your favorite FTP application.

1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installations wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

= Getting started =

When the title of the plugin has appeared on your dashboard, you may start the setup. The following steps are required to make it possible for customers use your newly installed plugin:

* Enter company details (name, address, etc.);
* Set company’s working hours;
* Define category of services and list of services in each category;
* Determine price and duration of services;
* Determine service staff member;
* Set working schedule for staff member;
* Add a booking form to a website page.

Upon the completion of these steps, customers can book appointments. In the Calendar section of the administrators view, administrators can add the information about appointments manually. Site owner can also look through and set:

* Appearance mode for appointment information;
* Individual schedule provided by each staff member;
* Individual service prices provided by each staff member;
* Set holidays for the whole company and for each staff member separately;
* List of customers with contact information;
* Notification by email;
* Payment reports with filters on multiple criteria;
* Payment methods;
* Booking form appearance modes.

== Changelog ==

= 3.3 =
* Added option to hide date and time selection at the first step
* Fixed import of customers

= 3.2.2 =
* Fixed setting GC calendar ID
* Fixed regression bugs

= 3.2.1 =
* Added 2 way sync with Google Calendar via new API
* Added coupons
* Restored notes in the backend calendar when capacity is 1

= 3.1 =
* Added new replacement [[SERVICE_PRICE]] for email notifications
* Added delimiter setting for import from CSV
* Fixed regression bug in export to Google Calendar

= 3.0.1 =
* Fixed regression bug in cron notifications
* Fixed regression bug in displaying price next to employee name

= 3.0 =
* Added support for multiple bookings
* Added 5, 10 and 12 minutes time slots
* Added - Take in account Google Calendar events when displaying available time slots
* Added - Pre populate name and email for logged in customers
* Added - Sort categories, services and employees alphabetically

= 2.2.3 =
* Fixed displaying step 5 after PayPal payment (regression bug)

= 2.2.2 =
* Fixed bug when deleting appointment after cancellation
* Fixed bug in displaying customers list

= 2.2.1 =
* Fixed [[CATEGORY_NAME]] in cron notifications
* Fixed service selection when go backwards
* Fixed regression bugs

= 2.2.0 =
* Since this version Bookly requires at least PHP 5.3
* Added new payment method - Stripe
* Skip payment step for free services
* Fixed issues with HTTPS
* Fixed cancellation link for appointments created in the backend

= 2.1.0 =
* Added Authorize.net support
* Added new replacement [[CATEGORY_NAME]] both in appearance and email notifications settings
* Bug fixes

= 2.0.1 =
* WordPress 4.0 support
* Fixed translation into French
* Fixed cron notifications
* Fixed Google Calendar formatting
* Clear plugin data upon uninstalling

= 2.0 =
* Added automatic export to Google Calendar
* Added time zones support
* Added time slot length setting
* Added customer search field when editing appointment
* Added cancel appointment link
* Added 22 new currencies
* Added possibility to edit form labels
* Added possibility to enter CSS code for main color
* Added setting to disable last minute appointments
* Added export appointments feature
* Added import customers feature
* Fixed first day of week

= 1.2.0 =
* Added new field Notes at Details step
* Added RUB, SEK, DKK
* Fixed date and time in email notifications on non-English web sites

= 1.1.9 =
* New replacement [[STAFF_NAME]] in Appearance (old replacements [[BY_SERVICE_PROVIDER_NAME]] and [[BY_STAFF_NAME]] are deprecated)
* Translation into French
* Fixed bug with datepicker on non-English web sites

= 1.1.8 =
* Fixed sending emails to staff
* Updated translations

= 1.1.7 =
* Added decimal prices
* Don’t display non-working days
* Bug fixes

== Upgrade Notice ==

= 2.0.1 =
Upgrade now for full compatibility with WordPress 4.0