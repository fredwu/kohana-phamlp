<?php
/* SVN FILE: $Id: SassParser.php 78 2010-05-03 15:08:28Z chris.l.yates $ */
/**
 * SassParser class file.
 * See the {@link http://sass-lang.com/docs Sass documentation}
 * for details of Sass.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass
 */

require_once('SassFile.php');
require_once('tree/SassNode.php');
require_once('SassException.php');

/**
 * SassParser class.
 * Parses {@link http://sass-lang.com/ Sass} files.
 * @package			PHamlP
 * @subpackage	Sass
 */
class SassParser {
	/**#@+
	 * Default option values
	 */
	const CACHE							= true;
	const CACHE_LOCATION		= './sass-cache';
	const CSS_LOCATION			= './css';
	const TEMPLATE_LOCATION = './sass-templates';
	/**#@-*/

	/**
	 * @var string the character used for indenting
	 * @see indentChars
	 * @see indentSpaces
	 */
	private $indentChar;
	/**
	 * @var array allowable characters for indenting
	 */
	private $indentChars = array(' ', "\t");
	/**
	 * @var integer number of spaces for indentation.
	 * Used to calculate {@link indentLevel} if {@link indentChar} is space.
	 */
	private $indentSpaces = 2;
	/**
	 * @var integer line number of line being parsed
	 */
	private $lineNumber = 0;
	/**
	 * @var array options
	 * The following options are available:
	 *
	 * style: string Sets the style of the CSS output. Value can be:
	 * nested - Nested is the default Sass style, because it reflects the
	 * structure of the document in much the same way Sass does. Each selector
	 * and rule has its own line with indentation is based on how deeply the rule
	 * is nested. Nested style is very useful when looking at large CSS files for
	 * the same reason Sass is useful for making them: it allows you to very
	 * easily grasp the structure of the file without actually reading anything.
	 * expanded - Expanded is the typical human-made CSS style, with each selector
	 * and property taking up one line. Selectors are not indented; properties are
	 * indented within the rules.
	 * compact - Each CSS rule takes up only one line, with every property defined
	 * on that line. Nested rules are placed with each other while groups of rules
	 * are separated by a blank line.
	 * compressed - Compressed has no whitespace except that necessary to separate
	 * selectors and properties. It's not meant to be human-readable.
	 *
	 * property_syntax: string Forces the document to use one syntax for
	 * properties. If the correct syntax isn't used, an error is thrown.
	 * Value can be:
	 * new - forces the use of a colon or equals sign after the property name.
	 * For example	 color: #0f3 or width = !main_width.
	 * old -  forces the use of a colon before the property name.
	 * For example: :color #0f3 or :width = !main_width.
	 * By default, either syntax is valid.
	 *
	 * cache: boolean Whether parsed Sass files should be cached, allowing greater
	 * speed. Defaults to true.
	 *
	 * template_location: string Path to the root sass template directory for your
	 * application.
	 *
	 * css_location: string The path where CSS output should be written to.
	 * Defaults to "./css".
	 *
	 * cache_location: string The path where the cached sassc files should be
	 * written to. Defaults to "./sass-cache".
	 *
	 * load_paths: array An array of filesystem paths which should be searched for
	 * Sass templates imported with the @import directive.
	 * Defaults to
	 * "./sass-templates".
	 *
	 * line: integer The number of the first line of the Sass template. Used for
	 * reporting line numbers for errors. This is useful to set if the Sass
	 * template is embedded.
	 *
	 * line_numbers: boolean When set to true, causes the line number and file
	 * where a selector is defined to be emitted into the compiled CSS as a
	 * comment. Useful for debugging especially when using imports and mixins.
	 */
	private $options;

