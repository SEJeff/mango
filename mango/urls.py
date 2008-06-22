from django.conf.urls.defaults import *

from mango.views import current_datetime, list_users, test_index, list_accounts, edit_user, list_mirrors, edit_mirror
import mango.settings

urlpatterns = patterns('',
    (r'^%stime/$' % mango.settings.SITE_ROOT, current_datetime),
    (r'^%susers/$' % mango.settings.SITE_ROOT, list_users),
    (r'^%stest/$' % mango.settings.SITE_ROOT, test_index),
    (r'^%saccounts/$' % mango.settings.SITE_ROOT, list_accounts),
    (r'^%smirrors/$' % mango.settings.SITE_ROOT, list_mirrors),
    (r'^%smirrors/edit/(?P<pk>\d+)/$' % mango.settings.SITE_ROOT, edit_mirror),
    (r'^%susers/edit/(?P<user>\w+)/$' % mango.settings.SITE_ROOT, edit_user),
    # Example:
    # (r'^mango/', include('mango.foo.urls')),

    # Uncomment this for admin:
#     (r'^admin/', include('django.contrib.admin.urls')),
)
