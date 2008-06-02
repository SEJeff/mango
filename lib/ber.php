<?

# Straigth port from the BER class from python-paramiko
# Note: ignored the encoding stuff

class BER {

    private
        $idx = 0,
        $content;

    function __construct($data) {
        $this->idx = 0;
        $this->content = $data;
    }

    public function decode() {
        return $this->decode_next();
    }

    public function decode_next() {
        if ($this->idx > strlen($this->content))
            return null;

        $ident = ord($this->content{$this->idx});
        $this->idx += 1;
        print "ident: $ident\n";
        if (($ident & 31) == 31) {
            # identifier > 30
            $ident = 0;
            while ($this->idx < strlen($this->content)) {
                $t = ord($this->content{$this->idx});
                $this->idx += 1;
                $ident = ($ident << 7) | ($t & 0x7f);
                if (!(t & 0x80))
                    break;
            }
        }
        if ($this->idx >= strlen($this->content))
            return null;
        # now fetch length
        $size = ord($this->content{$this->idx});
        $this->idx += 1;
        if ($size & 0x80) {
            # more complimicated...
            # FIXME: theoretically should handle indefinite-length (0x80)
            $t = $size & 0x7f;
            if ($this->idx + t > strlen($this->content))
                return null;
            $size = inflate_long(substr($this->content, $this->idx, $this->idx + t), True);
            $this->idx += t;
        }
        if ($this->idx + $size > strlen($this->content))
            # can't fit
            return null;
        $data = substr($this->content, $this->idx, $this->idx + $size);
        $this->idx += $size;
        # now switch on id
        if ($ident == 0x30)
            # sequence
            return BER::decode_sequence($data);
        elseif ($ident == 2) {
            # int
            return inflate_long($data);
        }
        else {
            # 1: boolean (00 false, otherwise true)
            throw new Exception("Unknown ber encoding type $ident (robey is lazy) idx $this->idx");
        }

    }

    static function decode_sequence($data) {
        $out = array();
        $b = new BER($data);
        while (true) {
            $x = $b.decode_next();
            if (is_null($x))
                break;
            $out[] = $x;
        }
        return $out;
    }
}

?>
