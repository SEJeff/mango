from pprint import pprint
from django.template import RequestContext
from django.shortcuts import render_to_response, get_object_or_404, HttpResponse
from django.contrib.auth.decorators import login_required
from django.utils.translation import ugettext_lazy as _

#from forms import UserForm
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
