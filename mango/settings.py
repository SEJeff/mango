# Django settings for mango project.

DEBUG = True
TEMPLATE_DEBUG = DEBUG

ADMINS = (
    # ('Your Name', 'your_email@domain.com'),
)

MANAGERS = ADMINS

DATABASE_ENGINE = 'mysql'           # 'postgresql_psycopg2', 'postgresql', 'mysql', 'sqlite3' or 'oracle'.
DATABASE_NAME = 'mango'             # Or path to database file if using sqlite3.
DATABASE_USER = 'root'             # Not used with sqlite3.
DATABASE_PASSWORD = ''         # Not used with sqlite3.
DATABASE_HOST = ''             # Set to empty string for localhost. Not used with sqlite3.
DATABASE_PORT = ''             # Set to empty string for default. Not used with sqlite3.

# Local time zone for this installation. Choices can be found here:
# http://en.wikipedia.org/wiki/List_of_tz_zones_by_name
# although not all choices may be available on all operating systems.
# If running in a Windows environment this must be set to the same as your
# system time zone.
TIME_ZONE = 'UTC'


SITE_ROOT = r'mango/django/'
# Language code for this installation. All choices can be found here:
# http://www.i18nguy.com/unicode/language-identifiers.html
LANGUAGE_CODE = 'en-us'

SITE_ID = 1

# If you set this to False, Django will make some optimizations so as not
# to load the internationalization machinery.
USE_I18N = True

# Absolute path to the directory that holds media.
# Example: "/home/media/media.lawrence.com/"
MEDIA_ROOT = ''

# URL that handles the media served from MEDIA_ROOT. Make sure to use a
# trailing slash if there is a path component (optional in other cases).
# Examples: "http://media.lawrence.com", "http://example.com/media/"
MEDIA_URL = ''

# URL prefix for admin media -- CSS, JavaScript and images. Make sure to use a
# trailing slash.
# Examples: "http://foo.com/media/", "/media/".
ADMIN_MEDIA_PREFIX = '/media/'

# Make this unique, and don't share it with anybody.
SECRET_KEY = 'u(9-ndez*a1wi-438du+!rx$o+7nntz1w$tb0i_3m)+w$jt=sh'

# List of callables that know how to import templates from various sources.
TEMPLATE_LOADERS = (
    'django.template.loaders.filesystem.load_template_source',
    'django.template.loaders.app_directories.load_template_source',
#     'django.template.loaders.eggs.load_template_source',
)

SESSION_ENGINE = "django.contrib.sessions.backends.file"

MIDDLEWARE_CLASSES = (
    'django.middleware.common.CommonMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.middleware.doc.XViewMiddleware',
)

ROOT_URLCONF = 'mango.urls'

TEMPLATE_DIRS = (
    # Put strings here, like "/home/html/django_templates" or "C:/www/django/templates".
    # Always use forward slashes, even on Windows.
    # Don't forget to use absolute paths, not relative paths.
)

INSTALLED_APPS = (
#    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.sites',
)


cfg_opts = set((
    'cached_date',       # Date config last read from disk
    'mode',              # Runtime mode (Live/Preview/Development)
    'base_url',          # Base URL

    'accounts_db_url',   # Mirrors MySQL database URL
    'mirrors_db_url',    # Mirrors MySQL database URL
    'membership_db_url', # Foundation membership MySQL database URL

    'mail_backend',       # Mail backend
    'mail_sendmail_path', # Path to sendmail (sendmail backend)
    'mail_sendmail_args', # Additional options for sendmail (sendmail backend)
    'mail_smtp_host',     # SMTP server hostname (smtp backend)
    'mail_smtp_port',     # SMTP server port (smtp backend)
    'mail_smtp_auth',     # Whether or not to use smtp authentication (smtp backend)
    'mail_smtp_username', # Username to use for SMTP authentication (smtp backend)
    'mail_smtp_password', # Password to use for SMTP authentication (smtp backend)
    'mail_smtp_localhost', # Value to give when sending EHLO or HELO (smtp backend)
    'mail_smtp_timeout',  # SMTP connection timeout
    'mail_smtp_persist',  # Whether or not to use persistent SMTP connections (smtp backend)

    'ldap_url',            # LDAP URL
    'ldap_binddn',         # LDAP bind DN
    'ldap_bindpw',         # LDAP bind PW
    'ldap_basedn',         # LDAP base DN
    'ldap_users_basedn',   # LDAP users base DN
    'ldap_groups_basedn',  # LDAP groups base DN
    'ldap_modules_basedn', # LDAP modules base DN
    'ldap_aliases_basedn', # LDAP aliases base DN

    'token_salt',          # Salt to be used in e-mail tokens

    'support_email',       # Support e-mail
    'account_email',       # Email address of person(s) who handles account management -->

    'session_path'        # Session save path
))

MANGO_CFG = {}

try:
    import xml.etree.cElementTree as et
except ImportError:
    import cElementTree as et

cfg_files = ('/var/www/mango/config.xml', '/etc/mango/config.xml')

import os.path
for f in cfg_files:
    if os.path.exists(f):
        doc = et.parse(file(f, 'r'))
        root = doc.getroot()
        for el in root.getchildren():
            if el.tag in cfg_opts:
                MANGO_CFG[el.tag] = el.text
        
        break

MANGO_CFG['base_url'] = 'http://localhost/mango/django/www'
