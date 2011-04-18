#!/usr/bin/python
import base64

try:
    import hashlib
    md5_constructor = hashlib.md5
except ImportError:
    import md5
    md5_constructor = md5.md5

class SshKey(object):
    """
    Object oriented wrapper around dealing with an openssh key

    Python port of: http://git.gnome.org/mango/tree/lib/util.php#n6
    """

    def __init__(self, key):
        self.key = key
        self.index = 0
        self.data_length = len(self.data)
        self.length = 0
        self.hash = ''

        if len(key.split()) != 3:
            raise ValueError("Key needs to have 3 arguments: ssh-rsa keydata.here user@email_or_comment")

    def validate_key(self):
        """
        Make sure we've got a valid ssh key
        """
        key = self.key

        # Empty key or just garbage
        if (not key) or (not key.startswith("ssh-")):
            return False

        # TODO: Check for empty key
        # Not a dsa or rsa key
        try:
            keytype = self.keytype
        except ValueError:
            return False

        # Data should be a base64 encoded string
        certificate = base64.decodestring(self.data)

        # Already decoded data
        if certificate == self.data:
            return False

        # When the encoded key type isn't the same as the unencoded type
        key_type = self.get_string()
        if key_type != self.format:
            return False

        try:
            if key_type == "RSA":
                e = msg.get_string()
                n = msg.get_string()
                self.length = self._bit_length(n)
            else:
                p = msg.get_string()
                q = msg.get_string()
                g = msg.get_string()
                y = msg.get_string()
                self.length = self._bit_length(p)
        except:
            return False

        return True

    def _bit_length(self, data):
        hbyte = ord(data[0])
        bitlen = len(data) * 8
        check = 0x80
        while (check and not (hbyte & check)):
            check >>= 1
            bitlen -= 1
        return bitlen

    def _get_bytes(self, num):
        if ((self.index + num) > self.data_length):
            raise ValueError("Not enough bytes available in SSH message")
        self.index += num
        return self.data[self.index:self.index + num]

    def get_int(self):
       print "DEBUG: self._get_bytes(4)='%s'" % self._get_bytes(4)

       arr =  "%ul" % int(self._get_bytes(4))
       return arr[0]

    def get_string(self):
        return self._get_bytes(self.get_int())

    def _chunk_split(self, string, length, end="\r\n"):
        """
        Mimic of php's chunk_split()
        """
        ret = ""
        for i in range(0, len(string), length):
            ret += string[i:min(i+length, len(string))] + end
        return ret

    def __unicode__(self):
        return u"%s %s %s" % (self.keytype, self.fingerprint, self.comment)

    def __str__(self):
        return self.__unicode__().encode('ascii')

    def __repr__(self):
        return "<%s %s %s>" % (self.keytype, self.fingerprint, self.comment)

    @property
    def fingerprint(self):
        certificate = base64.decodestring(self.data);
        digest = md5_constructor(certificate).hexdigest()
        output = self._chunk_split(digest, 2, ':').rstrip(':')
        return output

    @property
    def keytype(self):
        if self.format == "ssh-dss":
            return "DSA"
        elif self.format == "ssh-rsa":
            return "RSA"
        else:
            raise ValueError("Can't determine keytype")

    @property
    def format(self):
        return self.key.split()[0]

    @property
    def data(self):
        return self.key.split()[1]

    @property
    def comment(self):
        return self.key.split()[2]
