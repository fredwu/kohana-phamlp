# Kohana PHamlP Module

This module is a bridge between the [Kohana PHP framework](http://kohanaframework.org/) and the [PHamlP library](http://code.google.com/p/phamlp/).

## Compatibility

This module is for Kohana 3.0.

## Installation

* Download the source code, extract it and put it in your Kohana's modules directory.
* Enable the module in your boostrap file (`boostrap.php` under your `application` directory).
* Copy and paste the configuration file (`config/phamlp.php`) to your application's config directory.
* Make necessary changes to the configuration file to suit your needs.
* Copy and paste the controller file (`classes/controller/haml.php`) to your controller directory if you want to customise it.

## Usage

If you would like to take advantage of the Haml controller shipped with this module, simply:

* Make sure the layout file exists (configurable by setting `$this->$template` in the controller).
* Make sure the view files exist. View files follow the `controller_name/action_name` convention.
* By default the view files have `.haml` as the file extension.
* Assign view variables to `$this->view_data`, e.g. `$this->view_data['title'] = 'My Website';`.

If you would like to take control of the view rendering yourself, you can:

* Call `Haml::factory()` instead of `View::factory()`, e.g. `Haml::factory($view_file, $view_data)`.

## Todo

* Bridge Kohana::View methods, e.g. `set()` and `bind()`, etc.
* PHamlP configuration array.
* Sass support.

## Author

Copyright (c) 2010 Fred Wu (<http://fredwu.me>), released under the [MIT license](http://www.opensource.org/licenses/mit-license.php).

Brought to you by **Wuit** - <http://wuit.com>.