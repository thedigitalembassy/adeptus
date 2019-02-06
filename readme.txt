=== Adeptus ===
Contributors: pjgalbraith
Tags: wordpress security plugin, wordpress security audit log, audit log, activity logs, event log wordpress, wordpress user tracking, wordpress activity log, wordpress audit, security event log, audit trail, wordpress security monitor, wordpress admin, wordpress admin monitoring, user activity, admin, multisite,  wordpress monitoring, tracking, user tracking, user activity report, wordpress audit trail
Requires at least: 4.0
Tested up to: 5.0
Stable tag: trunk
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 5.6

Adeptus is a powerful, extendable WordPress activity log plugin.

== Description ==
Adeptus is a lightweight plugin that allows you to send WordPress website logs to syslog, logstash, debug.log and error_log for you to collect, store, process and monitor your logs seamlessly.

With Adeptus you have the ability to
- Monitor all website and CMS activity in real time
- Audit stored logs information for security and debuggin purposes
- Visualize log data in 3rd party apps (Grafana, Kibana, etc..)
- Quickly generate reports in 3rd party apps

Adeptus is developed and maintained by [The Digital Embassy](https://www.thedigitalembassy.co), a Digital Agency in Adelaide South Australia.

= Blazingly fast =
Adeptus follows the philosphy of doing one thing really well, and that is collecting event data. How that data is stored and where it is sent is fully configurable. This makes Adeptus lightweight and very fast.

= Extensive logging providers =
Adeptus comes bundled with the ability to send logs to a number of providers, listed below. Need additional options? We follow the PSR-3 PHP standard so you can write your own provider or [contact us](https://www.thedigitalembassy.co/Contact-Us) for assistance.

- Debug.log
- Syslog (stored in `/var/log` or Windows event viewer)
- Logstash
- PHP error_log

= You control it all — forever =
Adeptus gives you complete control of your activity data. There's no limit for how much data is stored, or for how long - this is entirely up to you!

= Built with developers in mind =
Extendable, adaptable, and open source — Adeptus was created with developers in mind. With its extendable architecture you can use a number of drivers to connect and monitor you application event data.

Check out [https://github.com/thedigitalembassy/adeptus](https://github.com/thedigitalembassy/adeptus) for additional developer documentation.

== Installation ==

= Minimum Requirements =

* PHP version 5.6.0 or greater (PHP 7.2 or greater is recommended)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of Adeptus, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “Adeptus” and click Search Plugins. Once you’ve found our plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you regularly backup your site just in case.

= Configuring a provider =

In order to store event logs you will need to configure a log provider. See [our readme](https://github.com/thedigitalembassy/adeptus) for more info.

== Frequently Asked Questions ==

= Where can I request new features, and providers? =

You can request new features on [Github](https://github.com/thedigitalembassy/adeptus/issues)

= Where can I report bugs or contribute to the project? =

Bugs can be reported either in our support forum or preferably on our [GitHub repository](https://github.com/thedigitalembassy/adeptus/issues).

= Adeptus is awesome! Can I contribute? =

Yes you can! Join in on our [GitHub repository](https://github.com/thedigitalembassy/adeptus) :)

== Screenshots ==

1. The Adeptus settings panel.

== Changelog ==

[See changelog for all versions](https://raw.githubusercontent.com/thedigitalembassy/adeptus/master/CHANGELOG.txt).