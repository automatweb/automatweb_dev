dist:
	@echo "Generating AW distribution file"
	@if test -e aw-dist.tar.gz; then\
		rm aw-dist.tar.gz;\
	else :; fi
	@tar czf aw-dist.tar.gz \
		--exclude=addons\
		--exclude=doc\
		--exclude=scripts\
		--exclude=quiz\
		--exclude=stats\
		--exclude=img\
		--exclude=java\
		*
	@echo "Done. File is aw-dist.tar.gz"

ini:
	@echo "Generating AW ini file"
	./scripts/ini/php -d register_argc_argv=1 -q -f ./scripts/ini/mk_ini.aw ../../aw.ini.root > aw.ini
