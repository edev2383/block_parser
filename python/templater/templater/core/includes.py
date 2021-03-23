import re
import random

class Includes:

    re_include = r"\@include\((.*?)\)"

    def __init__(self, block):
        self.block = block
        self.string_list = block["string"].split("\r\n")
        self.block["includes"] = []

    def parse(self):
        ii = 0
        while ii < len(self.string_list):
            line = self.string_list[ii]
            is_include = self._is_include(line)
            if is_include is not None:
                hashstr = str(random.getrandbits(128))
                self.string_list[ii] = "{" + hashstr + "}"
                self.block["includes"].append(
                    {
                        "hash": hashstr,
                        "key": is_include.group(1)
                    }
                )
            ii = ii + 1
        self.block["string"] = "\r\n".join(self.string_list)
        return self.block
    
    def _is_include(self, line):
        return re.search(self.re_include, line)

