class AccountRequestRouter(object):
    """
    A very simple database router to route all queries for the 'requests'
    app to  the  account_requests  database  for  all  pending  accounts.
    """

    def db_for_read(self, model, **hints):
        if model._meta.app_label == 'requests':
            return 'account_requests'
        return None

    def db_for_write(self, model, **hints):
        if model._meta.app_label == 'requests':
            return 'account_requests'
        return None

    def allow_relation(self, obj1, obj2, **hints):
        "Allow any relation if a model in 'requests' is involved"
        if obj1._meta.app_label == 'requests' or obj2._meta.app_label == 'requests':
            return True
        return None

    def allow_syncdb(self, db, model):
        "Make sure the 'requests' app only appears on the 'account_requests' db"
        if db == 'account_requests':
            return model._meta.app_label == 'requests'
        elif model._meta.app_label == 'requests':
            return False
        return None
