from django.conf.urls.defaults import *

urlpatterns = patterns('',
    url(r'^$', 'mango.members.views.index', name="members-index"),
    #url(r'^(?P<username>\w+)$', 'mango.members.views.update', name="members-update"),
    #url(r'^add/$', 'mango.members.views.add', name="members-add"),
)
