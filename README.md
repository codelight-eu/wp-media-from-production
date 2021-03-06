# WordPress Media from Production
This is a WordPress plugin that allows loading the site's uploaded media files from a different environment. 
You'll want to use this in your local or development environment to save disk space and time by not constantly having to sync the ridiculously high number of different image sizes.  

This plugin is a fork of Bill Erickson's [BE Media from Production](https://github.com/billerickson/BE-Media-from-Production), updated to support different remote folders, svg files, constants instead of filters for configuration and a couple of other details.

## Version support
Tested & works on WP 5.3 and earlier.

The plugin is actively used by [Codelight](https://codelight.eu/) in our day-to-day operations, so we expect to maintain it for a while.

## How it works
For every media file that's displayed on the site, the plugin checks if there is an existing local file with the same name in wherever your uploads are located.
If there is, it displays the local file. If there is not, then it rewrites the image URL to point at your remote environment.

## Configuration
First, ensure your `WP_ENV` constant is set to `development` in wp-config:
```php
<?php
define('WP_ENV', 'development');
```

Configuring the plugin via wp-config (recommended):
```php
<?php
define('MEDIA_PRODUCTION_REMOTE_URL', 'https://production-url.com');

// Optional, in case you're running Trellis or something that rewrites wp-content folder name
define('MEDIA_PRODUCTION_REMOTE_FOLDER', 'app');

// Optional, in case some of your images come from 3rd party domains, such as an image resizer
define('MEDIA_PRODUCTION_IGNORE_DOMAINS', [
    'resizer.com',
    'someotherdomain.com',
]);
```

Via filters (note that the filter names will change in an upcoming release):
```php
<?php

add_filter('be_media_from_production_url', function() {
  return 'https://production-url.com';
});

add_filter('be_media_from_production_remote_content_dir', function() {
  return 'app';
});
```

Additional filters are available to include specific folders based on the upload time:
- be_media_from_production_start_month – Specify the Start Month
- be_media_from_production_start_year – Specify the Start Year

## Contributing
All issues, comments and PRs are most welcome.
