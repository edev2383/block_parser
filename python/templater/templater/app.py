from templater.core.parser import Parser
from templater.common.helper import args, validate_filename, write_new_file
import os
import json
import logging

def run():  
    try: 

        logging.basicConfig(
            filename=f"/home/edev/public_html/vendor/Edev/shell/Tools/shell/log_monitor.log",
            format="%(asctime)s %(levelname)-8s %(message)s",
            level=logging.DEBUG,
            datefmt="%Y-%m-%d %H:%M:%S",
        )

        filename = args.filename

        # Validate proper file extension (.html)
        validate_filename(filename)

        target_path = "/home/edev/public_html/view_templates"
        remove_path = "/home/edev/public_html/View"
        
        # get the filenam
        local_file = filename.replace(remove_path, "")
        segs = local_file.split('/')

        filename = segs[-1]
        # need to check every folder on the local path to create and change
        # chmod/chown to dump the json output
        dirs = segs[1:-1]

        
        directory = target_path 
        for dr in dirs:
            directory = directory + "/" + dr  
        if not os.path.exists(directory): 
            logging.debug(f"creating directory: {directory}")
            os.makedirs(directory)

        file_to_parse = remove_path + local_file
        new_filename = target_path + local_file + ".json"
        logging.debug(f"found file, parsing: {file_to_parse}")
        logging.debug(f"new filename: {new_filename}")


        
        json_output = Parser(file_to_parse).parse()

        if args.test is not True:
            write_new_file(json_output, new_filename)

    except ValueError as error:
        print(error)
        logging.debug(f"Exception: {error}")
