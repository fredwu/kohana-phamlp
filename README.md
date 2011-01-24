[![](http://stillmaintained.com/fredwu/kohana-phamlp.png)](http://stillmaintained.com/fredwu/kohana-phamlp)

# Kohana PHamlP Module

This module is a bridge between the [Kohana PHP framework](http://kohanaframework.org/) and the [PHamlP library](http://code.google.com/p/phamlp/).

## Compatibility

This module is for Kohana 3.0+.

## Installation

* Download the source code, extract it and put it in your Kohana's modules directory.
* Enable the module in your boostrap file (`boostrap.php` under your `application` directory).
* Copy and paste the configuration file (`config/phamlp.php`) to your application's config directory.
* Make necessary changes to the configuration file to suit your needs.
* Copy and paste the controller file (`classes/controller/haml.php`) to your controller directory if you want to customise it.

## Haml

### Usage

#### Prerequesites

* By default the view files have `.haml` as the file extension.

If you would like to take advantage of the Haml controller shipped with this module, simply:

* Make sure the layout file exists (configurable by setting `$this->$template` in the controller).
* Make sure the view files exist. View files follow the `controller_name/action_name` convention.

If you would like to take control of the view rendering yourself, you can:

* Either don't inherit from `Controller_Haml`, or set `$this->auto_render` to false.
* Call `Haml::factory()` instead of `View::factory()`, e.g. `Haml::factory($view_file, $view_data, $haml_options)`.

#### Configuration Options

* Default Haml options are configured in `config/phamlp.php` file - these apply to all Haml templates generated.
* Optionally, you may set per controller or per action Haml options via `$this->haml_options` in your controller actions. These overrides the default options.

#### Assigning Variables

* Assign view variables to `$this->view_data`, e.g. `$this->view_data['title'] = 'My Website';`.
* You may use either `View::set_global()`/`View::bind_global()` or `Haml::set_global()`/`Haml::bind_global()` to set global view variables.
* `Haml::factory()` returns a `View` object, so you may use `bind()`, `set()` and magic getter/setters on it.

## Todo

* Sass support.

## Author

Copyright (c) 2010 Fred Wu (<http://fredwu.me>), released under the [MIT license](http://www.opensource.org/licenses/mit-license.php).

Brought to you by **Wuit** - <http://wuit.com>.