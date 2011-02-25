from django.forms import ModelForm

class AccountsForm(ModelForm):
    class Meta:
        model = AccountRequest

class AccountsFormAdd(ModelForm):
    group = MultipleChoiceField()
    vouch_dev = ChoiceField()
    vouch_i18n = ChoiceField()

    class Meta:
        model = AccountRequest
