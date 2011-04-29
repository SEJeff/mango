from django.template import RequestContext
from django.http import HttpResponse
from django.core.urlresolvers import reverse
from django.shortcuts import render_to_response, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.utils.translation import ugettext_lazy as _

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
        "add_text": _("Add New Mirror"),
        "add_url": reverse("mirrors-add"),
    }, context_instance=RequestContext(request))

def update(request, mirror_id, name="edit", template="mirrors/update.html"):
    mirror = get_object_or_404(FtpMirror, pk=mirror_id)
    if request.method == "POST":
        form = FtpMirrorForm(request.POST, instance=mirror)
        if form.is_valid():
            if form.has_changed():
                form.save()
            return HttpResponse("Saved settings for mirror: %s" % mirror.name)
        else:
            return HttpResponse("ERROR: %s" % form.errors)

    form = FtpMirrorForm(instance=mirror)

    return render_to_response(template, {
        "form": form,
        "mirror": mirror,
        "current": "mirrors",
    }, context_instance=RequestContext(request))

def add(request, template="mirrors/add.html"):
    form = FtpMirrorForm()
    return render_to_response(template, {
        "form": form,
    }, context_instance=RequestContext(request))
