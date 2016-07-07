//Overall
var http_arr = new Array();

function doUpload() {
	document.getElementById('progress-group').innerHTML = ''; //Reset Progress-group
	var files = document.getElementById('myfile').files; 
	for (i=0;i<files.length;i++) {
		uploadFile(files[i], i);
	}
	return false;
}

function uploadFile(file, index) {
	var http = new XMLHttpRequest();
	http_arr.push(http);
	/** Start Process **/
	//Div.Progress-group
	var ProgressGroup = document.getElementById('progress-group');
	//Div.Progress
	var Progress = document.createElement('div');
	Progress.className = 'progress';
	//Div.Progress-bar
	var ProgressBar = document.createElement('div');
	ProgressBar.className = 'progress-bar';
	//Div.Progress-text
	var ProgressText = document.createElement('div');
	ProgressText.className = 'progress-text';	
	//Add Div.Progress-bar and Div.Progress-text into Div.Progress
	Progress.appendChild(ProgressBar);
	Progress.appendChild(ProgressText);
	//Add Div.Progress and Div.Progress-bar into Div.Progress-group	
	ProgressGroup.appendChild(Progress);


	//Speed caculator
	var oldLoaded = 0;
	var oldTime = 0;
	//Process event
	http.upload.addEventListener('progress', function(event) {	
		if (oldTime == 0) { //Set privous time if it was 0.
			oldTime = event.timeStamp;
		}	
		//Start necessary variants
		var fileName = file.name; // file name
		var fileLoaded = event.loaded; //How many percentage of uploading
		var fileTotal = event.total; //Total size of uploading
		var fileProgress = parseInt((fileLoaded/fileTotal)*100) || 0; //Processing
		var speed = speedRate(oldTime, event.timeStamp, oldLoaded, event.loaded);
		//Using variant
		ProgressBar.innerHTML = fileName + ' Uploading...';
		ProgressBar.style.width = fileProgress + '%';
		ProgressText.innerHTML = fileProgress + '% Upload Speed: '+speed+'KB/s';
		//Wait for return value
		if (fileProgress == 100) {
			ProgressBar.style.background = 'url("images/progressbar.gif")';
		}
		oldTime = event.timeStamp; //Set processed time
		oldLoaded = event.loaded; //Set received data
	}, false);
	

	//Starting Upload
	var data = new FormData();
	data.append('filename', file.name);
	data.append('myfile', file);
	http.open('POST', 'upload.php', true);
	http.send(data);


	//Receive retuned value
	http.onreadystatechange = function(event) {
		//Check returned condition
		if (http.readyState == 4 && http.status == 200) {
			ProgressBar.style.background = ''; //Remove processed imaages
			try { //Trap error JSON
				var server = JSON.parse(http.responseText);
				if (server.status) {
					ProgressBar.className += ' progress-bar-success'; //Add class Success
					ProgressBar.innerHTML = server.message; //Warning				
				} else {
					ProgressBar.className += ' progress-bar-danger'; //Add class Danger
					ProgressBar.innerHTML = server.message; //Warning
				}
			} catch (e) {
				ProgressBar.className += ' progress-bar-danger'; //Add class Danger
				ProgressBar.innerHTML = 'There is error'; //Warning
			}
		}
		http.removeEventListener('progress'); //Remove event
	}
}

function cancleUpload() {
	for (i=0;i<http_arr.length;i++) {
		http_arr[i].removeEventListener('progress');
		http_arr[i].abort();
	}
	var ProgressBar = document.getElementsByClassName('progress-bar');
	for (i=0;i<ProgressBar.length;i++) {
		ProgressBar[i].className = 'progress progress-bar progress-bar-danger';
	}	
}


function speedRate(oldTime, newTime, oldLoaded, newLoaded) {
		var timeProcess = newTime - oldTime; //Delay between 2 recalled events
		if (timeProcess != 0) {
			var currentLoadedPerMilisecond = (newLoaded - oldLoaded)/timeProcess; // Amount of transferred byte by 1 Mili second
			return parseInt((currentLoadedPerMilisecond * 1000)/1024); //Return speed at KB/s
		} else {
			return parseInt(newLoaded/1024); //Return speed at KB/s
		}
}