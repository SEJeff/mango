from django.template import RequestContext
from django.shortcuts import render_to_response, get_object_or_404, HttpResponse
from django.contrib.auth.decorators import login_required
from django.utils.translation import ugettext_lazy as _

#from forms import UserForm
#from forms import UserFormFactory
#from models import LdapUser, LdapGroup

def index(request, template="members/index.html"):
    return render_to_response(template, {
        "current": "members",
        "members_index": True,
    }, context_instance=RequestContext(request))
