from django import forms
from models import LdapUser, LdapGroup
from django.utils.translation import ugettext_lazy as _

# TEST TEST TEST
from django.contrib.auth.models import User

class LdapModelForm(forms.ModelForm):
    """
    Django won't properly serialize ListFields
    """
    def __init__(self, *args, **kwargs):
        if kwargs.get("initial", None):
            kwargs['initial'] = forms.model_to_dict(kwargs['initial'])
        super(LdapModelForm, self).__init__(*args, **kwargs)

class UpdateUserForm(forms.ModelForm):
    full_name = forms.CharField(max_length=50, label=_('Full Name'))
    email = forms.EmailField(label=_('E-mail'))
    description = forms.CharField(label=_('Description'), widget=forms.Textarea(attrs={'rows': 5, 'cols': 40}))

    # Developer Options
    gnomecvs = forms.BooleanField(label=_('Git account'), required=False)
    ftpadmin = forms.BooleanField(label=_('FTP upload'), required=False)

    # Foundation Options
    foundation = forms.BooleanField(label=_('Foundation Member'), required=False)
    mailusers = forms.BooleanField(label=_("has a cool '%s' email alias") % "gnome.org", required=False)

    # Shell access
    bugzilla = forms.BooleanField(label=_('Bugzilla dude/dudess'), required=False)
    gnomeweb = forms.BooleanField(label=_('Web admin'), required=False)
    buildmaster = forms.BooleanField(label=_('Build master account'), required=False)
    buildslave = forms.BooleanField(label=_('Build slave account'), required=False)
    artweb = forms.BooleanField(label=_('Artweb Admin'), required=False)
    gitadmin = forms.BooleanField(label=_('Git admin'), required=False)

    # Mango related
    accounts = forms.BooleanField(label=_('Accounts team dude/dudess'), required=False)
    membcte = forms.BooleanField(label=_('Membership Committee dude/dudess'), required=False)
    sysadmin = forms.BooleanField(label=_('Sysadmin team dude/dudess'), required=False)

    #self.groups = LdapGroup.objects.filter(members__contains=self.instance.username)
    #self.all_groups = LdapGroup.objects.filter(name__in=self.groups)

    #def save(self, *args, **kwargs):
    #    super(UpdateUserForm, self).save(*args, **kwargs)
    #    all_groups = LdapGroup.objects.filter(name__in=self.groups)


    @property
    def groups():
        return ['sysadmin', 'membcte', 'accounts', 'gitadmin', 'artweb',
                'buildslave', 'buildmaster', 'gnomeweb', 'bugzilla',
                'mailusers', 'ftpadmin', 'gnomecvs', 'foundation']

class ModelUserForm(forms.ModelForm):
    class Meta:
        model = User

class UserForm(forms.ModelForm):

    class Meta:
        model = LdapUser
        #exclude = ('keys', 'password', 'home_directory', 'first_name', 'last_name', 'uid', 'gid', 'dn', 'login_shell', 'username')
        #exclude = ('keys', 'password', 'home_directory', 'first_name', 'last_name', 'uid', 'gid', 'login_shell', 'username')
        # TODO: Write a custom widget for ListField entries like keys and use it
        #widgets = {
        #    'description': forms.Textarea(attrs={'rows': 5, 'cols': 50}),
        #}
