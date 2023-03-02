# php-rssCloud changelog

**2nd March 2023**

Adjust url parameter check. More to come.

Updated files:

- .pleaseNotify/index.php

**27th Feb 2023**

Added logging for auto-removal of subs due to 5 failures.

Made a new icon and added to each page.

Updated files:

index.html
style.css
/cancelForm/index.html
/docs/index.html
/images/cloud.png
/ping/index.php
/pingForm/index.html
/pleaseNotifyForm/index.html
/viewLog/index.php

**26th Feb 2023**

If the server fails to notify any given subscriber it will increment the 'failures' column in the rssc_sub table for that subscription. If it reaches 5 failures the subscription will be removed automatically.

Updated files:

/ping/index.php
