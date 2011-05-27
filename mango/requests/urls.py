from django.conf.urls.defaults import *

urlpatterns = patterns('',
    url(r'^$', 'mango.requests.views.index', name="requests-index"),
    #url(r'^(?P<username>\w+)$', 'mango.users.views.update', name="users-update"),
)
