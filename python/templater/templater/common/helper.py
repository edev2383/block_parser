import re
import json
import argparse


parser = argparse.ArgumentParser()
parser.add_argument("--filename", "-f", help="Supply the filename")
parser.add_argument("--test", "-t", help="Run the system under test")

args = parser.parse_args()

def meta_if(line):
    re_if = r"\((.*?)\)"
    return break_match(re.search(re_if, line).group(1))
    
def break_match(matchgroup):
    re_compound = r"([a-zA-Z0-9]+)\s?([=!<>]{1,2})\s?([\'\"a-zA-Z0-9]+)"
    compoundgroup = re.match(re_compound, matchgroup)
    if compoundgroup is not None:
        return break_compound(compoundgroup)
    else:
        return break_simple(matchgroup)

def break_compound(compoundgroup):
    return {
        "key": compoundgroup.group(1),
        "operator": compoundgroup.group(2), 
        "value": clean_value(compoundgroup.group(3)),
        "value_type": get_value_type(compoundgroup.group(3))
    }

def get_value_type(value):
    if value.lower() == "true" or value.lower() == "false":
        return "boolean"
    if re.match(r"^[0-9]*$", value):
        return "number"
    return "string"

def clean_value(value):
    return value.strip("\"").strip("\'")

def break_simple(simplegroup):
    grp = {
        "key": simplegroup,
        "operator": "==",
        "value_type": "boolean",
    }
    if re.match(r"^\!+", simplegroup):
        grp["value"] = "false"
    else:
        grp["value"] = "true"
    return grp

def meta_foreach(line):
    re_compound = r"\((.*?) in (.*?)\)"
    compoundgroup = re.search(re_compound, line)
    if compoundgroup is not None:
        return break_compound_foreach(compoundgroup)
    else:
        return break_simple_foreach(line)

def break_compound_foreach(matchgroup):
    return {
        "key": matchgroup.group(2),
        "item": matchgroup.group(1),
        "compound": True
    }

def break_simple_foreach(line):
    re_fe = r"\((.*?)\)"
    match = re.search(re_fe, line)
    return { 
        "key": match.group(1),
        "item": None,
        "compound": False,
        }

def meta_inlineif(line):
    re_inlineif = r"\@\((.*?)\?((.*?)(\:(.*?))?)\)"
    match = self.search()

def validate_filename(filename):
    if filename is None:
        raise ValueError(f"Missing filename - [ {filename} ]")
    if filename[-5:] != ".html":
        raise ValueError(f"Incorrect file type provided - [ {filename} ]")

def write_new_file(data_to_write, new_filename):
    with open(new_filename, 'w+') as outfile:
        json.dump(data_to_write, outfile, indent=4, sort_keys=True)