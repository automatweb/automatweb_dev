/*
 * AW install script
 * path to configuration file should probably come from command line
 * $Header: /home/cvs/automatweb_dev/scripts/install/Attic/install.c,v 1.4 2002/06/10 15:51:01 kristo Exp $
 */

/* 1 - get data from users (logroot, folder, ServerName, admin_folder, type)
 * 2 - get vhost conf file from user
 * 3 - copy the vhost conf to place
 * 4 - create log dir based on user input
 * 5 - create site dir based on user input
 * 6 - copy files to site directory
 * 7 - return - aw has to do the rest
 */
#include <stdio.h>
#include <string.h>
#include <errno.h>
// for mkdir
#include <sys/stat.h>
#include <sys/types.h>
#include <fcntl.h>
#include <unistd.h>
#include <syslog.h>

main(int argc, char **argv)
{
	// that's where we store the vhost conf we receive from caller
	char vhost_conf[4097];
	char line[200],basedir[200],vhost_loc[200],logroot[200],ServerName[200],admin_folder[200],type[200],remote_host[200],cmdline[200],mysql_host[200],mysql_user[200],mysql_pass[200],mysql_client[200],dbname[200],dbuser[200],dbpass[200];
	char errmsg[200];
	char logmsg[200];
	FILE *pmysql; // pipe to mysql
	// we get the whole apache configuration block from caller (AW)
	// it contains an unknown number of lines so we use those tokens
	// to mark the begin and end of the config block
	const char vhost_start_token[] = "##vhost-start##";
	const char vhost_end_token[] = "##vhost-end##";
	const int keys_needed = 14;
	int is_conf,keys = 0;

	char *ckey,*cval;

	FILE *tmp;

	extern int errno;

	strcpy(vhost_conf,"");

	while( fgets(line,sizeof(line)-1,stdin) != NULL)
	{
		if ( strncmp(line,vhost_start_token,sizeof(vhost_start_token)-1) == 0)
		{
			is_conf = 1;
		}
		else if ( strncmp(line,vhost_end_token,sizeof(vhost_end_token)-1) == 0)
		{
			is_conf = 0;
		}
		else
		{
			if (is_conf == 1)
			{
				strcat(vhost_conf,line);
			}
			else
			{
				// snarf the key
				ckey = strtok(line,"=");
				// and now the value up to the end of line
				cval = strtok(NULL,"\n");
				if (strcmp(ckey,"basedir") == 0)
				{
					strncpy(basedir,cval,sizeof(basedir));
					keys++;
				}
				else if (strcmp(ckey,"logroot") == 0)
				{
					strncpy(logroot,cval,sizeof(logroot));
					keys++;
				}
				else if (strcmp(ckey,"ServerName") == 0)
				{
					strncpy(ServerName,cval,sizeof(ServerName));
					keys++;
				}
				else if (strcmp(ckey,"admin_folder") == 0)
				{
					strncpy(admin_folder,cval,sizeof(admin_folder));
					keys++;
				}
				else if (strcmp(ckey,"vhost_loc") == 0)
				{
					strncpy(vhost_loc,cval,sizeof(vhost_loc));
					keys++;
				}
				else if (strcmp(ckey,"type") == 0)
				{
					strncpy(type,cval,sizeof(type));
					keys++;
				}
				else if (strcmp(ckey,"remote_host") == 0)
				{
					strncpy(remote_host,cval,sizeof(remote_host));
					keys++;
				}
				else if (strcmp(ckey,"mysql_host") == 0)
				{
					strncpy(mysql_host,cval,sizeof(mysql_host));
					keys++;
				}
				else if (strcmp(ckey,"mysql_user") == 0)
				{
					strncpy(mysql_user,cval,sizeof(mysql_user));
					keys++;
				}
				else if (strcmp(ckey,"mysql_pass") == 0)
				{
					strncpy(mysql_pass,cval,sizeof(mysql_pass));
					keys++;
				}
				else if (strcmp(ckey,"mysql_client") == 0)
				{
					strncpy(mysql_client,cval,sizeof(mysql_client));
					keys++;
				}
				else if (strcmp(ckey,"dbname") == 0)
				{
					strncpy(dbname,cval,sizeof(dbname));
					keys++;
				}
				else if (strcmp(ckey,"dbuser") == 0)
				{
					strncpy(dbuser,cval,sizeof(dbuser));
					keys++;
				}
				else if (strcmp(ckey,"dbpass") == 0)
				{
					strncpy(dbpass,cval,sizeof(dbpass));
					keys++;
				}
			};
		};
	};
	openlog("awinst",0,LOG_USER);
	sprintf(logmsg,"AW installer received a request from %s to initialize site %s",remote_host,ServerName);
	syslog(LOG_USER | LOG_NOTICE,logmsg);
	// write config file
	// now just for testing we write all the data out to tmp file
	if ( (tmp = fopen(vhost_loc,"w")) == NULL)
	{
		strcpy(errmsg,"tried to open /tmp/awinst.tmp for writing, but ");
		strncat(errmsg,strerror(errno),sizeof(errmsg)-strlen(errmsg));
		syslog(LOG_USER | LOG_NOTICE,errmsg);
		return 1;
	};

	if ( (strlen(vhost_conf) == 0) || (keys != keys_needed) )
	{
		printf("didn't get everything I need, dropping out\n");
		return 1;
	};

	fputs(vhost_conf,tmp);
	fclose(tmp);
	
	// create dir for log files
	mkdir(logroot,0777);

	// create basedir for teh site
	mkdir(basedir,0777);

	// copy files
	sprintf(cmdline,"cd %s; tar xpzf /www/automatweb_dev/install/%s.tar.gz",basedir,type);
	system(cmdline);

	printf("finishing %d",keys);

	// get the information from the client
	// tegelikult võiks see vajalik kataloogistrukuur tulla ju tar failist?
	// oh yes.
	//
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

	sprintf(cmdline,"CREATE DATABASE %s;\n",dbname);
	fputs(cmdline,pmysql);

	sprintf(cmdline,"GRANT ALL PRIVILEGES ON %s.* TO %s@%s IDENTIFIED BY '%s';\n",dbname,dbuser,mysql_client,dbpass);
	fputs(cmdline,pmysql);
		
	pclose(pmysql);
	
	sprintf(cmdline,"mysql -h %s -u %s --password=%s %s< /tmp/dump.tmp",mysql_host,dbuser,dbpass,dbname);
	printf("Importing database dump\n");
	printf("Executing %s\n",cmdline);
	system(cmdline);
	unlink("/tmp/dump.tmp");
	printf("Import done\n");

	// create symlink
	//
	// copy neccessary files
	//
	// create log dir
	
	closelog;
	return 0;

	printf("NICE ONE, BROTHA!!!!\n");
	return 0;
}

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
	// fix it. should figure out under what user apache is running as and set the owner of
	// the file to that uid
	strncpy(cmdline,"chmod 666 ",sizeof(cmdline)-1);
	strcat(cmdline,path);
	strcat(cmdline,"/aw.ini");
	system(cmdline);
}
