
#include <my_global.h>
#include <my_sys.h>
#include <mysql.h>
#include <m_ctype.h>

char *stradd(UDF_INIT *initid, UDF_ARGS *args,char *result, unsigned long *length,char *is_null, char *error);
my_bool stradd_init(UDF_INIT *initid, UDF_ARGS *args, char *message);
void stradd_deinit(UDF_INIT *initid);
void stradd_reset(UDF_INIT *initid, UDF_ARGS *args,char *is_null, char *error);
void stradd_add(UDF_INIT *initid, UDF_ARGS *args,char *is_null, char *error);


struct stradd_struct
{
	char *sep;
	int sep_len;
	char *collect;
	int collect_len;
};

//#define DBG 1

my_bool stradd_init(UDF_INIT *initid, UDF_ARGS *args, char *message)
{
	struct stradd_struct *pt;
#ifdef DBG
	FILE *fp;
#endif

	if (args->arg_count != 2)
	{
		strcpy(message,"stradd() requires two arguments");
		return 1;
	}


	args->arg_type[0] = STRING_RESULT;
	args->arg_type[1] = STRING_RESULT;

	initid->maybe_null = 0;
	initid->max_length = 65535;
	
	initid->ptr = (char *)malloc(sizeof(struct stradd_struct));
	pt = (struct stradd_struct *)initid->ptr;
	
#ifdef DBG
	fp = fopen("/tmp/stradd.log","a");
	fprintf(fp, "init inited pt to %i \n",pt);
	fclose(fp);
#endif
	
	pt->collect = (char *)malloc(65535);
	memset(pt->collect, 0, 65535);
	pt->collect_len = 0;

	pt->sep = (char *)malloc(args->lengths[0]);
	pt->sep_len = args->lengths[0];
	memcpy(pt->sep, args->args[0], pt->sep_len);
	return 0;
}

void stradd_deinit(UDF_INIT *initid)
{
	struct stradd_struct *pt = ((struct stradd_struct *)initid->ptr);
#ifdef DBG
	FILE *fp;
	fp = fopen("/tmp/stradd.log","a");
	fprintf(fp, "deinit() \n");
	fclose(fp);
#endif 
	free(pt->collect);
	free(pt->sep);
	free(initid->ptr);
}

void stradd_reset(UDF_INIT *initid, UDF_ARGS *args,char *is_null, char *error)
{
	struct stradd_struct *pt = ((struct stradd_struct *)initid->ptr);
#ifdef DBG
	FILE *fp;

	fp = fopen("/tmp/stradd.log","a");
	fprintf(fp, "stradd_reset() \n");
#endif
	memset(pt->collect,0,65535);
	pt->collect_len = 0;
#ifdef DBG
	fprintf(fp, "exit stradd_reset()\n");	
	fclose(fp);
#endif	
	stradd_add(initid,args,is_null, error);
}

void stradd_add(UDF_INIT *initid, UDF_ARGS *args,char *is_null, char *error)
{
	struct stradd_struct *pt = ((struct stradd_struct *)initid->ptr);
#ifdef DBG
	FILE *fp;
	fp = fopen("/tmp/stradd.log","a");
	fprintf(fp, "enter stradd_add() argc = %i arg1len = %i , collectlen = %i, collect = '%s' collectrptr = %i pt = %i \n",
		args->arg_count,
		args->lengths[1],
		pt->collect_len,
		pt->collect,
		pt->collect,
		pt);
	fprintf(fp, "arg0 = %s(%x) arg0len = %i arg1 = %s (%x) arg1len = %i\n",
		args->args[0],
		args->args[0],
		args->lengths[0],
		args->args[1],
		args->args[1],
		args->lengths[1]
	);

	fflush(fp);

	if (args->arg_type[1] != STRING_RESULT)
	{
		fprintf(fp, "ERROR, arg1 is not string!\n");
	}
#endif
	
	if (pt->collect_len > 0 && args->lengths[1] > 0 && args->args[1] != NULL)
	{
		memcpy(pt->collect+pt->collect_len, pt->sep, pt->sep_len);
		pt->collect_len+=pt->sep_len;
	}

	if (((pt->collect_len + args->lengths[1]) < 65000) && args->lengths[1] > 0 && args->args[1] != NULL)
	{
		memcpy(pt->collect+pt->collect_len, args->args[1], args->lengths[1]);
		pt->collect_len += args->lengths[1];
	}

#ifdef DBG
	else
	{
		if (args->lengths[1] < 1)
		{
			fprintf(fp, "arglen 0 \n");
		}
		else
		{
			fprintf(fp, "too long totlen = %i \n",(pt->collect_len+args->lengths[1])); 
		}
	}
	fprintf(fp, "stradd_add exit , collect = %s , length = %i \n", pt->collect, pt->collect_len);
	fclose(fp);
#endif
}

char *stradd(UDF_INIT *initid, UDF_ARGS *args,char *result, unsigned long *length,char *is_null, char *error)
{
	struct stradd_struct *pt = ((struct stradd_struct *)initid->ptr);
	char *res;
#ifdef DBG
	FILE *fp;
	fp = fopen("/tmp/stradd.log","a");
	fprintf(fp, "stradd() , collect = %s len = %i \n", pt->collect,pt->collect_len);
	fclose(fp);
#endif
	*length = pt->collect_len;
	res = (char *)malloc(pt->collect_len);
	memcpy(res, pt->collect, pt->collect_len);
	return res;
}
