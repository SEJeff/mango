import os
from django.conf.urls.defaults import *

from django.contrib import admin
admin.autodiscover()

from django.conf import settings

urlpatterns = patterns('',
    #(r'^%s$' % mango.settings.SITE_ROOT, view.view_index),
    #(r'^%stime/$' % mango.settings.SITE_ROOT, view.current_datetime),
    #(r'^%slogin/$' % mango.settings.SITE_ROOT, view.handle_login),
    #(r'^%slogout/$' % mango.settings.SITE_ROOT, view.handle_logout),
    #(r'^%susers/$' % mango.settings.SITE_ROOT, view.list_users),
    #(r'^%susers/edit/(?P<user>\w+)/$' % mango.settings.SITE_ROOT, view.edit_user),
    #(r'^%srequests/$' % mango.settings.SITE_ROOT, view.list_accounts),
    #(r'^%srequests/add/$' % mango.settings.SITE_ROOT, view.add_account),
    #(r'^%smodules/$' % mango.settings.SITE_ROOT, view.list_modules),
    #(r'^%smodules/edit/(?P<module>[\w.-]+)/$' % mango.settings.SITE_ROOT, view.edit_module),
    #(r'^%sfoundationmembers/$' % mango.settings.SITE_ROOT, view.list_foundationmembers),
    #(r'^%sfoundationmembers/add/$' % mango.settings.SITE_ROOT, view.add_foundationmember),
    #(r'^%sfoundationmembers/edit/(?P<pk>\d+)/$' % mango.settings.SITE_ROOT, view.edit_foundationmember),

    url(r'^$',   'mango.views.index', name="index"),

    (r'^admin/',    include(admin.site.urls)),
    (r'^search/',   include('haystack.urls')),
    (r'^users/',    include('mango.users.urls')),
    (r'^mirrors/',  include('mango.mirrors.urls')),
    (r'^requests/', include('mango.requests.urls')),
)

# For convenience, let django serve media for developers
if settings.DEBUG or getfqdn().startswith("local"):
    urlpatterns += patterns('',
        url(r'^static_media/(?P<path>.*)', 'django.views.static.serve', {
          'document_root': os.path.join(os.path.dirname(__file__), "static_media"),
          'show_indexes': True,
        }),
        url(r'^css/(?P<path>.*)', 'django.views.static.serve', {
          'document_root': os.path.join(os.path.dirname(__file__), "static_media", "css"),
          'show_indexes': True,
        }),
        url(r'^js/(?P<path>.*)', 'django.views.static.serve', {
          'document_root': os.path.join(os.path.dirname(__file__), "static_media", "js"),
          'show_indexes': True,
        }),
        url(r'^img/(?P<path>.*)', 'django.views.static.serve', {
          'document_root': os.path.join(os.path.dirname(__file__), "static_media", "img"),
          'show_indexes': True,
        }),
    )
