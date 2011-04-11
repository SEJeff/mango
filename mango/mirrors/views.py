from django.template import RequestContext
from django.shortcuts import render_to_response, get_object_or_404
from django.contrib.auth.decorators import login_required

from forms import FtpmirrorForm
from models import Ftpmirror, Webmirror
from pprint import pprint

def index(request, template="mirrors/index.html"):
    #mirrors = itertools.chain(Ftpmirror.objects.order_by("-active", "name"), Webmirror.objects.order_by("-active", "name"))
    mirrors = Ftpmirror.objects.order_by("-active")
    # TODO: Error handling if the database flips out
    return render_to_response(template, {
        "mirrors": mirrors,
        "current": "mirrors",

        # For the datatables jquery plugin
        #"disable_sorting": True,
        "search_label": "Search Mirrors",
    }, context_instance=RequestContext(request))
