# SofaDoc

SofaDoc is a simple live documentation of PHP source code. SofaDoc recognizes JavaDoc style documentation, but does not need it. It parses the code with the PHP Tokenizer and extracts some features.
- Classes defined and used
- Functions defined and used
- Globals and variables
- Includes
- Features: what group of PHP built in functions are used

# Demo

A live demonstration of SofaDoc is the documentation of SofaWiki on https://www.sofawiki.com/sofadoc.php

# Usage

Just drop the sofadoc.php file in the main folder of your code and it will explore the files starting with index.php. There is no configuration needed. The documentation is very fast (less than a second for the entire sofawiki codebase).

# Tuning:

- If your main file is not index.php, you need to create an index.php file and include the main file from it.
- If you want to exclude a file from the Doc (e.g. a configuration file), define the path with a variable and then include the variable so it is hidden from SofaDoc.
- Paths are resolved relative to sofadoc.php folder. If you use a variable for the absolute path, you can ignore it with a comment /* SOFADOC_IGNORE $swRoot/ */.
- You can add include paths with a comment /* SOFADOC_INCLUDE inc/skins/default.php */.

# Limitations

The parser is not perfect. It has only be tested against Sofawiki code. If elements are not recognized, sections other and token appear.
Only include paths with constants are followed. This exclude features like autoload. Namespaces are not supported either.
