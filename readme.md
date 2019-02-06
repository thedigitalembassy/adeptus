# Adeptus

## Introduction

Adeptus is a lightweight plugin that allows you to send WordPress website logs to syslog, logstash, debug.log and error_log for you to collect, store, process and monitor your logs seamlessly.

With Adeptus you have the ability to:

 - Monitor all website and CMS activity in real time
 - Audit stored logs information for security and debuggin purposes
 - Visualize log data in 3rd party apps (Grafana, Kibana, etc..)
 - Quickly generate reports in 3rd party apps

## Built-In Events

Attachments

    Created
    Updated
    Deleted/Trashed

Core

    Wordpress updated

Menus

    Created
    Deleted
    Menu item added/update/moved/deleted

Options

    Option changes
    
Plugins

    Installed (covered by plugin activation)
    Activated/Deactivated
    Updated
    Deleted (covered by plugin deactivation)

Themes

    Activated/Deactivated 
    Updated

Posts (inc. all custom post types)

    Created
    Updated
    Deleted/Trashed
    Published

Taxonomies

    Category created
    Category moved
    Category updated
    Category deleted

Users

    Login
    Logout
    Profile updated
    Password changed
    Password reset
    Role updated
    Created
    Deleted/Trashed

Fatal PHP Errors/Exceptions

    Ability to disable logging per request (e.g. during imports)

Comments

    Created
    Change Status (Spam/Deleted/Trashed)

Widgets (Options)

    Added
    Updated
    Removed

WooCommerce

    Woocommerce Options
    Add / Edit / Delete Product
    Update Product Variation
    Ppdate Product Attribute

Other Options 

    ACF options page(s) (i.e. global content)
    Yoast (settings changes)

## Custom Events

Sometimes we need to run some custom events (i.e. running cronjobs or importing some data) which contains thousand of events or db updates. To prevent storing to many logs on the server, it's better to ignore the events caused by the custom script.  

## Enable/Disable Logging in Custom Events

To disable and enable logging in custom events such as importing scripts wrap the script you don't want to be logged between following function calls:

```php
\Adeptus::disableLogging();

// your code here...

\Adeptus::enableLogging();
```

In this case the events between these 2 lines won't be logged.

### Example

```php
function do_import() 
{
    \Adeptus::logEvent([
        'alert_code'        => 20080,
        'alert_level'       => \Psr\Log\LogLevel::INFO,
        'alert_title'       => 'Import Started',
        'alert_description' => 'Import Started',
        'sensor'            => 'ImportExample'
    ]);

    \Adeptus::disableLogging();

    // Perform import
    update_option('blogname',rand());

    \Adeptus::enableLogging();

    \Adeptus::logEvent([
        'alert_code'        => 20080,
        'alert_level'       => \Psr\Log\LogLevel::INFO,
        'alert_title'       => 'Import Finished',
        'alert_description' => 'Imported 100 items successfully',
        'sensor'            => 'ImportExample'
    ]);
}
```

## Hooks

adeptus/activate [Action]

    Trigger when plugin activates.

adeptus/loggers [Action]

    Allows registration of custom loggers.

```php
class MyLogger implements \Psr\Log\LoggerInterface {
    //...
}

add_action('adeptus/loggers', function($alert_manager) {
    $logger = new MyLogger();
    $alert_manager->setLogger($logger);
});
```

adeptus/sensors [Filter]

    Allows filtering of sensors and/or registration of additional sensors which provide loggable events.

adeptus/alert_manager/alert_title [Filter]

    Allows filtering of the alert title.

adeptus/alert_manager/alert_level [Filter]

    Allows filtering of the alert level.

adeptus/alert_manager/context [Filter]

    Allows filtering of the alert context.

adeptus/alert_manager/loggers [Filter]

    Allows filtering of the loggers when an event is triggered.

adeptus/sensors/options/whitelist [Filter]

    This filter is in OptionsSensor and includes a list of most important options to log.

adeptus/sensors/options/woocommerce_blacklist [Filter]
    
    Blacklisted WooCommerce options to be ignored.


## Enable/Disable Logging Globally

To enable logging across entire website add the following line in .env file:

```define('WP_ADEPTUS_LOGGING_DISABLED', true);```
