from pprint import pprint
from django.template import RequestContext
from django.shortcuts import render_to_response, get_object_or_404, HttpResponse
from django.contrib.auth.decorators import login_required
from django.utils.translation import ugettext_lazy as _

#from forms import UserForm
from forms import UserFormFactory
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
    UserForm = UserFormFactory()
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

def add(request, template="users/add.html"):
    UserForm = UserFormFactory(hide_username=False)
    form = UserForm()
    if request.method == "POST":
        form = UserForm(request.POST)
        if form.is_valid():
            if form.has_changed():
                form.save()
            return HttpResponse(_("Saved settings for: %s") % mirror.username)
        else:
            return HttpResponse(_("ERROR: %s") % form.errors)

    return render_to_response(template, {
        "form": form,
        "current": "users",
    }, context_instance=RequestContext(request))
