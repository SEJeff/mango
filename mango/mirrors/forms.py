from models import Ftpmirrors
from django.forms import ModelForm

class FtpmirrorsForm(ModelForm):
    class Meta:
        model = Ftpmirrors
