# MemLog: A quick and dirty memory usage profiler for PHP

MemLog can be used to log memory usage of your PHP code over time, with the
intention of helping you locate where you're running into memory usage
problems. **It is not a full-featured profiling tool like Xdebug or
Webgrind.**

MemLog writes out data to a log file, which can be analyzed with another tool
(or by hand if you wish, it's just pseudo-JSON). Each line is a JSON array with
the following fields:

    [time since logging began, in seconds (float),
     current PHP memory usage, in bytes (integer),
     file name of the currently executing code, if any (string),
     class name of the currently executing code, if any (string),
     function name of the currently executing code, if any (string)]

Basic usage is as follows:

    $memlog = new MemLog();
    $memlog->start('My Terrible Code');
    
    // ...your bad code eats gobs of memory...
    
    $memlog->stop();

Log files are saved with a base path of `MemLog::$basePath`, Unless you have
a compelling reason, `/tmp` is a good choice for `$basePath`.

Also included is a very basic MemLog viewer. You **should not** host the
viewer on a production server, as it has some security concerns because it's
a quick and dirty example of how you can show MemLog files in a meaningful
way.

Patches/contributions are welcome.
