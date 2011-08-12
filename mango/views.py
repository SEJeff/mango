from django.conf import settings
from django.contrib.auth import logout
from django.template import RequestContext
from django.core.urlresolvers import reverse
from django.http import HttpResponseRedirect, HttpResponse
from django.shortcuts import render_to_response
from django.contrib.admin.models import User
from mango.users.models import LdapUser

def index(request):
    title = settings.PROJECT_TITLE
    username = request.user
    user = LdapUser.objects.get(username=username)
    return render_to_response("index.html", {
        'user'       : user,
        'main_index' : True,
        'title'      : title,
    }, context_instance=RequestContext(request))

def logout_view(request):
    """Quietly logout and redirect to /"""
    logout(request)
    url = reverse("index")
    return HttpResponseRedirect(url)

def server_error(request, template="500.html"):
    """
    Custom handler500 for sending errors to sentry and a user-friendly
    error string for users to use when reporting some sort  of  error.
    """
    from django.template import Context, loader
    from django.http import HttpResponseServerError
    import logging
    import sys
    try:
        context = request
    except Exception, e:
        logging.error(e, exc_info=sys.exc_info(), extra={'request': request})
        context = {}

    context['request'] = request
    context['MEDIA_URL'] = settings.MEDIA_URL

    t = loader.get_template(template)
    return HttpResponseServerError(t.render(Context(context)))
