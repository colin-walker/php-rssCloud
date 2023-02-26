# php-rssCloud changelog

**26th Feb 2023**

If the server fails to notify any given subscriber it will increment the 'failures' column in the rssc_sub table for that subscription. If it reaches 5 failures the subscription will be removed automatically.

Updated files:

/ping/index.php
