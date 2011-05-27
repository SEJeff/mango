from django import forms
from django.contrib import admin
from django.utils.translation import ugettext_lazy as _

from models import AccountRequest
from mango.users.forms import SSHKeyWidget, MultipleChoiceAnyField

from uni_form.helpers import FormHelper, Submit, Reset

RO_ATTRS = {'readonly': 'readonly', 'title': 'This element is read only'}

class AccountRequestForm(forms.ModelForm):
    # django-uni-form stuff to make the pretty
    helper = FormHelper()

    submit = Submit('submit', _('Delete Request'))
    # Add the pretty rounded button style
    submit.field_classes += " action_button"
    helper.add_input(submit)

    uid =     forms.CharField(label=_('Username'), widget=forms.TextInput(attrs=RO_ATTRS))
    cn =      forms.CharField(label=_('Name'), widget=forms.TextInput(attrs=RO_ATTRS))
    mail =    forms.CharField(label=_('Email'),  widget=forms.TextInput(attrs=RO_ATTRS))
    comment = forms.CharField(label=_('Comment'), widget=forms.Textarea(attrs=RO_ATTRS))

    confirm = forms.BooleanField(label=_('Confirm Request Deletion'), required=False)
    authorizationkeys = MultipleChoiceAnyField(required=False, widget=SSHKeyWidget(attrs=RO_ATTRS))

    def clean_authorizationkeys(self):
        data = self.cleaned_data.get("authorizationkeys", "")
        return data

    def clean(self):
        cleaned_data = self.cleaned_data
        cleaned_data["authorizationkeys"] = "THIS IS NULL AND WILL NOT BE USED"
        return cleaned_data

    class Meta:
        model = AccountRequest
