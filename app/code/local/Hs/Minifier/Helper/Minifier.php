<?php
/**
 * Copyright (c) 2009, Robert Hafner
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Stash Project nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Robert Hafner BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * Magento port adjustment by Tomislav Nikčevski, 2016
 * JS Minifier
 * Usage - $this->minify($js);
 */
 
class Hs_Minifier_Helper_Minifier
{
	/**
	 * The input javascript to be minified.
	 *
	 * @var string
	 */
	protected $_input;
 
	/**
	 * Output javascript buffer - this will be returned
	 * @var
	 */
	protected $_text;
 
	/**
	 * The location of the character (in the input string) that is next to be
	 * processed.
	 *
	 * @var int
	 */
	protected $_currentPosition = 0;
 
	/**
	 * The first of the characters currently being looked at.
	 *
	 * @var string
	 */
	protected $_firstChar = '';
 
	/**
	 * The next character being looked at (after firstChar);
	 *
	 * @var string
	 */
	protected $_secondChar = '';
 
	/**
	 * This character is only active when certain look ahead actions take place.
	 *
	 *  @var string
	 */
	protected $_endChar;
 
	/**
	 * Contains lock ids which are used to replace certain code patterns and
	 * prevent them from being minified
	 *
	 * @var array
	 */
	protected $locks = array();
 
	/**
	 * Takes a string containing javascript and removes unneeded characters in
	 * order to shrink the code without altering it's functionality.
	 *
	 * @param  string      $js      The raw javascript to be minified
	 * @return bool|string
	 */
	public function minify($js)
	{
		try {
			$js = $this->lock($js);
 
			$this->initialize($js);
			$this->loop();
			$js = $this->unlock(ltrim($this->_text));
			$this->clean();
 
			return $js;
 
		} catch (Exception $e) {
			$this->clean();
			Mage::log('Minifier failed. Error: ' . $e->getMessage());
			return false;
		}
	}
 
	/**
	 *  Initializes internal variables, normalizes new lines,
	 *
	 * @param string $js      The raw javascript to be minified
	 */
	protected function initialize($js)
	{
		$js = str_replace("\r\n", "\n", $js);
		$js = str_replace('/**/', '', $js);
		$this->_input = str_replace("\r", "\n", $js);
 
		// We add a newline to the end of the script to make it easier to deal
		// with comments at the bottom of the script- this prevents the unclosed
		// comment error that can otherwise occur.
		$this->_input .= PHP_EOL;
 
		// Populate "firstChar" with a new line, "secondChar" with the first character, before
		// entering the loop
		$this->_firstChar = "\n";
		$this->_secondChar = $this->getReal();
	}
 
	/**
	 * The primary action occurs here. This function loops through the input string,
	 * outputting anything that's relevant and discarding anything that is not.
	 */
	protected function loop()
	{
		while ($this->_firstChar !== false && !is_null($this->_firstChar) && $this->_firstChar !== '') {
 
			switch ($this->_firstChar) {
				// new lines
				case "\n":
					// if the next line is something that can't stand alone preserve the newline
					if (strpos('(-+{[@', $this->_secondChar) !== false) {
						$this->_text .= $this->_firstChar;
						$this->saveString();
						break;
					}
 
					// if secondChar is a space we skip the rest of the switch block and go down to the
					// string/regex check below, resetting secondChar with getReal
					if($this->_secondChar === ' ')
						break;
 
				// otherwise we treat the newline like a space
 
				case ' ':
					if(static::isAlphaNumeric($this->_secondChar))
						$this->_text .= $this->_firstChar;
 
					$this->saveString();
					break;
 
				default:
					switch ($this->_secondChar) {
						case "\n":
							if (strpos('}])+-"\'', $this->_firstChar) !== false) {
								$this->_text .= $this->_firstChar;
								$this->saveString();
								break;
							} else {
								if (static::isAlphaNumeric($this->_firstChar)) {
									$this->_text .= $this->_firstChar;
									$this->saveString();
								}
							}
							break;
 
						case ' ':
							if(!static::isAlphaNumeric($this->_firstChar))
								break;
 
						default:
							// check for some regex that breaks stuff
							if ($this->_firstChar === '/' && ($this->_secondChar === '\'' || $this->_secondChar === '"')) {
								$this->saveRegex();
								continue;
							}
 
							$this->_text .= $this->_firstChar;
							$this->saveString();
							break;
					}
			}
 
			// do reg check of doom
			$this->_secondChar = $this->getReal();
 
			if(($this->_secondChar == '/' && strpos('(,=:[!&|?', $this->_firstChar) !== false))
				$this->saveRegex();
		}
	}
 
