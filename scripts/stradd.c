
#include <global.h>
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
};


my_bool stradd_init(UDF_INIT *initid, UDF_ARGS *args, char *message)
{
	struct stradd_struct *pt;
	
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
	
	pt->sep = (char *)malloc(args->lengths[0]+1);
	memset(pt->sep, 0, args->lengths[0]+1);
	strncpy(pt->sep, args->args[0], args->lengths[0]);
	pt->sep_len = args->lengths[0];
	
	pt->collect = (char *)malloc(65535);
	memset(pt->collect, 0, 65535);
	
	return 0;
}

void stradd_deinit(UDF_INIT *initid)
{
	struct stradd_struct *pt = ((struct stradd_struct *)initid->ptr);
	free(pt->sep);
	free(pt->collect);
	free(initid->ptr);
}

void stradd_reset(UDF_INIT *initid, UDF_ARGS *args,char *is_null, char *error)
{
	struct stradd_struct *pt = ((struct stradd_struct *)initid->ptr);
	memset(pt->collect,0,65535);
	stradd_add(initid,args,is_null, error);
}

void stradd_add(UDF_INIT *initid, UDF_ARGS *args,char *is_null, char *error)
{
	struct stradd_struct *pt = ((struct stradd_struct *)initid->ptr);
	int len = strlen(pt->collect);
	
	
	if (len > 0 && 65535 > (len+pt->sep_len) )
	{
		strncat(pt->collect, pt->sep,pt->sep_len);
		len+=pt->sep_len;
	}
	
	
	if ((len+args->lengths[1]) < 65535)
	{
		strncat(pt->collect, args->args[1], args->lengths[1]);
	}
}

char *stradd(UDF_INIT *initid, UDF_ARGS *args,char *result, unsigned long *length,char *is_null, char *error)
{
	struct stradd_struct *pt = ((struct stradd_struct *)initid->ptr);
	*length = strlen(pt->collect);
	return pt->collect;
}
