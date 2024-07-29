
# Voyager
Transfer your project files to the host/server in one click.

## Installation
### Install Package
```shell
composer require rapid/voyager
```

### Create Root file
Create a file like `voy.php` with following contents:
```php
<?php

use Rapid\Voyager\Voyager;

require_once __DIR__ . '/vendor/autoload.php';

# Configuration
$voy = Voyager::factory(__FILE__, __DIR__);
$voy->remote("https://example.com/voy.php", 'SECURITY_KEY');

# Sources
//$voy->source('src');

$voy->start();
```

## Configure
Let me explain configuration file line by line:

This is creating new instance of `Voyager`. First parameter is path of `voy.php` file
    , second is root folder path, and last one is root folder path for the server-side
    only (optional).
```php
$voy = Voyager::factory(__FILE__, __DIR__);
```

You should set remote url and security key. Security key is recommended to be unique
    and random.

In the background, security key used for hashing and validating data and uploading files.
    It's like authorization.
```php
$voy->remote("https://example.com/voy.php", 'SECURITY_KEY');
```

You can add source codes (folders or files) that used for uploading:
```php
$voy->source('src');
$voy->source('composer.json');
```

### Setup & Run
#### Edit voy.php
First, you should edit `voy.php` file. Editing this file later, maybe makes some bugs.

Also, if you're editing this file in client-side, client ask you to update this file or not!
    That means voyager upload that's file to server and update that.

#### Upload At First Time
First time you should upload `voy.php` and `composer.json` file,
    and `vendor` folder to the server/host.
    Everything you need to run `voy.php` and composer autoload file, required.

#### Upload Command
Execute following command to update files in server/host:
```shell
php voy.php
```
Voyager is smart! [See concept.](#concept)


### Concept
When you're updating files, Voyager take a snapshot from your local files, and then
    get last snapshot from the server-side files.

Then compare snapshots and detect updates (e.g. new files, deleted files, edited files).

And finally upload just updated files to improve speeds.

> If some files in server-side will be changed, voyager doesn't detect that!
> So you should not change server-side files.


### Template
#### Laravel For Server
```php
<?php

use Rapid\Voyager\Voyager;

require_once __DIR__ . '/vendor/autoload.php';

# Configuration
$voy = Voyager::factory(__FILE__, __DIR__, __DIR__ . '/..');
$voy->remote("https://example.com/voy.php", 'SECURITY_KEY');

# Sources
$voy->sourceRoot();
$voy->source('public', 'public_html');

$voy->exclude('voy.php');
$voy->exclude(['vendor', 'node_modules']);
$voy->exclude(['public/hot']);
$voy->exclude(['database/database.sql']);

$voy->instead('.env', '.env.production');

$voy->start();
```
And create `.env.server` file.

#### Laravel For Host
```php
<?php

use Rapid\Voyager\Voyager;

require_once __DIR__ . '/vendor/autoload.php';

# Configuration
$voy = Voyager::factory(__FILE__, __DIR__, __DIR__ . '/../ROOT_PATH');
$voy->remote("https://example.com/voy.php", 'SECURITY_KEY');

# Sources
$voy->sourceRoot();
$voy->source('public', '../public_html');

$voy->exclude('voy.php');
$voy->exclude(['vendor', 'node_modules']);
$voy->exclude(['public/hot']);
$voy->exclude(['database/database.sql']);

$voy->instead('.env', '.env.production');

$voy->start();
```
