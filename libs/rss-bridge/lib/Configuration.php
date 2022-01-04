<?php
namespace RSS_Bridge;
/**
 * This file is part of RSS-Bridge, a PHP project capable of generating RSS and
 * Atom feeds for websites that don't have one.
 *
 * For the full license information, please view the UNLICENSE file distributed
 * with this source code.
 *
 * @package	Core
 * @license	http://unlicense.org/ UNLICENSE
 * @link	https://github.com/rss-bridge/rss-bridge
 */

/**
 * Configuration module for RSS-Bridge.
 *
 * This class implements a configuration module for RSS-Bridge.
 */
final class Configuration {

	/**
	 * Holds the current release version of RSS-Bridge.
	 *
	 * Do not access this property directly!
	 * Use {@see Configuration::getVersion()} instead.
	 *
	 * @var string
	 *
	 * @todo Replace this property by a constant.
	 */
	public static $VERSION = 'dev.2020-11-10';

	/**
	 * Holds the configuration data.
	 *
	 * Do not access this property directly!
	 * Use {@see Configuration::getConfig()} instead.
	 *
	 * @var array|null
	 */
	private static $config = null;

	/**
	 * Throw an exception when trying to create a new instance of this class.
	 *
	 * @throws \LogicException if called.
	 */
	public function __construct(){
		throw new \LogicException('Can\'t create object of this class!');
	}

	/**
	 * Verifies the current installation of RSS-Bridge and PHP.
	 *
	 * Returns an error message and aborts execution if the installation does
	 * not satisfy the requirements of RSS-Bridge.
	 *
	 * **Requirements**
	 * - PHP 5.6.0 or higher
	 * - `openssl` extension
	 * - `libxml` extension
	 * - `mbstring` extension
	 * - `simplexml` extension
	 * - `curl` extension
	 * - `json` extension
	 * - The cache folder specified by {@see PATH_CACHE} requires write permission
	 * - The whitelist file specified by {@see WHITELIST} requires write permission
	 *
	 * @link http://php.net/supported-versions.php PHP Supported Versions
	 * @link http://php.net/manual/en/book.openssl.php OpenSSL
	 * @link http://php.net/manual/en/book.libxml.php libxml
	 * @link http://php.net/manual/en/book.mbstring.php Multibyte String (mbstring)
	 * @link http://php.net/manual/en/book.simplexml.php SimpleXML
	 * @link http://php.net/manual/en/book.curl.php Client URL Library (curl)
	 * @link http://php.net/manual/en/book.json.php JavaScript Object Notation (json)
	 *
	 * @return void
	 */
	public static function verifyInstallation() {

		// Check PHP version
		if(version_compare(PHP_VERSION, '5.6.0') === -1)
			self::reportError('RSS-Bridge requires at least PHP version 5.6.0!');

		// extensions check
		if(!extension_loaded('openssl'))
			self::reportError('"openssl" extension not loaded. Please check "php.ini"');

		if(!extension_loaded('libxml'))
			self::reportError('"libxml" extension not loaded. Please check "php.ini"');

		if(!extension_loaded('mbstring'))
			self::reportError('"mbstring" extension not loaded. Please check "php.ini"');

		if(!extension_loaded('simplexml'))
			self::reportError('"simplexml" extension not loaded. Please check "php.ini"');

		// Allow RSS-Bridge to run without curl module in CLI mode without root certificates
		if(!extension_loaded('curl') && !(php_sapi_name() === 'cli' && empty(ini_get('curl.cainfo'))))
			self::reportError('"curl" extension not loaded. Please check "php.ini"');

		if(!extension_loaded('json'))
			self::reportError('"json" extension not loaded. Please check "php.ini"');

	}

	/**
	 * Loads the configuration from disk and checks if the parameters are valid.
	 *
	 * Returns an error message and aborts execution if the configuration is invalid.
	 *
	 * The RSS-Bridge configuration is split into two files:
	 * - {@see Constants::FILE_CONFIG_DEFAULT} The default configuration file that ships
	 * with every release of RSS-Bridge (do not modify this file!).
	 * - {@see Constants::FILE_CONFIG} The local configuration file that can be modified
	 * by server administrators.
	 *
	 * RSS-Bridge will first load {@see Constants::FILE_CONFIG_DEFAULT} into memory and then
	 * replace parameters with the contents of {@see Constants::FILE_CONFIG}. That way new
	 * parameters are automatically initialized with default values and custom
	 * configurations can be reduced to the minimum set of parametes necessary
	 * (only the ones that changed).
	 *
	 * The configuration files must be placed in the root folder of RSS-Bridge
	 * (next to `index.php`).
	 *
	 * _Notice_: The configuration is stored in {@see Configuration::$config}.
	 *
	 * @return void
	 */
	public static function loadConfiguration() {

		if(!file_exists(Constants::FILE_CONFIG_DEFAULT))
			self::reportError('The default configuration file is missing at ' . Constants::FILE_CONFIG_DEFAULT);

		Configuration::$config = parse_ini_file(Constants::FILE_CONFIG_DEFAULT, true, INI_SCANNER_TYPED);
		if(!Configuration::$config)
			self::reportError('Error parsing ' . Constants::FILE_CONFIG_DEFAULT);

		if(file_exists(Constants::FILE_CONFIG)) {
			// Replace default configuration with custom settings
			foreach(parse_ini_file(Constants::FILE_CONFIG, true, INI_SCANNER_TYPED) as $header => $section) {
				foreach($section as $key => $value) {
					Configuration::$config[$header][$key] = $value;
				}
			}
		}
	}

	/**
	 * Returns the value of a parameter identified by section and key.
	 *
	 * @param string $section The section name.
	 * @param string $key The property name (key).
	 * @return mixed|null The parameter value.
	 */
	public static function getConfig($section, $key) {
		if(array_key_exists($section, self::$config) && array_key_exists($key, self::$config[$section])) {
			return self::$config[$section][$key];
		}

		return null;
	}

	/**
	 * Returns the current version string of RSS-Bridge.
	 *
	 * This function returns the contents of {@see Configuration::$VERSION} for
	 * regular installations and the git branch name and commit id for instances
	 * running in a git environment.
	 *
	 * @return string The version string.
	 */
	public static function getVersion() {
		return Configuration::$VERSION;

	}

	/**
	 * Reports an configuration error for the specified section and key to the
	 * user and ends execution
	 *
	 * @param string $section The section name
	 * @param string $key The configuration key
	 * @param string $message An optional message to the user
	 *
	 * @return void
	 */
	private static function reportConfigurationError($section, $key, $message = '') {

		$report = "Parameter [{$section}] => \"{$key}\" is invalid!" . PHP_EOL;

		if(file_exists(Constants::FILE_CONFIG)) {
			$report .= 'Please check your configuration file at ' . Constants::FILE_CONFIG . PHP_EOL;
		} elseif(!file_exists(Constants::FILE_CONFIG_DEFAULT)) {
			$report .= 'The default configuration file is missing at ' . Constants::FILE_CONFIG_DEFAULT . PHP_EOL;
		} else {
			$report .= 'The default configuration file is broken.' . PHP_EOL
			. 'Restore the original file from ' . REPOSITORY . PHP_EOL;
		}

		$report .= $message;
		self::reportError($report);

	}

	/**
	 * Reports an error message to the user and ends execution
	 *
	 * @param string $message The error message
	 *
	 * @return void
	 */
	private static function reportError($message) {
		throw new Exception( $message );
	}
}
