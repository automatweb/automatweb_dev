dist:
	@echo "Generating AW distribution file"
	@if test -e aw-dist.tar.gz; then\
		rm aw-dist.tar.gz;\
	else :; fi
	@tar czf aw-dist.tar.gz \
		--exclude=doc\
		--exclude=scripts\
		--exclude=quiz\
		--exclude=stats\
		--exclude=img\
		--exclude=java\
		*
	@echo "Done. File is aw-dist.tar.gz"
