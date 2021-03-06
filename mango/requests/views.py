from pprint import pprint
from django.template import RequestContext
from django.shortcuts import render_to_response, get_object_or_404, HttpResponse, HttpResponseRedirect
from django.contrib.auth.decorators import login_required
from django.utils.translation import ugettext_lazy as _
from django.template.defaultfilters import slugify
from django.core.urlresolvers import reverse

from forms import AccountRequestForm
from models import AccountRequest, REQUEST_VERDICTS

def index(request, template="requests/index.html"):
    # TODO: Error handling if the database flips out
    requests = AccountRequest.objects.all()
    choices  = [verdict[1] for verdict in REQUEST_VERDICTS]
    choices.append(_("all account requests"))
    choices.sort()

    return render_to_response(template, {
        "requests_index"    : True,
        "choices"           : choices,
        "requests"          : requests,
        "current"           : "requests",
        "search_label"      : _("Account Requests"),
    }, context_instance=RequestContext(request))

def update(request, pk, slug=None, template="requests/update-request.html"):
    # TODO: Error handling if the database flips out
    acct_request = get_object_or_404(AccountRequest, pk=pk)
    username  = acct_request.uid
    full_name = acct_request.cn

    if request.method == "POST":
        form = AccountRequestForm(request.POST, instance=acct_request)
        if form.is_valid():
            changed = form.changed_data
            delete =  "confirm" in changed
            if delete:
                form.instance.delete()
                return HttpResponse("Deleted Account Request for %s" % full_name)
            return HttpResponseRedirect(reverse("requests-update", args=(pk, slugify(full_name))))
        else:
            # TODO: This is pretty lame, fix it
            return HttpResponse("ERROR: %s" % form.errors)

    form = AccountRequestForm(instance=acct_request)

    return render_to_response(template, {
        "form": form,
        "acct_request": acct_request,
        "current": "requests",
    }, context_instance=RequestContext(request))
