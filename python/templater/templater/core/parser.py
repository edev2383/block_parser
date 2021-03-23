import re
import hashlib
import pprint
from .block import Block
from .includes import Includes
from templater.common.helper import args
# from .block_if import IfBlock
# from .block_foreach import ForeachBlock 

class Parser:
    upstream = []
    file_data = {}
    re_foreach = "@foreach \(.*\)"
    re_if = "@if \(.*\)"
    string_hash: str

    def __init__(self, path):
        self.path = path
        self.filecontents = self._set_filecontents(path)
        if len(self.filecontents) <= 0:
            raise ValueError(f"Empty file provided. Exiting. [{path}]")
        self.first_line = self.filecontents[0]

    def parse(self):
        mainBlock = Block(self.filecontents).parse()
        self._prepend_primeline(self.first_line, mainBlock)
        return Includes(mainBlock).parse()

    def _set_filecontents(self, path):
        with open(path, 'r') as file:
            string_contents = file.read()
        return string_contents.splitlines()
    
    def _prepend_primeline(self, primeline, block):
        if "string" not in block.keys():
            raise ValueError(f"Missing 'string' key {self.path}")
        block["string"] = primeline + "\r\n" + block["string"]