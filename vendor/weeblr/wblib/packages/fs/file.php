<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author      weeblrPress
 * @copyright   (c) WeeblrPress - Weeblr,llc - 2020
 * @package     AMP on WordPress - weeblrAMP CE
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     1.12.5.783
 * @date        2020-05-19
 */

/** ensure this file is being included by a parent file */
defined( 'WBLIB_ROOT_PATH' ) || die;

/**
 * Manipulate Path on the file system
 *
 */
class WblFs_File {

	/**
	 * This method taken from the Joomla! platform, see (c) notice below
	 */
	/**
	 * @package     Joomla.Platform
	 * @subpackage  FileSystem
	 *
	 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE
	 */
	/**
	 * Makes path name safe to use.
	 *
	 * @param   string $path The full path to sanitise.
	 *
	 * @return  string  The sanitised string.
	 *
	 * @since   11.1
	 */
	public static function makeSafePath( $path ) {

		$regex = array( '#[^A-Za-z0-9_\\\/\(\)\[\]\{\}\#\$\^\+\.\'~`!@&=;,-]#' );

		return preg_replace( $regex, '', $path );
	}

	/**
	 * This method taken from the Joomla! platform, see (c) notice below
	 */
	/**
	 * @package     Joomla.Platform
	 * @subpackage  FileSystem
	 *
	 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE
	 */

	/**
	 * @package     Joomla.Platform
	 * @subpackage  FileSystem
	 *
	 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE
	 */
	public static function find( $paths, $file ) {

		// Force to array
		if ( ! is_array( $paths ) && ! ( $paths instanceof Iterator ) ) {
			settype( $paths, 'array' );
		}

		// Start looping through the path set
		foreach ( $paths as $path ) {
			// Get the path to the file
			$fullname = wbSlashJoin( $path, $file );

			// Is the path based on a stream?
			if ( ! wbContains( $path, '://' ) ) {
				// Not a stream, so do a realpath() to avoid directory
				// traversal attempts on the local file system.

				// Needed for substr() later
				$path     = realpath( $path );
				$fullname = realpath( $fullname );
			}

			/*
			 * The substr() check added to make sure that the realpath()
			 * results in a directory registered so that
			 * non-registered directories are not accessible via directory
			 * traversal attempts.
			 */
			if ( file_exists( $fullname ) && substr( $fullname, 0, strlen( $path ) ) == $path ) {
				return $fullname;
			}
		}

		// Could not find the file in the set of paths
		return false;
	}

	/**
	 * This method taken from the Joomla! platform, see (c) notice below
	 */
	/**
	 * Searches the directory paths for a given file.
	 *
	 * @param   mixed  $paths An path string or array of path strings to search in
	 * @param   string $file The file name to look for.
	 *
	 * @return  mixed   The full path and file name for the target file, or boolean false if the file is not found in
	 *     any of the paths.
	 *
	 * @since   11.1
	 */

