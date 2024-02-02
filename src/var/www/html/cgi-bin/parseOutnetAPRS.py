#!/usr/bin/env python3
import aprslib
import sys
from datetime import datetime
from time import localtime, strftime, time, ctime

filepath = sys.argv[1]
#json_message = '{"records":['
json_message = ''
with open(filepath, 'r') as fp:
	for cnt, line in enumerate(fp):
		#if cnt > 0:
		#	json_message += ','
		try:
			strTime = filepath.replace('messages-', '').replace('.txt', '')
			strYear = strTime[0:4]
			strMonth = strTime[5:7]
			strDay = strTime[8:10]
			strHour = strTime[11:13]
			strMinute = strTime[14:]
			x = aprslib.parse(line)
			json_message += '{'
			strFullTime = strYear + '.' + strMonth + '.' + strDay + ' ' + strHour + ':' + strMinute
			try:
				t = datetime.strptime(strFullTime, "%Y.%m.%d %H:%M")
				json_message = json_message + '"time":"' + t.strftime("%a %b %d %H:%M:%S %Y") + '"'
			except:
				t = time()	
				json_message = json_message + '"time":"' + str(ctime(t)) + '"'
			try:
				json_message += ',"from":"' + str(x["from"]) + '"'
			except:
				json_message += ',"from":"error"'
			try:
				json_message += ',"to":"' + str(x["to"]) + '"'
			except:
				json_message += ',"to":"error"'
			try:
				json_message += ',"longitude":"' + str(x["longitude"]) + '"'
			except:
				json_message += ',"longitude":"error"'
			try:
				json_message += ',"latitude":"' + str(x["latitude"]) + '"'
			except:
				json_message += ',"latitude":"error"'
			try:
				json_message += ',"comment":"' + str(x["comment"]).replace("\"", "\\\"") + '"'
			except:
				json_message += ',"comment":"error"'
			try:
				json_message += ',"via":"' + str(x["via"]) + '"'
			except:
				json_message += ',"via":"error"'
			try:
				json_message += ',"symbol":"' + str(x["symbol"]) + '"'
			except:
				json_message += ',"symbol":"error"'
			try:
				json_message += ',"raw":"' + str(x["raw"]).replace("\\", "\\\\").replace("\"", "\\\"") + '"'
			except:
				json_message += ',"raw":"error"'
			try:
				json_message += ',"path":"' + str(x["path"]) + '"'
			except:
				json_message += ',"path":"error"'
			json_message += ',"source":"OUTNET"'
			json_message = json_message + '}'
			json_message = json_message + ','
		except:
			log_message = 'An exception occurred'
		#json_message = json_message + '}'

print(json_message)
