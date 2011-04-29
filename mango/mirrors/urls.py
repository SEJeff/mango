from django.conf.urls.defaults import *

urlpatterns = patterns('',
    url(r'^$', 'mango.mirrors.views.index', name="mirrors-index"),
    url(r'^(?P<mirror_id>\d+)/(?P<name>.*)/$', 'mango.mirrors.views.update', name="mirrors-update"),
    url(r'^add/$', 'mango.mirrors.views.add', name="mirrors-add"),
)
