# Yii Audit Module

Track and store usage information including page requests, database field changes and system errors.


### Contents

- [Features](#features)
	- [Visitor Request Tracking](#visitor-request-tracking)
	- [Model Field Tracking](#model-field-tracking)
	- [Error Tracking](#error-tracking)
	- [Log Tracking](#log-tracking)
- [Screenshots](#screenshots)
- [Installation](#installation)
	- [Composer Installation](#composer-installation)
	- [Manual Installation](#manual-installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Resources](#resources)
- [License](#license)


## Features


### Visitor Request Tracking

- Track site activity including everything you need to know about the request.
- The error handler will automatically create an AuditRequest record for each visitor hit.
- When the application ends it will update the AuditRequest with memory and time information.

Tracks the following information:

- Links - Requested URL, referring URL, redirecting to URL (read from the headers at the end of the application)
- User - Visitors IP Address and logged in user's ID
- Superglobals - (`$_GET`/`$_POST`/`$_SESSION`/`$_FILES`/`$_COOKIE`), the arrays are serialized then compressed using gzip
- Timers - Start and end times of the application
- Memory - Memory usage and peak memory usage


### Model Field Tracking

- Tracks the old and new values each time your model is saved.
- Behavior can easily be attached to any model you want to track field changes.
- Each field change is related to an AuditRequest so you can see the entire state of the visitors action.
- Performs multiple inserts in a single query with `CDbCommandBuilder::createMultipleInsertCommand()`.
- Provides views that can be rendered into your application to show changed fields for your model.


### Error Tracking

- Full error stack dump is saved, even in live mode.
- Catches all errors, including fatal errors.
- View all the collected data from the module interface.
- Each error is related to an AuditRequest so you can see the entire state of the visitors action.


### Log Tracking

- Save logs to your database for easy real-time debugging or for checking on historical logs.
- Each log is related to an AuditRequest so you can see the entire state of the visitors action.


## Screenshots

Yii Audit Module Homepage:
![home](https://raw.github.com/cornernote/yii-audit-module/master/screenshot/home.png)

Request List
![Requests](https://raw.github.com/cornernote/yii-audit-module/master/screenshot/requests.png)

Request View
![Request](https://raw.github.com/cornernote/yii-audit-module/master/screenshot/request.png)

Field List
![Fields](https://raw.github.com/cornernote/yii-audit-module/master/screenshot/fields.png)

Field View
![Field](https://raw.github.com/cornernote/yii-audit-module/master/screenshot/field.png)

Error List
![Errors](https://raw.github.com/cornernote/yii-audit-module/master/screenshot/errors.png)

Error View
![Error](https://raw.github.com/cornernote/yii-audit-module/master/screenshot/error.png)

Log List
![Logs](https://raw.github.com/cornernote/yii-audit-module/master/screenshot/logs.png)

Log View
![Log](https://raw.github.com/cornernote/yii-audit-module/master/screenshot/log.png)


## Installation

Please download using ONE of the following methods:


### Composer Installation

All requirements are automatically downloaded into the correct location when using composer.  There is no need to download additional files or set paths to third party files.

Get composer:

```
curl http://getcomposer.org/installer | php
```

Install latest release OR development version:

```
php composer.phar require cornernote/yii-audit-module:*           // latest release
php composer.phar require cornernote/yii-audit-module:dev-master  // development version
```

Add the `vendor` folder to the `aliases` in your yii configuration:

```php
return array(
	'aliases' => array(
		'vendor' => '/path/to/vendor',
	),
);
```


### Manual Installation

Download the [latest version](https://github.com/cornernote/yii-audit-module/archive/master.zip) and move the `audit` folder into your `protected/modules` folder.

In addition the following are required:
* [YiiStrap](http://www.getyiistrap.com) for the interface elements.  Please follow their Getting Started guide to setup the aliases and components for your application.


## Configuration

Add yii-audit-module to the `modules` in your yii configuration:

```php
return array(
	'modules' => array(
		'audit' => array(
			// path to the AuditModule class
			'class' => '/path/to/vendor/cornernote/yii-audit-module/audit/AuditModule',

			// add a list of users who can access the audit module
			'adminUsers' => array('admin'),

			// set this to your user view url, 
			// AuditModule will replace --user_id-- with the actual user_id
			'userViewUrl' => array('/user/view', 'id' => '--user_id--'),

			// set this to false in production to improve performance
			'autoCreateTables' => true,
		),
	),
);
```

Use `AuditErrorHandler` as your applications error handler by updating the `components` section in your yii configuration:

```php
return array(
	'components' => array(
		'errorHandler' => array(
			// path to the AuditErrorHandler class
			'class' => 'audit.components.AuditErrorHandler',

			// set this as you normally would for CErrorHandler
			'errorAction' => 'site/error',

			// set this to true to track all requests
			'trackAllRequests' => true,
		),
	),
);
```

To handle fatal errors we have add the error handler to the `preload` section in your yii configuration:

```php
return array(
	'preload' => array(
		'log', 
		'errorHandler', // handle fatal errors
	),
);
```

To track logs we need to add a logroute to `AuditLogRoute` to your yii configuration:
```php
return array(
	'components' => array(
		'db' => array(
			// standard setup
			'connectionString' => 'mysql:host=localhost;dbname=test',
			'username' => 'root',
			'password' => '',

			// set to true to enable database query logging
			// don't forget to put `profile` in the log route `levels` below
			'enableProfiling' => true,

			// set to true to replace the params with the literal values
			'enableParamLogging' => true,
		),
		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				// add a new log route
				array(
					// path to the AuditLogRoute class
					'class' => 'audit.components.AuditLogRoute',

					// can be: trace, warning, error, info, profile
					// can also be anything else you want to pass as a level to `Yii::log()`
					'levels' => 'error, warning, profile, audit',
				),
			),
		),
	),
);
```

To track field changes add `AuditFieldBehavior` to your CActiveRecord `behaviors()` functions.

```php
class Post extends CActiveRecord
{
	public function behaviors()
	{
		return array(
			'AuditFieldBehavior' => 'audit.components.AuditFieldBehavior',
		);
	}
}
```


## Usage

Logging is as simple as calling `Yii::log()`.  The second argument needs to be one of the `AuditLogRoute::levels` you specified above (error, warning or audit).
```php
Yii::log('Hello World!', 'audit');
Yii::log('something really bad just happened', 'error');
```

There are several partial views that you can render into your application.  These are all optional.

Add information to your footer:
```php
$this->renderPartial('audit.views.request.__footer');
```

Show changes for a model:
```php
$post = Post::model()->findByPk(123);
$this->renderPartial('audit.views.field.__fields', array('model' => $post));
// or by using the model_name and model_id
// $this->renderPartial('audit.views.field.__fields', array('model_name' => 'Post', 'model_id' => 123));
```

Show changes for a single field in a model:
```php
$post = Post::model()->findByPk(123);
$this->renderPartial('audit.views.field.__field', array('model' => $post, 'field' => 'status'));
// or by using the model_name and model_id
// $this->renderPartial('audit.views.field.__field', array('model_name' => 'Post', 'model_id' => 123, 'field' => 'status'));
```


## Resources

[![Mr PHP](https://raw.github.com/cornernote/mrphp-assets/master/img/code-banner.png)](http://mrphp.com.au) [![Github Project](https://raw.github.com/cornernote/mrphp-assets/master/vendor/github/github-latest-sourcecode-16.png)](https://github.com/cornernote/yii-audit-module#yii-audit-module) [![Yii Extension](https://raw.github.com/cornernote/mrphp-assets/master/vendor/yii/yii-extension-16.png)](http://www.yiiframework.com/extension/yii-audit-module) [![Project Stats](https://www.ohloh.net/p/yii-audit-module/widgets/project_thin_badge.gif)](https://www.ohloh.net/p/yii-audit-module)

[![Latest Stable Version](https://poser.pugx.org/cornernote/yii-audit-module/v/stable.png)](https://packagist.org/packages/cornernote/yii-audit-module) [![Build Status](https://travis-ci.org/cornernote/yii-audit-module.png?branch=master)](https://travis-ci.org/cornernote/yii-audit-module) [![Dependencies Check](https://depending.in/cornernote/yii-audit-module.png)](https://depending.in/cornernote/yii-audit-module)

- [YiiStrap](http://getyiistrap.com/)


## License

[BSD-3-Clause](https://raw.github.com/cornernote/yii-audit-module/master/LICENSE), Copyright © 2013-2014 [Mr PHP](mailto:info@mrphp.com.au)
