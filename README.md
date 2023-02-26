# php-rssCloud

An rssCloud server implementation in PHP

This has been inspired heavily by Andrew Shell's [server in node.js](https://github.com/rsscloud/rsscloud-server).

A key difference here is that subscriptions are **not** removed after 25 hours so do not need to be refreshed. See POST /cancel/ below.  
I am considering situations where subscriptions might be auto-removed after X notification failures (a failures column already exists in the subs table but is not currently used) but have yet to make any final decisions.

## Prerequisites

- a MySQL database
- an account to read/write from /to that database
    - it is preferable to have separate accounts for read & write
    
## Setup

- copy all files to the required location
- add database and account details to /config/config.php

```
define('DB_SERVER', '');
define('DB_NAME', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_USERNAMESEL', '');
define('DB_PASSWORDSEL', '');
```

(DB_USERNAME & DB_PASSWORD for writing, DB_USERNAMESEL & DB_PASSWORDSEL for read only)

- create the required tables:
    - use the SQL statements in tables.txt
    
## Usage

### POST /pleaseNotify/

Posting to /pleaseNotify/ tells the server that you wish to receive updates from the specified feed.

The required parameters are:

1.  **domain** - the domain of your notification endpoint. This can be just the host portion (e.g. mysite.com) or you can also include the URL scheme (http/https).
  
3.  **port** - the port your endpoint uses. 80 & 8080 are treated as http, 443 is treated as https. Otherwise, the default is http but adding the scheme to the domain will override the port.
  
5.  **path** - the path of your notification endpoint relative to the domain above (e.g. /notify/).
  
7.  **url** - the address of the feed you wish to receive notifications for.

The rssCloud spec also includes 'registerProcedure' and 'protocol' parameters. These are both included but hidden. registerProcedure is not used and this implementation only supports http-post.

When you POST to **/pleaseNotify/** the server checks the link validity then performs a GET request to the notification endpoint represented by http://: with two query string parameters: url and challenge. To accept the subscription the endpoint needs to return an HTTP 200 status code and have the challenge value as the response body.

/pleaseNotify/ will return an XML request body with success="true" or "false", for example:

    <?xml version="1.0" ?>
    <notifyResult success="true"/>

### POST /ping/

Posting to /ping/ tells the server that the specified feed has been updated.

The required parameters are:

1.  **url** - the full address of the feed

When you POST to **/ping/** the server checks if the feed has actually changed since the last ping. If so, it will cycle through the subscribers to that feed and POST to the respective endpoints with the parameter url.

/ping/ will return an XML request body with success="true" or "false" depending on whether the feed has changed since the last ping.

### POST /cancel/

Posting to /cancel/ tells the server that you wish to stop receiving updates from the specified feed.

The required parameters are:

1.  **domain** - the domain of your notification endpoint.
  
3.  **path** - the path of your notification endpoint relative to the domain.
  
5.  **url** - the full address of the feed you wish to unsubscribe from.

When you POST to **/cancel/** the server checks for a matching subscriber/feed pair and deletes it if found.

/cancel/ will return an XML request body with success="true" or "false".
  
  
**IMPORTANT**: please ensure you include the trailing slashes for the above endpoints.

## Forms

The server has web based forms that you can use instead of taking code-based actions.

**/pingForm/**, **/pleaseNotifyForm/**, and **/cancelForm/** each correspond to their respective endpoints above.

## Log

Recent events can be seen at **/viewLog/**.

After manually filling out each of the forms you will be automatically routed to the log to see whether the action succeeded or failed.

## To do

- clearing old events from the log, I need to determine a suitable retention period
- options for auto-removal of subscriptions (not decided yet)
    - after X notification failures, or
    - when a feed hasn't update in a while
