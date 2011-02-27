from django.conf.urls.defaults import *

urlpatterns = patterns('',
    url(r'^$', 'mango.mirrors.views.index', name="mirrors-index"),
    #url(r'^.*/(?P<id>\d+)$', 'mango.mirrors.views.update', name="mirrors-update"),
)
