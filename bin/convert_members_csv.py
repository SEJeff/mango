#!/usr/bin/python

def escape(value):
	newvalue = ''
	for ch in value[0:(len(value))]:
		if ch == '\'':
			newvalue = newvalue + '\\'
		newvalue = newvalue + ch

	return newvalue

csvin = open('members.csv', 'r')
sqlout = open('members.sql', 'w')

linenum = 0
while(1):
	line = csvin.readline()
	if(len(line) < 1):
		break
	linenum = linenum + 1
	tokens = line.split(';')
	if(len(tokens) != 4):
		raise Error('Wrong number of tokens on line ' + linenum)
	firstname = escape(tokens[0])
	lastname = escape(tokens[1])
	email = tokens[2]
	first_added_yy = tokens[3][0:2]
	first_added_mm = tokens[3][3:5]	
	first_added_dd = tokens[3][6:8]
	if(int(first_added_yy) > 90):
		first_added_yy = "19" + first_added_yy
	else:
		first_added_yy = "20" + first_added_yy
	first_added = first_added_yy + "-" + first_added_mm + "-" + first_added_dd
	print >> sqlout, "INSERT INTO foundationmembers (firstname, lastname, email, first_added) VALUES ('" + firstname + "', '" + lastname + "', '" + email + "', '" + first_added + "');"

csvin.close()
sqlout.close()