	/**
	 * Constructor.
	 * Sets parser options
	 * @param array $options
	 * @return SassParser
	 */
	public function __construct($options = array()) {
		if (!is_array($options)) {
			throw new SassException("Incorrect type for options; array required");
		}
		$this->options = array_merge(array(
			'style' 				 => SassRenderer::STYLE_NESTED,
			'cache' 				 => self::CACHE,
			'cache_location' => dirname(__FILE__) . DIRECTORY_SEPARATOR . self::CACHE_LOCATION,
			'css_location'	 => dirname(__FILE__) . DIRECTORY_SEPARATOR . self::CSS_LOCATION,
			'load_paths' 		 => array(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::TEMPLATE_LOCATION),
			'property_syntax' => 'either'
		), $options);
	}

	/**
	 * Parse a sass file or Sass source code and returns the CSS.
	 * @param string name of source file or Sass source
	 * @return string CSS
	 */
	public function toCss($source, $isFile = true) {
		return $this->parse($source, $isFile)->render();
	}

	/**
	 * Parse a sass file or Sass source code and
	 * returns the document tree that can then be rendered.
	 * The file will be searched for in the directories specified by the
	 * load_paths option.
	 * If caching is enabled a cached version will be used if possible or the
	 * compiled version cached if not.
	 * @param string name of source file or Sass source
	 * @return SassRootNode Root node of document tree
	 */
	public function parse($source, $isFile = true) {
		if ($isFile) {
			$filename = SassFile::getFile($source, $this->options);
			$this->options['file']['dirname'] = dirname($filename);
			$this->options['file']['basename'] = basename($filename);

			if ($this->options['cache']) {
				$cached = SassFile::getCachedFile($filename, $this->options);
				if ($cached !== false) {
					return $cached;
				}
			}

			$tree = $this->toTree(file($filename, FILE_IGNORE_NEW_LINES));

			if ($this->options['cache']) {
				SassFile::setCachedFile($tree, $filename, $this->options);
			}

			return $tree;
		}
		else {
			return $this->toTree(explode("\n", $source));
		}
	}

	/**
	 * Parse Sass source into a document tree.
	 * If the tree is already created return that.
	 * @param array Sass source
	 * @return SassNode the root of this document tree
	 */
	private function toTree($source) {
		$this->setIndentChar($source);
		$root = new SassRootNode($this->options);
		$this->buildTree($root, $source);
		return $root;
	}

	/**
	 * Adds children to a node if the current line has children.
	 * @param SassNode the node to add children to
	 * @param array line to test
	 * @param array remaing in source lines
	 */
	private function addChildren($node, $line, &$lines) {
		$node->line = $line;
		if ($this->hasChild($line, $lines)) {
			$this->buildTree($node, $lines);
		}
	}

