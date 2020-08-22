
Fatal error: Uncaught TypeError: Return value of SemanticVersionHelp::addCommand() must be an instance of SemanticVersionHelp, none returned in /www/tests/semantic-version/version.php:1120
Stack trace:
#0 /www/tests/semantic-version/version.php(341): SemanticVersionHelp->addCommand(NULL, 'patch', Array)
#1 /www/tests/semantic-version/version.php(192): SemanticVersion->showHelp()
#2 /www/tests/semantic-version/version.php(106): SemanticVersion->dispatcher()
#3 /www/tests/semantic-version/version.php(1248): SemanticVersion->__construct()
#4 {main}
  thrown in /www/tests/semantic-version/version.php on line 1120