	/**
	 * Include a file, if it exists and return its content
	 *
	 * @param String $fileName Full path to file
	 *
	 * @return String
	 */
	public static function getIncludedFile( $fileName ) {

		$includedFile = '';
		if ( file_exists( $fileName ) ) {
			ob_start();
			include $fileName;
			$includedFile = ob_get_contents();
			ob_end_clean();
		}

		return $includedFile;
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param   string $file The name of the file [not full path]
	 *
	 * @return  string  The sanitised string
	 *
	 * @since   11.1
	 */
	public function makeSafeFilename( $file ) {

		// Remove any trailing dots, as those aren't ever valid file names.
		$file = rtrim( $file, '.' );

		$regex = array( '#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#' );

		return trim( preg_replace( $regex, '', $file ) );
	}

	/**
	 * Force download by user of some content or an existing file.
	 *
	 * @param string $displayName The display name of the file, will be used by browser to save te file.
	 * @param string $filename The file (fullpathed) name of the file to download, if no content has been provided.
	 * @param array  $options A set of options for the download:
	 * 'content': the actual content to download, used instead of reading a file from disk if provided.
	 * 'content_type': mime type for the file, defaults to Application/octet-stream.
	 * 'cookies': an array of cookies defintion: array('name' => 'xxx', 'value'=>'xxx', 'expire' => 123456789).
	 * 'headers' an array of headers for the response, as strings.
	 * 'die' : if true, we'll exist after triggering the download. If false, control is returned to the caller, returned value is boolean true.
	 *
	 */
	public static function triggerDownload( $displayName, $filename = null, $options = array() ) {

		$fileContent = wbArrayGet( $options, 'content', null );
		$fromFile    = is_null( $fileContent );

		// caller wants to download a file, but does the file exist?
		if (
			$fromFile
			&&
			( empty( $filename ) || ! file_exists( $filename ) )
		) {
			return false;
		}

		// get filesize
		if ( $fromFile ) {
			$filesize = @filesize( $filename );
		} else {
			$filesize = strlen( $fileContent );
		}

		ob_end_clean(); //flush any other stuff from the ouput buffer

		// output
		header( 'Expires: 0' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', time() ) . ' GMT' );
		header( 'Pragma: public' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Accept-Ranges: bytes' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . $filesize );
		header( 'Content-Type: ' . wbArrayGet( $options, 'content_type', 'Application/octet-stream' ) );
		header( 'Content-Disposition: attachment; filename="' . $displayName . '"' );
		header( 'Connection: close' );
		$headers = wbArrayGet( $options, 'headers', array() );
		if ( ! empty( $headers ) ) {
			foreach ( $headers as $header ) {
				header( $header );
			}
		}

		$cookies = wbArrayGet( $options, 'cookies', array() );
		if ( ! empty( $cookies ) ) {
			foreach ( $cookies as $cookieDef ) {
				setcookie( wbArrayGet( $cookieDef, 'name' ), wbArrayGet( $cookieDef, 'value' ), wbArrayGet( $cookieDef, 'expire' ) );
			}
		}

		if ( $fromFile ) {
			// read file content by chunks and send it
			$offset = 0;
			do {
				$chunk = self::read( $filename, $incpath = false, $amount = 81920, $chunksize = 8192, $offset );
				if ( $chunk ) {
					print $chunk;
					$offset += strlen( $chunk );
				}
			} while ( $chunk );
		} else {
			// just print the provided content
			print $fileContent;
		}

		// die, to have file content sent
		$die = wbArrayGet( $options, 'die', true );
		if ( $die ) {
			exit();
		}

		return true;
	}

	/**
	 * This method taken from the Joomla! platform, see (c) notice below
	 */
	/**
	 * Read the contents of a file
	 *
	 * @param   string  $filename The full file path
	 * @param   boolean $incpath Use include path
	 * @param   integer $amount Amount of file to read
	 * @param   integer $chunksize Size of chunks to read
	 * @param   integer $offset Offset of the file
	 *
	 * @return  mixed  Returns file contents or boolean False if failed
	 *
	 * @since   11.1
	 */
	public static function read( $filename, $incpath = false, $amount = 0, $chunksize = 8192, $offset = 0 ) {

		$data = null;

		if ( $amount && $chunksize > $amount ) {
			$chunksize = $amount;
		}

		if ( false === $fh = fopen( $filename, 'rb', $incpath ) ) {

			return false;
		}

		clearstatcache();

		if ( $offset ) {
			fseek( $fh, $offset );
		}

		if ( $fsize = @ filesize( $filename ) ) {
			if ( $amount && $fsize > $amount ) {
				$data = fread( $fh, $amount );
			} else {
				$data = fread( $fh, $fsize );
			}
		} else {
			$data = '';

			/*
			 * While it's:
			 * 1: Not the end of the file AND
			 * 2a: No Max Amount set OR
			 * 2b: The length of the data is less than the max amount we want
			 */
			while ( ! feof( $fh ) && ( ! $amount || strlen( $data ) < $amount ) ) {
				$data .= fread( $fh, $chunksize );
			}
		}

		fclose( $fh );

		return $data;
	}
}
