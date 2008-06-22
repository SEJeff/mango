from django.conf.urls.defaults import *

from mango.views import current_datetime, list_users, list_accounts, edit_user, list_mirrors, edit_mirror, list_foundationmembers, view_index, add_account
import mango.settings

urlpatterns = patterns('',
    (r'^%s$' % mango.settings.SITE_ROOT, view_index),
    (r'^%stime/$' % mango.settings.SITE_ROOT, current_datetime),
    (r'^%susers/$' % mango.settings.SITE_ROOT, list_users),
    (r'^%susers/edit/(?P<user>\w+)/$' % mango.settings.SITE_ROOT, edit_user),
    (r'^%srequests/$' % mango.settings.SITE_ROOT, list_accounts),
    (r'^%srequests/add/$' % mango.settings.SITE_ROOT, add_account),
    (r'^%smirrors/$' % mango.settings.SITE_ROOT, list_mirrors),
    (r'^%smirrors/edit/(?P<pk>\d+)/$' % mango.settings.SITE_ROOT, edit_mirror),
    (r'^%sfoundationmembers/$' % mango.settings.SITE_ROOT, list_foundationmembers),
    # Example:
    # (r'^mango/', include('mango.foo.urls')),

    # Uncomment this for admin:
#     (r'^admin/', include('django.contrib.admin.urls')),
)
