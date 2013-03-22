<?php

$composer_autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composer_autoload)) {
	require_once $composer_autoload;
}

if (!class_exists('Composer\Autoload\ClassLoader', false)) {
	die(
		'You need to set up the project dependencies using the following commands:' . PHP_EOL .
		'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
		'php composer.phar install' . PHP_EOL
	);
}

$REDIS_BACKEND = getenv('REDIS_BACKEND');
$REDIS_BACKEND_DB = getenv('REDIS_BACKEND_DB');
if(!empty($REDIS_BACKEND)) {
	if (empty($REDIS_BACKEND_DB)) 
		Resque::setBackend($REDIS_BACKEND);
	else
	Resque::setBackend($REDIS_BACKEND, $REDIS_BACKEND_DB);
}
	
	// Set log level for resque-scheduler
$logLevel = 0;
$LOGGING = getenv('LOGGING');
$VERBOSE = getenv('VERBOSE');
$VVERBOSE = getenv('VVERBOSE');
if(!empty($LOGGING) || !empty($VERBOSE)) {
	$logLevel = ResqueScheduler_Worker::LOG_NORMAL;
}
else if(!empty($VVERBOSE)) {
	$logLevel = ResqueScheduler_Worker::LOG_VERBOSE;
}

// Check for jobs every $interval seconds
$interval = 5;
$INTERVAL = getenv('INTERVAL');
if(!empty($INTERVAL)) {
	$interval = $INTERVAL;
}

// Load the user's application if one exists
$APP_INCLUDE = getenv('APP_INCLUDE');
if($APP_INCLUDE) {
	if(!file_exists($APP_INCLUDE)) {
		die('APP_INCLUDE ('.$APP_INCLUDE.") does not exist.\n");
	}

	require_once $APP_INCLUDE;
}

$worker = new ResqueScheduler_Worker();
$worker->logLevel = $logLevel;

$PIDFILE = getenv('PIDFILE');
if ($PIDFILE) {
	file_put_contents($PIDFILE, getmypid()) or
		die('Could not write PID information to ' . $PIDFILE);
}

fwrite(STDOUT, "\033[00;32m*** Starting scheduler worker\033[00m\n");
$worker->work($interval);
fwrite(STDOUT, "\033[01;34m*** Stopping scheduler worker\033[00m\n");

/* End of file resque-scheduler.php */
/* Location: ./resque-scheduler.php */