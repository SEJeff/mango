from django.template import RequestContext
from django.shortcuts import render_to_response, get_object_or_404
from django.contrib.auth.decorators import login_required

from models import Ftpmirror, Webmirror
#from forms import UserForm, UpdateUserForm

def index(request, template="mirrors/index.html"):
    ftp_mirrors = Ftpmirrors.objects.all()
    # TODO: Error handling if the database flips out
    foundation_members = LdapGroup.objects.get(name="foundation").members
    return render_to_response(template, {
        "users": users,
    }, context_instance=RequestContext(request))

#def update(request, username, template="users/update-user.html"):
#    user = get_object_or_404(LdapUser, username=username)
#    # FIXME: REMOVE THIS DEBUG CRAP
#    if request.method == "POST":
#        form = UserForm(data=request.POST)
#        import pdb; pdb.set_trace()
#
#    # TODO: Error handling if the database flips out
#    groups = LdapGroup.objects.filter(members__contains=username).values_list('name', flat=True)
#
#    form = UserForm(instance=user)
#
#    return render_to_response(template, {
#        "form": form,
#        "user": user,
#        "groups": groups,
#    }, context_instance=RequestContext(request))
