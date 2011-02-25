from django.conf.urls.defaults import *

urlpatterns = patterns('',
    url(r'^$', 'mango.users.views.index', name="user-index"),
    url(r'^(?P<username>\w+)$', 'mango.users.views.update', name="user-update"),
)
