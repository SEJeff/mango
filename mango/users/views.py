from django.template import RequestContext
from django.shortcuts import render_to_response, get_object_or_404
from django.contrib.auth.decorators import login_required

from models import LdapUser, LdapGroup
from forms import UserForm, UpdateUserForm

def index(request, template="users/index.html"):
    users = LdapUser.objects.all()
    # TODO: Error handling if the database flips out
    foundation_members = LdapGroup.objects.get(name="foundation").members
    return render_to_response(template, {
        "users": users,
        "foundation_members": foundation_members,
    }, context_instance=RequestContext(request))

def update(request, username, template="users/update-user.html"):
    user = get_object_or_404(LdapUser, username=username)
    # FIXME: REMOVE THIS DEBUG CRAP
    if request.method == "POST":
        form = UserForm(data=request.POST)
        import pdb; pdb.set_trace()

    # TODO: Error handling if the database flips out
    groups = LdapGroup.objects.filter(members__contains=username).values_list('name', flat=True)

    ## Group name to ldap model attribute mapping
    #lookup = {
    #    'sysadmin':    'sysadmin_team',
    #    'membcte':     'membership_committee',
    #    'accounts':    'accounts_team',
    #    'gitadmin':    'gitadmin',
    #    'artweb':      'artweb',
    #    'buildslave':  'buildslave',
    #    'buildmaster': 'buildmaster',
    #    'gnomeweb':    'web_admin',
    #    'bugzilla':    'bugzilla_admin',
    #    'mailusers':   'gnome_email',
    #    'ftpadmin':    'ftp_upload',
    #    'gnomecvs':    'git_account',
    #    'foundation':  'foundation_member',
    #}
    #form_data = {'full_name': user.full_name, 'email': user.email, 'description': user.description}
    #for group in groups:
    #    try:
    #        field = lookup[group]
    #    except KeyError:
    #        continue
    #    form_data[field] = True

    form = UserForm(instance=user)

    return render_to_response(template, {
        "form": form,
        "user": user,
        "groups": groups,
    }, context_instance=RequestContext(request))