	/**
	 * Resets attributes that do not need to be stored between requests so that
	 * the next request is ready to go. Another reason for this is to make sure
	 * the variables are cleared and are not taking up memory.
	 */
	protected function clean()
	{
		unset($this->_input);
		$this->_currentPosition = 0;
		$this->_firstChar = $this->_secondChar = $this->_text = '';
		unset($this->_endChar);
	}
 
	/**
	 * Returns the next string for processing based off of the current index.
	 *
	 * @return string
	 */
	protected function getChar()
	{
		// Check to see if we had anything in the look ahead buffer and use that.
		if (isset($this->_endChar)) {
			$char = $this->_endChar;
			unset($this->_endChar);
 
			// Otherwise we start pulling from the input.
		} else {
			$char = substr($this->_input, $this->_currentPosition, 1);
 
			// If the next character doesn't exist return false.
			if (isset($char) && $char === false) {
				return false;
			}
 
			// Otherwise increment the pointer and use this char.
			$this->_currentPosition++;
		}
 
		// Normalize all whitespace except for the newline character into a
		// standard space.
		if($char !== "\n" && ord($char) < 32)
 
			return ' ';
 
		return $char;
	}
 
	/**
	 * This function gets the next "real" character. It is essentially a wrapper
	 * around the getChar function that skips comments. This has significant
	 * performance benefits as the skipping is done using native functions (ie,
	 * c code) rather than in script php.
	 *
	 *
	 * @return string            Next 'real' character to be processed.
	 */
	protected function getReal()
	{
		$startIndex = $this->_currentPosition;
		$char = $this->getChar();
 
		// Check to see if we're potentially in a comment
		if ($char !== '/') {
			return $char;
		}
 
		$this->_endChar = $this->getChar();
 
		if ($this->_endChar === '/') {
			return $this->processOneLineComments($startIndex);
 
		} elseif ($this->_endChar === '*') {
			return $this->processMultiLineComments($startIndex);
		}
 
		return $char;
	}
 
	/**
	 * Removed one line comments, with the exception of some very specific types of
	 * conditional comments.
	 *
	 * @param  int    $startIndex The index point where "getReal" function started
	 * @return string
	 */
	protected function processOneLineComments($startIndex)
	{
		$thirdCommentString = substr($this->_input, $this->_currentPosition, 1);
 
		// kill rest of line
		$this->getNext("\n");
 
		if ($thirdCommentString == '@') {
			$endPoint = $this->_currentPosition - $startIndex;
			unset($this->_endChar);
			$char = "\n" . substr($this->_input, $startIndex, $endPoint);
		} else {
			// first one is contents of $this->_endChar
			$this->getChar();
			$char = $this->getChar();
		}
 
		return $char;
	}
 
	/**
	 * Skips multiline comments where appropriate, and includes them where needed.
	 * Conditional comments and "license" style blocks are preserved.
	 *
	 * @param  int               $startIndex The index point where "getReal" function started
	 * @return bool|string       False if there's no character
	 * @throws \RuntimeException Unclosed comments will throw an error
	 */
	protected function processMultiLineComments($startIndex)
	{
		$this->getChar(); // current endChar
		$thirdCommentString = $this->getChar();
 
		// kill everything up to the next */ if it's there
		if ($this->getNext('*/')) {
 
			$this->getChar(); // get *
			$this->getChar(); // get /
			$char = $this->getChar(); // get next real character
 
			// Now we reinsert conditional comments and YUI-style licensing comments
			if ($thirdCommentString === '!' || $thirdCommentString === '@') {
 
				// If conditional comments or flagged comments are not the first thing in the script
				// we need to append firstChar to text and fill it with a space before moving on.
				if ($startIndex > 0) {
					$this->_text .= $this->_firstChar;
					$this->_firstChar = " ";
 
					// If the comment started on a new line we let it stay on the new line
					if ($this->_input[($startIndex - 1)] === "\n") {
						$this->_text .= "\n";
					}
				}
 
				$endPoint = ($this->_currentPosition - 1) - $startIndex;
				$this->_text .= substr($this->_input, $startIndex, $endPoint);
 
				return $char;
			}
 
		} else {
			$char = false;
		}
 
		if($char === false)
			throw new \RuntimeException('Unclosed multiline comment at position: ' . ($this->_currentPosition - 2));
 
		// if we're here endChar is part of the comment and therefore tossed
		if(isset($this->_endChar))
			unset($this->_endChar);
 
		return $char;
	}
 
