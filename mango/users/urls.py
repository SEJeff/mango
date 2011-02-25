from django.conf.urls.defaults import *

urlpatterns = patterns('',
    url(r'^$', 'mango.users.views.index', name="users-index"),
    url(r'^(?P<username>\w+)$', 'mango.users.views.update', name="users-update"),
)