	/**
	 * Returns a value indicating if the next line is a child of the parent line
	 * @param array parent line
	 * @param array remaing in source lines
	 * @param boolean whether the source line is a comment.
	 * If it all indented lines are regarded as children; if not the child line
	 * must only be indented by 1
	 * @return boolean true if the next line is a child of the parent line
	 * @throws SassException if the indent is invalid
	 */
	private function hasChild($line, &$lines, $isComment = false) {
		if (!empty($lines)) {
			$i = 0;
			$c = count($lines);
			while (empty($nextLine) && $i <= $c) {
				$nextLine = $lines[$i++];
			}

			$indentLevel = $this->getIndentLevel($nextLine, $line['number'] + $i);

			if (($indentLevel == $line['indentLevel'] + 1) ||
					($isComment && $indentLevel > $line['indentLevel'])) {
				return true;
			}
			elseif ($indentLevel <= $line['indentLevel']) {
				return false;
			}
			else {
				throw new SassException("Illegal indentation level ($indentLevel); indentation level can only increase by one.\nLine " . ($line['number'] + $i) . ': ' . (is_array($line['file']) ? join(DIRECTORY_SEPARATOR, $line['file']) : ''));
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Builds a parse tree under the parent node.
	 * @param SassNode the parent node
	 * @param array remaining source lines
	 */
	private function buildTree($parent, &$lines) {
		while (!empty($lines) && $this->isChildOf($parent, $lines[0])) {
			$line = $this->getLine($lines);
			if (!empty($line)) {
				$node = $this->parseLine($line, $lines, $parent);
				if (!empty($node)) {
					$parent->addChild($node);
					$this->addChildren($node, $line, $lines);
				}
			}
		}
	}

	/**
	 * Returns a value indicating if $line is a child of a node.
	 * @param SassNode the node
	 * @param array the line to check
	 * @return boolean true if the line is a child of the node, false if not
	 */
	private function isChildOf($node, $line) {
		return empty($line) || $this->getIndentLevel($line, $this->lineNumber) >
			$node->indentLevel;
	}

	/**
	 * Gets the next line.
	 * @param array remaining source lines
	 * @return array the next line
	 */
	private function getLine(&$lines) {
		$sourceLine = array_shift($lines);
		$number = $this->lineNumber++;
		$source = ltrim($sourceLine);
		if (empty($source)) { // blank lines are OK
			return;
		}
		$indentLevel = $this->getIndentLevel($sourceLine, $number);
		$file = $this->options['file'];
		return compact('source', 'number', 'indentLevel', 'file');
	}

	/**
	 * Returns the indent level of the line.
	 * @param string the line source
	 * @param integer line number
	 * @return integer the indent level of the line
	 * @throws Exception if the indent level is invalid
	 */
	private function getIndentLevel($line, $n) {
		$indent = strlen($line) - strlen(ltrim($line));
		if ($indent && $this->indentChar === ' ') {
			$indent /= $this->indentSpaces;
		}
		if (!is_integer($indent) ||
				preg_match("/[^{$this->indentChar}]/", substr($line, 0, $indent))) {
			throw new SassException("Invalid indentation\nLine " . ++$n . ': ' . (is_array($this->options['file']) ? join(DIRECTORY_SEPARATOR, $this->options['file']) : ''));
		}
		return $indent;
	}

	/**
	 * Determine the indent character and indent spaces.
	 * The first character of the first indented line determines the character.
	 * If this is a space the number of spaces determines the indentSpaces; this
	 * is always 1 if the indent character is a tab.
	 * @throws SassException if the indent is mixed or
	 * the indent character can not be determined
	 */
	private function setIndentChar($lines) {
		foreach ($lines as $l=>$line) {
			if (!empty($line) && in_array($line[0], $this->indentChars)) {
				$this->indentChar = $line[0];
				$len=strlen($line);
				for	($i=0; $i<$len&&$line[$i]==$this->indentChar; $i++) {}			
				if ($i<$len&&in_array($line[$i], $this->indentChars)) {
					throw new SassException("Mixed indentation not allowed.\nLine $i:" . (is_array($this->options['file']) ? join(DIRECTORY_SEPARATOR, $this->options['file']) : ''));
				}
				$this->indentSpaces = ($this->indentChar == ' ' ? $i : 1);
				return;
			}
		} // foreach
		$this->indentChar = ' ';
		$this->indentSpaces = 2;
	}

	/**
	 * Parse a line and its children.
	 * @param array line to parse
	 * @param array remaining lines
	 * @param SassNode parent node
	 * @return SassNode a SassNode of the appropriate type
	 */
	private function parseLine($line, &$lines, $parent) {
		if (empty($line)) {
			return null;
		}
		switch (true) {
			case SassCommentNode::isa($line):
				return $this->parseComment($line, $lines);
				break;
			case SassDirectiveNode::isa($line):
				return $this->parseDirective($line, $lines, $parent);
				break;
			case SassMixinDefinitionNode::isa($line):
				return $this->parseMixinDefinition($line);
				break;
			case SassMixinNode::isa($line):
				return $this->parseMixin($line);
				break;
			case SassVariableNode::isa($line):
				if ($this->hasChild($line, $Lines)) {
					throw new SassException("Illegal nesting. Nesting not allowed beneath variables.\nLine {$line['number']}: " . (is_array($line['file']) ? join(DIRECTORY_SEPARATOR, $line['file']) : ''));
				}
				return $this->parseVariable($line);
				break;
			case SassPropertyNode::isa($line, $this->options['property_syntax']):
				return $this->parseProperty($line);
				break;
			default:
				return $this->parseRule($line, $lines);
				break;
		} // switch
	}

	/**
	 * Parses a comment line and its child lines.
	 * @param string line to parse
	 * @param array remaining lines
	 * @return mixed SassCommentNode object for CSS comments, null for Sass comments
	 * @throws Exception if the comment type is unrecognised
	 */
	private function parseComment($line, &$lines) {
		switch ($line['source'][1]) {
			case SassCommentNode::Sass_COMMENT:
				$node = null;
				while ($this->hasChild($line, $lines, true)) {
					array_shift($lines);
					$this->lineNumber++;
				}
				break;
			case SassCommentNode::CSS_COMMENT:
				$matches = SassCommentNode::match($line);
				$node = new SassCommentNode($matches[SassCommentNode::COMMENT]);

				while ($this->hasChild($line, $lines, true)) {
					$node->addline(ltrim(array_shift($lines)));
					$this->lineNumber++;
				}
				break;
			default:
				throw new SassException("Illegal comment type.\nLine {$line['number']}: " . (is_array($line['file']) ? join(DIRECTORY_SEPARATOR, $line['file']) : ''));
				break;
		} // switch
		return $node;
	}

	/**
	 * Parses a mixin definition
	 * @param string line to parse
	 * @return SassMixinDefinitionNode mixin definition node
	 */
	private function parseMixinDefinition($line) {
  	if ($line['indentLevel'] !== 0) {
			throw new SassMixinDefinitionNodeException("Illegal Mixin definition, mixins can only be defined at root level\n{$line['number']}: " . (is_array($line['file']) ? join(DIRECTORY_SEPARATOR, $line['file']) : ''));
	 	}

	 	$matches = SassMixinDefinitionNode::match($line);
		return (sizeof($matches)==2 ?
			new SassMixinDefinitionNode($matches[SassMixinDefinitionNode::NAME]) :
			new SassMixinDefinitionNode(
				$matches[SassMixinDefinitionNode::NAME],
				$matches[SassMixinDefinitionNode::ARGUMENTS]
			)
		);
	}

	/**
	 * Parses a mixin definition
	 * @param string line to parse
	 * @return SassMixinDefinitionNode mixin definition node
	 */
	private function parseMixin($line) {
	 	$matches = SassMixinNode::match($line);
		return (sizeof($matches)==2 ?
			new SassMixinNode($matches[SassMixinNode::NAME]) :
			new SassMixinNode(
				$matches[SassMixinNode::NAME],
				$matches[SassMixinNode::ARGUMENTS]
			)
		);
	}

	/**
	 * Parses a property
	 * @param string line to parse
	 * @return SassPropertyNode property node
	 */
	private function parseProperty($line) {
		$matches = SassPropertyNode::match($line, $this->options['property_syntax']);
		return new SassPropertyNode(
			$matches[SassPropertyNode::NAME],
			$matches[SassPropertyNode::VALUE],
			($matches[SassPropertyNode::SCRIPT] === SassPropertyNode::IS_SCRIPT)
		);
	}

	/**
	 * Parses a rule
	 * @param array line to parse
	 * @param array remaining lines
	 * @return SassRuleNode rule node
	 */
	private function parseRule($line, &$lines) {
		$matches = SassRuleNode::match($line);
		$node = new SassRuleNode($matches[SassRuleNode::SELECTOR]);

		while ($node->isContinued) {
			$nextLine = $this->getLine($lines);

			if ($nextLine['indentLevel'] === $line['indentLevel']) {
				$node->addSelectors($nextLine['source']);
			}
			else {
				throw new SassException("Selectors can not end in a comma.\nLine {$nextLine['number']}: " . (is_array($nextLine['file']) ? join(DIRECTORY_SEPARATOR, $nextLine['file']) : ''));
			}
		}
		return $node;
	}

	/**
	 * Parses a variable
	 * @param array line to parse
	 * @return SassVariableNode variable node
	 */
	private function parseVariable($line) {
		$matches = SassVariableNode::match($line);
		return new SassVariableNode(
			$matches[SassVariableNode::NAME],
			$matches[SassVariableNode::VALUE],
			($matches[SassVariableNode::ASSIGNMENT] === SassVariableNode::IS_OPTIONAL)
		);
	}

	/**
	 * Parses a directive
	 * @param array line to parse
	 * @param array remaining lines
	 * @param SassNode parent node
	 * @return SassNode a Sass directive node
	 */
	private function parseDirective($line, &$lines, $parent) {
		preg_match(SassDirectiveNode::MATCH, $line['source'], $matches);
		switch (strtolower($matches[1])) {
			case '@import':
				if ($this->hasChild($line, $Lines)) {
					throw new SassException("Illegal nesting. Nesting not allowed beneath import directives\nLine {$line['number']}: " . (is_array($line['file']) ? join(DIRECTORY_SEPARATOR, $line['file']) : ''));
				}
				return $this->parseImport($line);
				break;
			case '@for':
				return $this->parseFor($line);
				break;
			case '@if':
				return $this->parseIf($line);
				break;
			case '@else':
				return $this->parseElse($line, $lines, $parent);
				break;
			case '@do':
			case '@while':
				return $this->parseWhile($line);
				break;
			case '@debug':
				return;
				break;
			default:
				return new SassDirectiveNode($line['source']);
				break;
		}
	}

	/**
	 * Parses an @import directive
	 * @param array line
	 * @return SassImportNode
	 */
	private function parseImport($line) {
		$matches = SassImportNode::match($line);
		return new SassImportNode($matches[SassImportNode::URI]);
	}

	/**
	 * Parses an @for directive
	 * @param array line
	 * @return SassForNode
	 */
	private function parseFor($line) {
		$matches = SassForNode::match($line);
		return new SassForNode(
			$matches[SassForNode::VARIABLE],
			$matches[SassForNode::FROM],
			$matches[SassForNode::TO],
			($matches[SassForNode::INCLUSIVE] == SassForNode::IS_INCLUSIVE),
			(empty($matches[SassForNode::STEP]) ? 1 : $matches[SassForNode::STEP])
		);
	}

	/**
	 * Parses an @if directive
	 * @param array line
	 * @return SassIfNode
	 */
	private function parseIf($line) {
		$matches = SassIfNode::matchIf($line);
		return new SassIfNode($matches[SassIfNode::IF_EXPRESSION]);
	}

	/**
	 * Parses an @else directive
	 * @param array line to parse
	 * @param array remaining lines
	 * @param SassNode parent node
	 * @return SassIfNode
	 */
	private function parseElse($line, &$lines, $parent) {
		$matches = SassIfNode::matchElse($line);
		$node = (sizeof($matches)==1 ? new SassIfNode() :
			new SassIfNode($matches[SassIfNode::ELSE_EXPRESSION]));
		$parent->lastChild->addElse($node);
		$this->addChildren($node, $line, $lines);
	}

	/**
	 * Parses a @while or @do directive
	 * @param array line
	 * @return SassWhileNode
	 */
	private function parseWhile($line) {
		$matches = SassWhileNode::match($line);
		return new SassWhileNode(
			$matches[SassWhileNode::EXPRESSION],
			($matches[SassWhileNode::LOOP] === SassWhileNode::IS_DO)
		);
	}
}
