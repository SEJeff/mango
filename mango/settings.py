# Django settings for mango project.
from django.utils.translation import ugettext_lazy as _
import os
DEBUG = True
TEMPLATE_DEBUG = DEBUG

ADMINS = (
    # ('Your Name', 'your_email@domain.com'),
)

MANAGERS = ADMINS

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME'  : os.path.join(os.path.dirname(__file__), "mango.db")
    },
    # For the mirrors application
    #'gnome_mirrors': {
    #    'ENGINE'    : 'django.db.backends.mysql',
    #    'NAME'      : 'mirrors',
    #    'USER'      : 'dbuser',
    #    'PASSWORD'  : 'dbpass',
    #},
    # For the requests application
    #'account_requests': {
    #    'ENGINE'    : 'django.db.backends.mysql',
    #    'NAME'      : 'mango',
    #    'USER'      : 'dbuser',
    #    'PASSWORD'  : 'dbpass',
    #},
}

DATABASE_ROUTERS = [
    "ldapdb.router.Router",
    "mango.mirrors.routers.MirrorRouter",
    "mango.requests.routers.AccountRequestRouter",
    "mango.members.routers.FoundationMemberRouter",
]

# Local time zone for this installation. Choices can be found here:
# http://en.wikipedia.org/wiki/List_of_tz_zones_by_name
# although not all choices may be available on all operating systems.
# If running in a Windows environment this must be set to the same as your
# system time zone.
TIME_ZONE = 'UTC'

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
MEDIA_URL = '/static_media/'

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
)

SESSION_ENGINE = "django.contrib.sessions.backends.file"

MIDDLEWARE_CLASSES = (
    'django.middleware.common.CommonMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
)

TEMPLATE_CONTEXT_PROCESSORS = (
    'django.contrib.auth.context_processors.auth',
    'django.core.context_processors.i18n',
    'django.core.context_processors.media',
    'django.core.context_processors.request',
)


ROOT_URLCONF = 'mango.urls'

TEMPLATE_DIRS = (
    # Put strings here, like "/home/html/django_templates" or "C:/www/django/templates".
    # Always use forward slashes, even on Windows.
    # Don't forget to use absolute paths, not relative paths.
    os.path.join(os.path.dirname(__file__), "templates"),
)

INSTALLED_APPS = (
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.sites',
    'django.contrib.auth',
    'django.contrib.admin',

    # For natural days in account requests
    'django.contrib.humanize',

    # Actual mango applications
    'users',
    'mirrors',
    'members',
    'requests',

    # For the pretty
    'uni_form',

    # For the omni-search
    'haystack',
)

# For the omni-search box to work
HAYSTACK_SITECONF = 'mango.search_sites'
HAYSTACK_SEARCH_ENGINE = 'whoosh'
HAYSTACK_WHOOSH_PATH = os.path.join(os.path.dirname(__file__), "search_indexes.whoosh")

MANGO_USER_HOMEDIR_BASE = '/home/users'
PROJECT_TITLE = _('GNOME Mango Accounts System')

# local_settings.py can be used to override environment-specific settings
# like database and email that differ between development and production.
PROJECT_ROOT = os.path.abspath(os.path.dirname(__file__))
local_settings = os.path.join(PROJECT_ROOT, 'local_settings.py')
if os.path.isfile(local_settings):
    try:
        execfile(local_settings)
    except:
        pass
