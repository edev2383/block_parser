import re
from ..common.helper import meta_if, meta_foreach
import hashlib
import random

class Block:
    """Needs to output
    hash
    type
    key
    length - to modulate the parser ii value
    """
    re_start_if = r"\@(if)\s?\((.*?)\)"
    re_start_foreach = r"\@(foreach)\s?\((.*?)\)"
    re_start_inlineif = r"\@\((.*?)\?((.*?)(\:(.*?))?)\)"

    def __init__(self, downstream: list):
        self.output = {}
        self.output["blocks"] = {}
        self.output["hash"] = str(random.getrandbits(128))
        self.downstream = downstream 
        self.upstream = []
        self._get_meta()
        
    def _get_meta(self):
        self._set_type()
        self._set_key_data()

    def _set_type(self):
        """ Sets the current Block type based on the found value in the
        0 indexed line of the given downstream list
        """
        self.output["type"] = self.get_type(self.downstream[0])

    def _set_key_data(self):
        """
        Set the key meta data. If Blocks are more complex and are 
        handled with the library helper function meta_if()
        foreach blocks can be associative or direct loops
        foreach (array) using keys as the template in the string
        OR foreach (item in array) where {item} is the printed value
        """
        if self.output["type"] == "if":
            meta = meta_if(self.downstream[0])
            self.output["key"] = meta["key"]
            self.output["operator"] = meta["operator"]
            self.output["value"] = meta["value"]
            self.output["value_type"] = meta["value_type"]
        elif self.output["type"] == "foreach":
            meta = meta_foreach(self.downstream[0])
            self.output["key"] = meta["key"]
            self.output["item"] = meta["item"]
            self.output["compound"] = meta["compound"]
        elif self.output["type"] = "inlineif":
            
        else: 
            return None

    def parse(self):
        """
        """
        ii = 1
        lenofup = len(self.downstream)
        # loop through the contents of the downstream list, since the
        # 0 index line is the actuator that initialized the current 
        # Block(), we set the workable index to 1 to start
        while ii < len(self.downstream):
            # get current line and check if there is a "Block" present
            line = self.downstream[ii]
            found_block = self.get_type(line)
            if found_block is not None:
                # If we found a block, create a new Block object, with
                # the current "downstream" as the argument, run parse
                newBlock = Block(self.downstream[ii:]).parse()
                # set the newBlock object as an entry into the blocks
                # dict, with the key being the hash of the newBlock
                self.output["blocks"][newBlock["hash"]] = newBlock
                # adjust the ii index value by adding the length of the
                # newBlock output
                ii = ii + int(newBlock["length"])
                # append the newBlock hash value as a template value, ie
                # curly brackets, so the PHP formatter can find it when
                # rendering the output
                self.upstream.append("{"+newBlock["hash"]+"}")
            else: 
                # if the current line is NOT a found_block, we check if
                # we're at the end of the current block
                if self.is_end(line):
                    ii = ii + 1
                    self.output["length"] = ii
                    self._format_output_string(self.upstream)
                    return self.output
                # if we're not at the end of the current block, we  just
                # append the line and iterate the index
                else:
                    ii = ii + 1
                    self.upstream.append(line)
        # once out of the while loop, return the main Block 
        self.output["length"] = ii
        self._format_output_string(self.upstream)
        return self.output


    def _format_output_string(self, upstream_list):
        """
        """
        if self.output["type"] == "if":
            return self._format_if_ouput_string(upstream_list)
        # in addition to the if and foreach output, we need to output 
        # the standard file contents. Since if is more specialized, we 
        # can treat foreach and None types the same way, by simply
        # outputing to 'string'
        else:
            self.output["string"] = "\r\n".join(upstream_list)

    def _format_if_ouput_string(self, upstream_list):
        # ! there is a better way to do this. 
        # join the list to regex for @else, then split list at @else
        # if found
        joined_list = "\r\n".join(upstream_list)
        if re.search(r"\@else", joined_list):
            # split list forms two possible outputs based on value
            ifelse_string = joined_list.split('@else')
            self.output["string_if_true"] = ifelse_string[0]
            self.output["string_if_false"] = ifelse_string[1]
        else:
            # if @else not found, set to true, false is empty string
            self.output["string_if_true"] = joined_list
            self.output["string_if_false"] = ""

    def get_type(self, line):
        """ Define the current block type. In theory, the only None type
        should be the main, outer block, while each found block will 
        have a set type of 'if' or 'foreach'
        """
        if re.search(self.re_start_foreach, line):
            return "foreach"
        elif re.search(self.re_start_if, line):
            return "if"
        elif re.search(self.re_start_inlineif, line):
            return "inlineif"
        else:
            return None
       
    def is_end(self, line):
        """
        Check the given line for a valid end tag, based on the current
        Block type. We check against type because we are dealing with
        potentially nested statements
        """
        if self.output["type"] == "if":
            return re.search(r"\@endif", line)
        else:
            return re.search(r"\@endforeach", line)

