#if set to 1, no acl checking is performed
acl.no_check = 0
acl.use_server = 0
acl.compare = 0

site_id = {VAR:site_id}

groups.all_users_grp = 2

archive.depth = 2
archive.use = 1


site_basedir = {VAR:basedir}

auth.md5_passwords = 1


baseurl = {VAR:baseurl}

tpldir = ${site_basedir}/templates

db.user = {VAR:db_user}
db.pass = {VAR:db_pass}
db.host = {VAR:db_host}
db.base = {VAR:db_base}

cache.page_cache = ${site_basedir}/pagecache

rootmenu = 6

#menuedit.menu_defs[9] = VASAK

admin_rootmenu2 = 4

documents.lead_splitter = <br>
document.no_lead_splitter = <br>

document.keyword_relations = 1

stitle = Autom@web

frontpage = 6

menuedit.template_sets[templates] = default

menuedit.no_fp_doc = 1

amenustart = 5

menuedit.num_menu_images = 10

sitemap.rootmenu = 6

document.link_authors = 0
document.link_authors_section = 0

per_oid = 6
lang_menus = 0

menuedit.no_fp_doc = 1

crypt_urls = 1
