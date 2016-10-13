<?php

/**
 * This class can be used to log memory usage of your PHP code over time,
 * with the intention of helping you locate where you're running into memory
 * usage problems. It is not a full-featured profiling tool like Xdebug or
 * Webgrind.
 *
 * MemLog writes out data to a log file, which can be analyzed with another
 * tool (or by hand if you wish, it's just pseudo-JSON). Each line is a JSON
 * array with the following fields:
 * 
 * [time since logging began, in seconds (float),
 *  current PHP memory usage, in bytes (integer),
 *  file name of the currently executing code, if any (string),
 *  class name of the currently executing code, if any (string),
 *  function name of the currently executing code, if any (string)]
 *
 * Basic usage is as follows:
 *
 * $memlog = new MemLog();
 * $memlog->start('My Terrible Code');
 * 
 * ...your bad code eats gobs of memory...
 *
 * $memlog->stop();
 *
 * Log files are saved with a base path of MemLog::$basePath, Unless you have
 * a compelling reason, /tmp is a good choice for $basePath.
 *
 * @author Bob Somers
 */
class MemLog {
   public static $basePath = '/tmp';
   private static $maxBufferSize = 100;

   private $isLogging = false;
   private $fp = null;
   private $buffer = array();
   private $startTime = 0.0;

   public function __construct() {
      // Tweak this if necessary.
      declare(ticks=1);
   }
    
   public function __destruct() {
      if ($this->isLogging) {
         $this->stop();
      }
   }

   public function start($name = 'MemLog') {
      if ($this->isLogging) return;

      $name .= ' - ' . date('r');

      list($usec, $time) = explode(' ', microtime());
      list($junk, $usec) = explode('.', $usec);
      $fileName = 'php_mem_usage.' . date('Y-m-d-H-i-s', $time) . "-$usec.log";

      $this->fp = fopen(self::$basePath . '/' . $fileName, 'w');
      fwrite($this->fp, $name);
      $this->buffer = array();

      $this->isLogging = true;

      $this->startTime = microtime(true);
      register_tick_function(array(&$this, 'tick'));
   }

   public function stop() {
      if (!$this->isLogging) return;

      unregister_tick_function(array(&$this, 'tick'));

      $this->flush();
      fclose($this->fp);

      $this->isLogging = false;
   }

   public function tick() {
      $timestamp = microtime(true) - $this->startTime;
      $memory = memory_get_usage(true);
        
      $file = '';
      $class = '';
      $function = '';
      $line = '';
      // Don't get arguments of functions, and only get the top two stack
      // frames
      $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
      if (isset($backtrace[0])) {
         if (isset($backtrace[0]['file'])) {
            $file = basename($backtrace[0]['file']);
         }
         if (isset($backtrace[1]['class'])) {
            $class = $backtrace[1]['class'];
         }
         if (isset($backtrace[1]['function'])) {
            $function = $backtrace[1]['function'];
         }
         if (isset($backtrace[0]['line'])) {
            $line = $backtrace[0]['line'];
         }
      }

      $this->buffer[] = array(
         sprintf("%0.6f", $timestamp),
         $memory,
         $file,
         $line,
         $class,
         $function
      );

      if (count($this->buffer) > self::$maxBufferSize) {
         $this->flush();
      }
   }

   private function flush() {
      for ($i = 0; $i < count($this->buffer); $i++) {
         fwrite($this->fp, "\n" . json_encode($this->buffer[$i]));
      }
      $this->buffer = array();
   }
}

?>
