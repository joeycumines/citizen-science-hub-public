# citizen-science-hub

This is team Mongoose's project "Citizen Science Hub", for Dr Dunbabin of QUT.

##Rationale
The reason given for this project, was to facilitate 'citizen science' initiatives, using image recognition and geo-tagging technology.
By identifying the contents of the picture, taken at a given date, and location, scientists can gain valuable data and insight into
various fields of research.

The primary objective was use in identifying and graph the locations of 'crown of thorns' starfish, to aid in research into the
deteriorating health of the great barrier reef.

The intent was to process images with Dr Dunbabin's own machine learning algorithm, however due to the nature of a commercial agreement,
a place holder, "green" identifying python script, has been used.

##Architecture
The server runs a LAMP stack, and the bulk of the code is done in php. Images are stored in a single folder, owned by the PHP process,
on the server itself. On upload and verification, images are moved there for further processing , references (relative) stored in the db.
From there, a python script that runs in a single thread (one image at a time) polls the localhost and GETs the next one out of a
prioritized queue. Upon processing completion, the the processed image relative path is then GET back to the localhost.
EXIF extraction is handled in PHP. Other metadata such as location data may be retrieved in JavaScript on upload.

##Git Structure
- mysql : contains database structure dumps, for use with a mysql server
	- Dump20160603.sql : the latest, up to date dump
- scripts
	- server.py : the python processing script that runs independently to the web server, on the same machine
- www : the web content, for use on a LAMP stack
	- about : About us info page
	- admin
		- delete.php : Script used to delete images, only works for a logged in admin.
		- index.php : Admin page, view, delete/pardon flagged images, ordered in number of flags (desc) then time (asc).
		- pardon.php : Script used to delete all flags for a given image. Only works for a logged in admin.
	- browse
		- image
			- processed
				- index.php : Path to processed image, GET with id (processed_id)
			- source
				- index.php : Path to source image, GET with id (source_id)
			- delete.php : Script used to delete a image, only works for owners of the image.
			- flag.php : Script used to flag a image with a reason, only works for logged in users.
			- index.php : Individual image view page, delete and flag images, and share on social media.
		- imageTable.php : Script used to get a table of the logged in user's images.
		- index.php : Displays all the images (and the processing status) uploaded by the user.
	- contact : Contact us page
	- css : folder contains various style sheets, inc bootstrap
	- dev : dev folder for test pages
	- fonts
	- images : image resources for the website
	- js : JavaScript, including jQuery and bootstrap.
	- manage
		- password_reset : reset your password
		- signin : sign in page
		- signout : sign out page, optionally redirects
		- signup : sign up page
		- upload : upload page
		- user : user account management page
		- feed.php : returns a JSON array, containing details about the last 15 images uploaded
		- index.php : landing page, and displays a upload feed for all images
	- node_modules : contains a library for social media sharing, installed using npm
	- php : PHP helper methods. Very well documented in code, see individual files
	- tutorial : Tutorial page
	- vendor : Composer was used to install the PHP swift mailer plugin. Facebook's PHP API was also installed, but not used
	- composer.json
	- composer.lock
	- index.php : The website landing page, with a brief mission statement
- php.ini : the php.ini used on the development server (set up for custom max upload size, etc)

For further information, joeycumines@gmail.com can be contacted, as the lead developer of Team Mongoose.