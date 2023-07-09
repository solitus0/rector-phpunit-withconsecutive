rector:
	vendor/bin/rector process

debug_rector:
	RECTOR_ALLOW_XDEBUG=1 vendor/bin/rector process --xdebug --debug
