from django.template import RequestContext
from django.shortcuts import render_to_response, get_object_or_404
from django.contrib.auth.decorators import login_required

from forms import FtpMirrorForm
from models import FtpMirror, Webmirror

def index(request, template="mirrors/index.html"):
    mirrors = FtpMirror.objects.order_by("-active")
    # TODO: Error handling if the database flips out
    return render_to_response(template, {
        "mirrors": mirrors,
        "current": "mirrors",
        "mirrors_index": True,

        # For the datatables jquery plugin
        "search_label": "Search Mirrors",
    }, context_instance=RequestContext(request))
