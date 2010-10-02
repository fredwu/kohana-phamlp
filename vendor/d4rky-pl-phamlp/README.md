Fork of http://code.google.com/p/phamlp

Changes
=======

- attributes are minimized only if they are in minimizedAttribute array in config file
- if attribute's value is a variable, it shows only if the variable is set and not empty ( checked="#{$checked}" works as expected)
- parsed HAML files are kept in APPPATH.'cache/haml/', not in APPPATH.'views/_compiled'


Todo
====

- removing unnecessary parameter from html_escape()
