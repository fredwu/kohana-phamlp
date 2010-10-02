# Changes / fixes d4rky-pl's version of PHamlP Module:

- Attributes are minimized only if they are in minimizedAttribute array in config file
- If attribute's value is a variable, it shows only if the variable is set and not empty ( checked="#{$checked}" works as expected)
- Parsed HAML files are kept in APPPATH.'cache/haml/', not in APPPATH.'views/_compiled'
- Helper methods work properly (haml_escape(something) returns htmlspecialchars(something) instead of htmlspecialchars('',something)
