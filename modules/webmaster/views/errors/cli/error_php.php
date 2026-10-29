A PHP Error was encountered

Severity: <?= $severity, "\n"; ?>
Message: <?= $message, "\n"; ?>
Filename: <?= $filepath, "\n"; ?>
Line Number: <?= $line; ?>

Backtrace:
<?php foreach (debug_backtrace() as $error) : ?>
	<?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>
		File: <?= $error['file'], "\n"; ?>
		Line: <?= $error['line'], "\n"; ?>
		Function: <?= $error['function'], "\n\n"; ?>
	<?php endif ?>
<?php endforeach ?>