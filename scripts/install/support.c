/*
 * support functions
 * $Header: /home/cvs/automatweb_dev/scripts/install/Attic/support.c,v 1.1 2002/04/10 01:03:21 duke Exp $
 */
#include <unistd.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>

void create_dir(char path[])
{
	if ( (mkdir(path,0777)) != 0)
	{
		perror(path);
		//printf("mkdir failed - %s!\n",errno);	
		exit(1);
	};
}

void copy_site_files(char path[])
{
	char cmdline[200];
	int ret;
	strncpy(cmdline,"cd ",sizeof(cmdline)-1);
	strcat(cmdline,path);

	strcat(cmdline,"; tar xzf /www/automatweb_dev/install/default.tar.gz");
	printf("executing %s",cmdline);
	ret = system(cmdline);
	printf("result code is %d\n",ret);
}

void create_apache_conf(char path[], char name[])
{
	char fullpath[200];
	char servername[200];
	char docroot[200];
	FILE *apconf;
	strncpy(fullpath,"/etc/apache/vhosts/",sizeof(fullpath)-1);
	strcat(fullpath,name);

	if ((apconf = fopen(fullpath,"w")) == NULL)
	{
		printf("Can't open apache configuration file %s for writing!\n",fullpath);
		exit(1);
	}
	else
	{
		fputs("<VirtualHost 194.204.30.123>\n",apconf);

		fputs("# created by AW installer\n",apconf);

		strncpy(servername,"ServerName ",sizeof(servername)-1);
		strcat(servername,name);
		strcat(servername,"\n");
		fputs(servername,apconf);

		strncpy(docroot,"Documentroot ",sizeof(servername)-1);
		strcat(docroot,path);
		strcat(docroot,"/public");
		strcat(docroot,"\n");
		fputs(docroot,apconf);

		fputs("</VirtualHost>\n\n",apconf);
		fclose(apconf);
	};
}

