from django.conf.urls.defaults import *

from mango import views as view
import mango.settings

urlpatterns = patterns('',
    (r'^%s$' % mango.settings.SITE_ROOT, view.view_index),
    (r'^%stime/$' % mango.settings.SITE_ROOT, view.current_datetime),
    (r'^%slogin/$' % mango.settings.SITE_ROOT, view.handle_login),
    (r'^%slogout/$' % mango.settings.SITE_ROOT, view.handle_logout),
    (r'^%susers/$' % mango.settings.SITE_ROOT, view.list_users),
    (r'^%susers/edit/(?P<user>\w+)/$' % mango.settings.SITE_ROOT, view.edit_user),
    (r'^%srequests/$' % mango.settings.SITE_ROOT, view.list_accounts),
    (r'^%srequests/add/$' % mango.settings.SITE_ROOT, view.add_account),
    (r'^%smirrors/$' % mango.settings.SITE_ROOT, view.list_mirrors),
    (r'^%smirrors/add/$' % mango.settings.SITE_ROOT, view.add_mirror),
    (r'^%smirrors/edit/(?P<pk>\d+)/$' % mango.settings.SITE_ROOT, view.edit_mirror),
    (r'^%smodules/$' % mango.settings.SITE_ROOT, view.list_modules),
    (r'^%smodules/edit/(?P<module>[\w.-]+)/$' % mango.settings.SITE_ROOT, view.edit_module),
    (r'^%sfoundationmembers/$' % mango.settings.SITE_ROOT, view.list_foundationmembers),
    (r'^%sfoundationmembers/add/$' % mango.settings.SITE_ROOT, view.add_foundationmember),
    (r'^%sfoundationmembers/edit/(?P<pk>\d+)/$' % mango.settings.SITE_ROOT, view.edit_foundationmember),
    # Example:
    # (r'^mango/', include('mango.foo.urls')),

    # Uncomment this for admin:
#     (r'^admin/', include('django.contrib.admin.urls')),
)
