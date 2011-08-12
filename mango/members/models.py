from django.db import models

class ElectionAnonTokens(models.Model):
    anon_token = models.CharField(max_length=600)
    election_id = models.IntegerField()
    class Meta:
        db_table = u'election_anon_tokens'

class ElectionChoices(models.Model):
    election_id = models.IntegerField()
    choice = models.CharField(max_length=450)
    class Meta:
        db_table = u'election_choices'

class ElectionResults(models.Model):
    election_id = models.IntegerField(primary_key=True)
    result = models.TextField(blank=True)
    class Meta:
        db_table = u'election_results'

class ElectionTmpTokens(models.Model):
    election_id = models.IntegerField()
    member_id = models.IntegerField()
    tmp_token = models.CharField(max_length=600)
    class Meta:
        db_table = u'election_tmp_tokens'

class ElectionVotes(models.Model):
    choice_id = models.IntegerField()
    anon_id = models.IntegerField()
    preference = models.IntegerField()
    class Meta:
        db_table = u'election_votes'

class Elections(models.Model):
    type = models.CharField(max_length=30)
    name = models.CharField(max_length=450)
    voting_start = models.DateTimeField(null=True, blank=True)
    voting_end = models.DateTimeField(null=True, blank=True)
    choices_nb = models.IntegerField()
    question = models.TextField()
    class Meta:
        db_table = u'elections'

class Electorate(models.Model):
    firstname = models.CharField(max_length=150, blank=True)
    lastname = models.CharField(max_length=150, blank=True)
    email = models.CharField(max_length=300, blank=True)
    class Meta:
        db_table = u'electorate'

class FoundationMember(models.Model):
    firstname = models.CharField(max_length=150, blank=True)
    lastname = models.CharField(max_length=150, blank=True)
    email = models.CharField(max_length=300, blank=True)
    comments = models.TextField(blank=True)
    first_added = models.DateField()
    last_renewed_on = models.DateField(null=True, blank=True)
    last_update = models.DateTimeField()
    resigned_on = models.DateField(null=True, blank=True)
    userid = models.CharField(max_length=45, blank=True)
    class Meta:
        db_table = u'foundationmembers'

