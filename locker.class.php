<?php
/*
	Locker - Simple, platform-independant PHP class for 
	mutual exclusive locking (mutex)

	Copyright (c) 2017, Martin Wandelt

	...................................................................
	The MIT License (MIT)

	Permission is hereby granted, free of charge, to any person
	obtaining a copy of this software and associated documentation files
	(the "Software"), to deal in the Software without restriction,
	including without limitation the rights to use, copy, modify, merge,
	publish, distribute, sublicense, and/or sell copies of the Software,
	and to permit persons to whom the Software is furnished to do so,
	subject to the following conditions:

	The above copyright notice and this permission notice shall be
	included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
	MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
	BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
	ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
	CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.
	...................................................................
*/

class Locker {

	public $lockDir = '';
	public $autoUnlockPeriod = 24*60*60; // seconds
	public $timeOutPeriod = 5; // seconds
	public $lockedKeys = array ();


	public function __construct( $lockDir = '' )
	{
		$this->lockDir = $lockDir ? rtrim( $lockDir, '/' ) : dirname( __FILE__ );
	}


	public function get_lock( $key, $autoUnlockPeriod = NULL, $timeOutPeriod = NULL )
	{
		if ( in_array( $key, $this->lockedKeys ) )
		{
			return TRUE;
		}
		
		if ( ! is_dir( $this->lockDir ) )
		{
			return FALSE;
		}
		
		$autoUnlockPeriod = empty( $autoUnlockPeriod ) ? $this->autoUnlockPeriod : $autoUnlockPeriod;
		$timeOutPeriod = empty( $timeOutPeriod ) ? $this->timeOutPeriod : $timeOutPeriod;
		$path = "{$this->lockDir}/{$key}";
		
		if ( is_dir( $path ) && $autoUnlockPeriod )
		{
			$mtime = filemtime( $path );

			if ( ( time() - $mtime ) > $autoUnlockPeriod )
			{
				rmdir( $path );
			}
		}

		$oldUmask = umask(0);
		$connectTime = time();

		while ( ! @mkdir( $path, 0777 ) )
		{
			if ( time() - $connectTime > $timeOutPeriod )
			{
				return FALSE;
			}
			
			usleep(100000);
		}
		
		umask( $oldUmask );
		$this->lockedKeys[] = $key;
		register_shutdown_function( array( $this, 'release_lock' ), $key );
		return TRUE;
	}

	
	public function release_lock( $key )
	{
		$path = "{$this->lockDir}/{$key}";
		
		if ( is_dir( $path ) )
		{
			rmdir( $path );
		}
		
		if ( in_array( $key, $this->lockedKeys ) )
		{
			unset( $this->lockedKeys[ $key ] );
		}
	}
}

// end of file locker.class.php
