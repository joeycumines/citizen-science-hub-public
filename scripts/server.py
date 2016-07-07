#!/usr/bin/env python
"""
	This is a simple python 2.7.X script that periodically fetches http://localhost/php/queue_next.php 
	then in the event we have a queue item, and performs operations on the database, to add
	new processed images to the db.
	
	We use string concat which is dumb, but the source is trusted.
	
	Cases:
	- source is a zip
		- we unpack it into the images folder
		- we verify images
		- we add to the database, copying tags for the source zip
		- we perform the image case on them
	- source is a image
		- we check we have the image
		- we extract exif data
		- we create a new image called <source>.processed.png
		- we add these fields into the database
	
	REQUIRES PIL
	"sudo apt-get install python-imaging" on debian systems.
	REQUIRES python 2.7.X
"""

import urllib2
import MySQLdb
import json
import time

from PIL import Image

IMAGE_LOCATION = "/home/debian/citizen_science/images/"
AUTH_KEY = ""

def isZip(filename):
	temp = filename.split(".")
	if len(temp) > 1 :
		temp = temp[len(temp) - 1]
		if (temp.lower() == "zip"):
			return True
	return False

def processImage(source, processed):
	i = Image.open(source)
	pixels = i.load() # this is not a list, nor is it list()'able
	width, height = i.size
	doKill = False
	for x in range(width):
		if doKill:
			break
		for y in range(height):
			if len(pixels[x, y]) < 3:
				print "exiting because we didnt have rgb in image "+source
				doKill = True
				break
			try:
				#is a turple
				#r,g,b = pixels[x, y]
				r = pixels[x, y][0]
				g = pixels[x, y][1]
				b = pixels[x, y][2]
				pixList = []
				if g > 100 and g < 255 and b < 100:
					pixList.append(255)
					pixList.append(255)
					pixList.append(255)
				else:
					pixList.append(0)
					pixList.append(0)
					pixList.append(0)
				#we now need to add any other values
				for num in range(3,len(pixels[x, y])):
					pixList.append(255)
				pixels[x, y] = tuple(pixList)
			except Exception as e:
				print e.__doc__
				print e.message
				print "exiting because didnt change pixel in image "+source
				doKill = True
				break
	i.save(processed)
	return

#we need a main loop
while True:
	try:
		#wait a bit
		time.sleep(0.001)
		#try to fetch the next item in the queue
		response = json.load(urllib2.urlopen('http://localhost/php/queue_next.php?authCode='+AUTH_KEY))
		if response["status"] != 0:
			continue
		sourceId = response['result']
		
		#we sleep for 6 seconds to emulate a time expensive script
		time.sleep(6)
		
		#print sourceId
		
		#now, load the source row from the db
		db = MySQLdb.connect(host="localhost", user="", passwd="", db="citizen_science")
		cur = db.cursor()
		cur.execute("SELECT save_location FROM image_source WHERE source_id = "+sourceId+";")
		sourceRow = 0;
		for row in cur.fetchall():
			sourceRow = row;
			break
		
		#now we have sourceRow
		#print sourceRow
		sourcePath = sourceRow[0]
		
		#we can work directly with the file.
		if (isZip(sourcePath)):
			#we want to extract any images then add as their own source, and work on them.
			print "handling zip not implemented yet"
		else:
			#create a new processed image
			processedPath = "processed."+sourcePath
			processImage(IMAGE_LOCATION+sourcePath, IMAGE_LOCATION+processedPath)
			
			#now we can create a new database entry with our data. This is handled in php.
			#we GET localhost/php/script_process.php?sourceId=&processedPath=&authKey=
			try:
				tempstring = urllib2.urlopen('http://localhost/php/script_process.php?sourceId='+sourceId+'&processedPath='+processedPath+'&authKey='+AUTH_KEY).read()
				response = json.loads(tempstring)
			except Exception as e:
				print "we had an error with "+sourcePath+"!"
				print tempstring
				print e.__doc__
				print e.message
			
			#now, we could handle output/success here if we wanted, but I don't want to.
		
		db.close()
	except Exception as e:
		print "WARNING: GENERAL EXCEPTION THROWN"
		print e.__doc__
		print e.message