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
		--exclude=files/logs\
		*
	@echo "Done. File is aw-dist.tar.gz"

ini:
	@echo "Generating AW ini file"
	@if test -e scripts/php; \
		then \
		`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 -f ./scripts/ini/mk_ini.aw aw.ini.root > aw.ini; \
	else \
		echo "Cmdline php not found, cannot compile ini file"; \
	fi

properties:
	@echo "Generating property definitions"
	@if test -e scripts/php; \
		then \
		`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 -f ./scripts/prop/collect.aw \
	else \
		echo "Cmdline php not found, cannot collect properties"; \
	fi

msg:
	@echo "Generating message maps"
	@if test -e scripts/php; \
		then \
		`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 -q -f ./scripts/msg_scan.aw \
	else \
		echo "Cmdline php not found, cannot create message maps"; \
	fi

orb:
	@echo "Generating orb definitions"
	@if test -e scripts/php; \
		then \
		`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 -q -f ./scripts/mk_orb.aw \
	else \
		echo "Cmdline php not found, cannot create orb definitions"; \
	fi

remoting:
	@echo "Generating remoting proxy classes"
	@if test -e scripts/php; \
		then \
		`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 -q -f ./scripts/mk_remoting.aw \
	else \
		echo "Cmdline php not found, cannot create remoting proxy classes"; \
	fi

profile:
	@echo "Generating profiling information into code"
	@if test -e scripts/php; \
		then \
		`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 -q -f ./scripts/mk_profiling.aw \
	else \
		echo "Cmdline php not found, cannot create profiling information"; \
	fi

class:
	@`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=200 -d register_argc_argv=1 scripts/mk_class/mk_class.aw

pot:
	@`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=800 -d register_argc_argv=1 scripts/trans/mk_pot.aw

pot.dbg:
	@`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=800 -d register_argc_argv=1 scripts/trans/mk_pot.aw --dbg

pot.warn:
	@`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=800 -d register_argc_argv=1 scripts/trans/mk_pot.aw --warn-only

trans.aw:
	@`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=800 -d register_argc_argv=1 scripts/trans/mk_pot.aw --make-aw

trans.untrans:
	@`which php` -d safe_mode=Off -d memory_limit=200M -d max_execution_time=800 -d register_argc_argv=1 scripts/trans/mk_pot.aw --list-untranslated-strings

trans: pot trans.aw

js:
	@echo "Generating automatweb/js/js-min"
	@if test -e scripts/yuicompressor-2.4.2.jar; \
		then \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/jquery-1.3.2.min.js > automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_timer.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_aw_releditor.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_dump.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_formreset.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_aw_object_quickadd.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_tabs.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_gup.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_sup.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_bgiframe.min.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_dimensions.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_ajaxQueue.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_thickbox-compressed.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_autocomplete.min.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_hotkeys_0.0.3.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_shortcut_manager.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery-impromptu.1.5.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_init_session_modal.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery.selectboxes.min.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_aw_unload_handler.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_popup.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery.tooltip.min.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery.rightClick.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/jquery/plugins/jquery_please_wait_window.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/aw.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/browserdetect.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/cbobjects.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/ajax.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/CalendarPopupMin.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/popup_menu.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/BronCalendar.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/url.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/aw_help.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/other.js >> automatweb/js/js-min.js && \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/js/defs.js >> automatweb/js/js-min.js \
	else \
		echo "Yuicompressor not found. Can't create js"; \
	fi

styles:
	@echo "Generating automatweb/css/style-min.css"
	@if test -e scripts/yuicompressor-2.4.2.jar; \
		then \
			java -jar scripts/yuicompressor-2.4.2.jar automatweb/css/style.css > automatweb/css/style-min.css  \
	else \
		echo "Yuicompressor not found. Can't create js"; \
	fi

all: ini properties msg orb remoting
