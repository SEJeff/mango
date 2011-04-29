from pprint import pprint
from django.template import RequestContext
from django.shortcuts import render_to_response, get_object_or_404, HttpResponse
from django.contrib.auth.decorators import login_required

from forms import UserForm
from models import LdapUser, LdapGroup

def index(request, template="users/index.html"):
    users = LdapUser.objects.all()
    # TODO: Error handling if the database flips out
    foundation_members = LdapGroup.objects.get(name="foundation").members
    return render_to_response(template, {
        "users": users,
        "current": "users",
        "users_index": True,
        "foundation_members": foundation_members,
        "search_label": "Search Users",
    }, context_instance=RequestContext(request))

def update(request, username, template="users/update-user.html"):
    user = get_object_or_404(LdapUser, username=username)
    if request.method == "POST":
        form = UserForm(request.POST, instance=user)
        if form.is_valid():
            if form.has_changed():
                form.save()
            return HttpResponse("Saved settings for user: %s" % user.full_name)
        else:
            return HttpResponse("ERROR: %s" % form.errors)

    # TODO: Error handling if the database flips out
    groups = LdapGroup.objects.filter(members__contains=username).values_list('name', flat=True)
    form = UserForm(instance=user)

    return render_to_response(template, {
        "form": form,
        "user": user,
        "groups": groups,
        "current": "users",
    }, context_instance=RequestContext(request))
