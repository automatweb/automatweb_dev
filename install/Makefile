def:
	@echo "Generating default site"
	@if test -e default.tar.gz; then\
		rm default.tar.gz;\
	else :; fi
	@cd default; tar czpf ../default.tar.gz --exclude CVS .
	@echo "Done. File is default.tar.gz"
