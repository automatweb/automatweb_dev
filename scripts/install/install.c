/*
 * AW install script
 * path to configuration file should probably come from command line
 * $Header: /home/cvs/automatweb_dev/scripts/install/Attic/install.c,v 1.1 2002/04/04 22:07:22 duke Exp $
 */
#include <stdio.h>
#include <string.h>
main(int argc, char **argv)
{
	FILE *fpconfig; // config file 
	FILE *pmysql; // pipe to mysql

	// these come from stdin/client
	char dbname[60], dbhost[60], dbuser[60], dbpass[60];

	// these come from install.ini
	char mysql_host[30], mysql_user[30], mysql_pass[30], mysql_client[30];
	char default_user[30], default_pass[30];

	// for internal use
	char cline[100],myline[100],cmdline[200];
	char *ckey,*cval;

	if ((fpconfig = fopen("install.ini","r")) == NULL)
	{
		printf("Can't open configuration file!\n");
		return 1;
	}
	else
	{
		while ( fgets(cline,sizeof(cline)-1,fpconfig) != NULL)
		{
			// snarf the key
			ckey = strtok(cline,"=");
			// and now the value up to the end of line
			cval = strtok(NULL,"\n");
			// check whether the key is one of the things we need
			// and if so, assign it to correct variable
			if ( strcmp(ckey,"mysql.host") == 0)
			{
				strncpy(mysql_host,cval,sizeof(mysql_host)-1);
			}
			else if ( strcmp(ckey,"mysql.user") == 0)
			{
				strncpy(mysql_user,cval,sizeof(mysql_user)-1);
			}
			else if ( strcmp(ckey,"mysql.pass") == 0)
			{
				strncpy(mysql_pass,cval,sizeof(mysql_pass)-1);
			}
			else if ( strcmp(ckey,"mysql.client") == 0)
			{
				strncpy(mysql_client,cval,sizeof(mysql_client)-1);
			};
		};
		fclose(fpconfig);
	};

	sprintf(cmdline,"mysql -h %s -u %s --password=%s",mysql_host,mysql_user,mysql_pass);

	// get the information from the client
	printf("AW installer\n");
	printf("db.name: ");
	scanf("%20s",dbname);
	printf("db.host: ");
	scanf("%20s",dbhost);
	printf("db.user: ");
	scanf("%20s",dbuser);
	printf("db.pass: ");
	scanf("%20s",dbpass);
	printf("default.user: ");
	scanf("%20s",default_user);
	printf("default.pass: ");
	scanf("%20s",default_pass);

	// now we want to connect to database and create the bloody table
	printf("executing %s\n",cmdline);
	if ((pmysql = popen(cmdline,"w")) == NULL)
	{
		printf("Can't open pipe to MySQL!\n");
		return 1;
	}
	else
	{
		sprintf(myline,"CREATE DATABASE %s;\n",dbname);
		fputs(myline,pmysql);

		sprintf(myline,"GRANT ALL PRIVILEGES ON %s.* TO %s@%s IDENTIFIED BY '%s';\n",dbname,dbuser,mysql_client,dbpass);
		fputs(myline,pmysql);
		
		pclose(pmysql);
	};
	printf("YIKES!\n");
	return 0;
}
