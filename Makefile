dist:
	@echo "Generating AW distribution file"
	@if test -e aw-dist.tar.gz; then\
		rm aw-dist.tar.gz;\
	else :; fi
	@tar czf aw-dist.tar.gz \
		--exclude=addons\
		--exclude=doc\
		--exclude=stats\
		--exclude=img\
		*
	@echo "Done. File is aw-dist.tar.gz"

ini:
	@echo "Generating AW ini file"
	@if test -e scripts/php; \
		then \
		./scripts/php -C -n -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 -f ./scripts/ini/mk_ini.aw aw.ini.root > aw.ini; \
	else \
		echo "Cmdline php not found, cannot compile ini file"; \
	fi

properties:
	@echo "Generating property definitions"
	@if test -e scripts/php; \
		then \
		./scripts/php -C -n -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 -f ./scripts/prop/collect.aw \
	else \
		echo "Cmdline php not found, cannot collect properties"; \
	fi

msg:
	@echo "Generating message maps"
	@if test -e scripts/php; \
		then \
		./scripts/php -C -n -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 -q -f ./scripts/msg_scan.aw \
	else \
		echo "Cmdline php not found, cannot create message maps"; \
	fi

orb:
	@echo "Generating orb definitions"
	@if test -e scripts/php; \
		then \
		./scripts/php -C -n -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 -q -f ./scripts/mk_orb.aw \
	else \
		echo "Cmdline php not found, cannot create orb definitions"; \
	fi

remoting:
	@echo "Generating remoting proxy classes"
	@if test -e scripts/php; \
		then \
		./scripts/php -C -n -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 -q -f ./scripts/mk_remoting.aw \
	else \
		echo "Cmdline php not found, cannot create remoting proxy classes"; \
	fi

class:
	@scripts/php -C -n -q -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 scripts/mk_class/mk_class.aw

all: ini properties msg orb remoting
