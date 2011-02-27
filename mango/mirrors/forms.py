from models import Ftpmirror
from django.forms import ModelForm

class FtpmirrorForm(ModelForm):
    class Meta:
        model = Ftpmirror
