/*
 * AW install script
 * path to configuration file should probably come from command line
 * $Header: /home/cvs/automatweb_dev/scripts/install/Attic/install.c,v 1.3 2002/04/10 01:03:21 duke Exp $
 */
#include <stdio.h>
#include <string.h>
#include <errno.h>
// for mkdir
#include <sys/stat.h>
#include <sys/types.h>
#include <fcntl.h>
#include <unistd.h>

main(int argc, char **argv)
{
	FILE *fpconfig; // config file 
	FILE *pmysql; // pipe to mysql

	// these come from stdin/client
	char dbname[60], dbhost[60], dbuser[60], dbpass[60], sitename[60];

	// these come from install.ini
	char mysql_host[30], mysql_user[30], mysql_pass[30], mysql_client[30];
	char default_user[30], default_pass[30], wwwroot[30];

	// for internal use
	char cline[100],myline[100],cmdline[200],path[100];
	char *ckey,*cval;

	extern int errno;

	if ((fpconfig = fopen("/www/automatweb_dev/scripts/install/install.ini","r")) == NULL)
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
			}
			else if ( strcmp(ckey,"default_user") == 0)
			{
				strncpy(default_user,cval,sizeof(default_user)-1);
			}
			else if ( strcmp(ckey,"default_pass") == 0)
			{
				strncpy(default_pass,cval,sizeof(default_pass)-1);
			}
			else if ( strcmp(ckey,"wwwroot") == 0)
			{
				strncpy(wwwroot,cval,sizeof(wwwroot)-1);
			};
		};
		fclose(fpconfig);
	};

	// get the information from the client
	// tegelikult võiks see vajalik kataloogistrukuur tulla ju tar failist?
	// oh yes.
	printf("AW installer\n");
	
	printf("db.name (will also be used as sitename: ");
	scanf("%20s",dbname);
	printf("db.host: ");
	scanf("%20s",dbhost);
	printf("db.user: ");
	scanf("%20s",dbuser);
	printf("db.pass: ");
	scanf("%20s",dbpass);
	printf("default.user (%s): ",default_user);
	scanf("%20s",default_user);
	printf("default.pass (%s): ",default_pass);
	scanf("%20s",default_pass);

	strncpy(sitename,dbname,sizeof(sitename)-1);
	
	strncpy(path,wwwroot,sizeof(wwwroot)-1);

	strcat(path,sitename);
	create_dir(path);
	copy_site_files(path);
	create_apache_conf(path,sitename);

	return 0;

	// create required directories
	// site_dir
	// pagecache
	// public
	// public/img
	// archive
	// files
	//
	//

	// get the database dump
	sprintf(cmdline,"mysqldump -d -h %s -u %s --password=%s samaw > /tmp/dump.tmp",mysql_host,mysql_user,mysql_pass);
	printf("dumping samaw...\n");
	printf("executing %s\n",cmdline);
	system(cmdline);
	printf("dump complete...\n");
	
	// now we want to connect to database and create the bloody table
	sprintf(cmdline,"mysql -h %s -u %s --password=%s",mysql_host,mysql_user,mysql_pass);
	printf("executing %s\n",cmdline);
	if ((pmysql = popen(cmdline,"w")) == NULL)
	{
		printf("Can't open pipe to MySQL!\n");
		// drop dead
		return 1;
	};

	sprintf(myline,"CREATE DATABASE %s;\n",dbname);
	fputs(myline,pmysql);

	sprintf(myline,"GRANT ALL PRIVILEGES ON %s.* TO %s@%s IDENTIFIED BY '%s';\n",dbname,dbuser,mysql_client,dbpass);
	fputs(myline,pmysql);
		
	pclose(pmysql);

	sprintf(cmdline,"mysql -h %s -u %s --password=%s %s< /tmp/dump.tmp",mysql_host,dbuser,dbpass,dbname);
	printf("Importing database dump\n");
	printf("Executing %s\n",cmdline);
	system(cmdline);
	unlink("/tmp/dump.tmp");
	printf("Import done\n");

	/*
	printf("Creating default entries\n");
	sprintf(cmdline,"mysql -h %s -u %s --password=%s %s",mysql_host,dbuser,dbpass,dbname);
	printf("executing %s\n",cmdline);
	if ((pmysql = popen(cmdline,"w")) == NULL)
	{
		printf("Can't open pipe to MySQL!\n");
		return 1;
	};

	sprintf(myline,"INSERT INTO users (uid,password) VALUES ('%s','%s');\n",default_user,default_pass);
	printf("Executing %s\n",myline);
	fputs(myline,pmysql);
	
	sprintf(myline,"INSERT INTO groups (gid,name,type,priority) VALUES (1,'root user',1,100000000);\n");
	printf("Executing %s\n",myline);
	fputs(myline,pmysql);
	
	sprintf(myline,"INSERT INTO groupmembers (gid,uid) values(1,'%s');\n",default_user);
	printf("Executing %s\n",myline);
	fputs(myline,pmysql);
	
	sprintf(myline,"INSERT INTO groups (gid,name,type,priority) VALUES (2,'admins',0,1);\n");
	printf("Executing %s\n",myline);
	fputs(myline,pmysql);
	
	sprintf(myline,"INSERT INTO groupmembers (gid,uid) VALUES (2,'%s');\n",default_user);
	printf("Executing %s\n",myline);
	fputs(myline,pmysql);
	
	sprintf(myline,"INSERT INTO objects (oid,parent,name,class_id,status) VALUES (1,0,'root',1,2);\n");
	printf("Executing %s\n",myline);
	fputs(myline,pmysql);
	
	sprintf(myline,"INSERT INTO objects (oid,parent,name,class_id,status) VALUES (2,1,'admins',37,2);\n");
	printf("Executing %s\n",myline);
	fputs(myline,pmysql);
	
	sprintf(myline,"INSERT INTO acl VALUES(1,2,2,1);\n");
	printf("Executing %s\n",myline);
	fputs(myline,pmysql);
	
	pclose(pmysql);
	*/

	printf("NICE ONE, BROTHA!!!!\n");
	return 0;
}