	/**
	 * Pushes the index ahead to the next instance of the supplied string. If it
	 * is found the first character of the string is returned and the index is set
	 * to it's position.
	 *
	 * @param  string       $string
	 * @return string|false Returns the first character of the string or false.
	 */
	protected function getNext($string)
	{
		// Find the next occurrence of "string" after the current position.
		$pos = strpos($this->_input, $string, $this->_currentPosition);
 
		// If it's not there return false.
		if($pos === false)
 
			return false;
 
		// Adjust position of index to jump ahead to the asked for string
		$this->_currentPosition = $pos;
 
		// Return the first character of that string.
		return substr($this->_input, $this->_currentPosition, 1);
	}
 
	/**
	 * When a javascript string is detected this function crawls for the end of
	 * it and saves the whole string.
	 *
	 * @throws \RuntimeException Unclosed strings will throw an error
	 */
	protected function saveString()
	{
		$startpos = $this->_currentPosition;
 
		// saveString is always called after a gets cleared, so we push secondChar into
		// that spot.
		$this->_firstChar = $this->_secondChar;
 
		// If this isn't a string we don't need to do anything.
		if ($this->_firstChar !== "'" && $this->_firstChar !== '"') {
			return;
		}
 
		// String type is the quote used, " or '
		$stringType = $this->_firstChar;
 
		// append out that starting quote
		$this->_text .= $this->_firstChar;
 
		// Loop until the string is done
		while (true) {
 
			// Grab the very next character and load it into firstChar
			$this->_firstChar = $this->getChar();
 
			switch ($this->_firstChar) {
 
				// If the string opener (single or double quote) is used
				// output it and break out of the while loop-
				// The string is finished!
				case $stringType:
					break 2;
 
				// New lines in strings without line delimiters are bad- actual
				// new lines will be represented by the string \n and not the actual
				// character, so those will be treated just fine using the switch
				// block below.
				case "\n":
					throw new \RuntimeException('Unclosed string at position: ' . $startpos );
					break;
 
				// Escaped characters get picked up here. If it's an escaped new line it's not really needed
				case '\\':
 
					// firstChar is a slash. We want to keep it, and the next character,
					// unless it's a new line. New lines as actual strings will be
					// preserved, but escaped new lines should be reduced.
					$this->_secondChar = $this->getChar();
 
					// If secondChar is a new line we discard firstChar and secondChar and restart the loop.
					if ($this->_secondChar === "\n") {
						break;
					}
 
					// append the escaped character and restart the loop.
					$this->_text .= $this->_firstChar . $this->_secondChar;
					break;
 
				// Since we're not dealing with any special cases we simply
				// output the character and continue our loop.
				default:
					$this->_text .= $this->_firstChar;
			}
		}
	}
 
	/**
	 * When a regular expression is detected this function crawls for the end of
	 * it and saves the whole regex.
	 *
	 * @throws \RuntimeException Unclosed regex will throw an error
	 */
	protected function saveRegex()
	{
		$this->_text .= $this->_firstChar . $this->_secondChar;
 
		while (($this->_firstChar = $this->getChar()) !== false) {
			if($this->_firstChar === '/')
				break;
 
			if ($this->_firstChar === '\\') {
				$this->_text .= $this->_firstChar;
				$this->_firstChar = $this->getChar();
			}
 
			if($this->_firstChar === "\n")
				throw new \RuntimeException('Unclosed regex pattern at position: ' . $this->_currentPosition);
 
			$this->_text .= $this->_firstChar;
		}
		$this->_secondChar = $this->getReal();
	}
 
	/**
	 * Checks to see if a character is alphanumeric.
	 *
	 * @param  string $char Just one character
	 * @return bool
	 */
	protected static function isAlphaNumeric($char)
	{
		return preg_match('/^[\w\$\pL]$/', $char) === 1 || $char == '/';
	}
 
	/**
	 * Replace patterns in the given string and store the replacement
	 *
	 * @param  string $js The string to lock
	 * @return bool
	 */
	protected function lock($js)
	{
		/* lock things like <code>"asd" + ++x;</code> */
		$lock = '"LOCK---' . crc32(time()) . '"';
 
		$matches = array();
		preg_match('/([+-])(\s+)([+-])/S', $js, $matches);
		if (empty($matches)) {
			return $js;
		}
 
		$this->locks[$lock] = $matches[2];
 
		$js = preg_replace('/([+-])\s+([+-])/S', "$1{$lock}$2", $js);
		/* -- */
 
		return $js;
	}
 
	/**
	 * Replace "locks" with the original characters
	 *
	 * @param  string $js The string to unlock
	 * @return bool
	 */
	protected function unlock($js)
	{
		if (empty($this->locks)) {
			return $js;
		}
 
		foreach ($this->locks as $lock => $replacement) {
			$js = str_replace($lock, $replacement, $js);
		}
 
		return $js;
	}
 
}