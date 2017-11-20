# What is Locker?

Locker is a simple, platform-independent PHP class for mutual exclusive locking
(mutex). It helps you ensure that a particular resource can only be used by one
thread at a time, which is especially important when running a script on a regular basis and there might be a chance that a run starts before the previous has been finished completely.

The locking is done by simply creating a directory at a certain location via `mkdir` command. This command would fail if the directory already existed. On the other hand, if it succeeds the creation is done in a single atomic action, so it cannot cause any race conditions.


## Basic Usage

First, you have to prepare an empty directory which is writable for PHP scripts.
Let's say, the path is stored in a variable called `$lockDir`, then do the
following:

```php
require_once 'locker.class.php';
$locker = new Locker( $lockDir );

if ( ! $locker->get_lock('myProcess') )
{
   die('Could not get lock');
}

// do some work

$locker->release_lock('myProcess');
```

## Avoiding Infinite Locks

Even if you forget to call the `release_lock` method, all locks created by the
current script will be removed automatically on shutdown. But when there is a
power failure, or the script gets killed or crashes, the locks would remain
forever, blocking any subsequent attempts to get exclusive access to the locked
resources. In order to avoid this scenario, Locker has an automatic unlock
feature. 

By default, when trying to lock a resource, an existing lock is ignored if it is
older than 24 hours. You can adjust this "auto unlock period" by providing a
second parameter when calling the `get_lock` method:

```php
if ( ! $locker->get_lock( 'myProcess', 180 ) )
{
   die('Could not get lock although ignoring locks older than 3 minutes)');
}
```

Alternatively, you may set the `autoUnlockPeriod` property of the Locker object
to the desired number of seconds:

```php
$locker->autoUnlockPeriod = 180;
```

You should carefully consider which auto unlock period is suitable for your
project. If it is too short, there might be occasions when a lock gets
auto-removed although the originating script is still running. If it is too
long, certain functions of your application might be blocked unneccessarily long
after a crash. 


## Changing the Time-Out

If a lock cannot be obtained, Locker will wait a second, then retry, then wait
another second, retry etc. By default this trial process lasts five seconds,
after which Locker will give up. If you want a shorter or longer time-out
period, you may set the desired number of seconds as third parameter when
calling the `get_lock` method:

```php
if ( ! $locker->get_lock( 'myProcess', 180, 10 ) )
{
   die('Could not get lock in 10 seconds');
}
```

Alternatively, you may set the `timeOutPeriod` property of the Locker object to
the desired number of seconds:

```php
$locker->timeOutPeriod = 10;
```

Don't worry too much about the time-out period. It can make your application a
little bit more user-friendly but is irrelevant, if you run your scripts via cron
jobs. From a user's point of view it is just less annoying to wait some seconds
for a reponse than to get an error message.
