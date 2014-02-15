app.service('user', ['btrResource', function(resource){
	var userResource = new resource("/api/users/me");
	var user = userResource.get(function(){
		//console.log(user);
	});
	//console.log(user);
	return user;
}]); 


app.service('metronome', function(){
	var sound = new buzz.sound('/web/sounds/metronome', {
		formats:['mp3'],
		preload:true,
		loop: true
	})

	this.play = function(){
		sound.play();
	}

	this.pause = function(){
		sound.pause();
	}
})

app.service('notifSound', function(){
	var sound = new buzz.sound('/web/sounds/notif', {
		formats:['mp3'],
		preload:true,
		loop: true
	})

	this.play = function(){
		sound.play();
	}

	this.pause = function(){
		sound.pause();
	}
})

app.service('music_player', function(){
	
	var playing = false;

	var track = new buzz.sound('/web/sounds/getInShapeLoop', {
	  formats: ['mp3'],
	  preload: true,
	  loop:true
	})

	window.test_h = track;

	window.test_s = this;

	this.reset = function(){
		track.setPercent(0);
	}

	this.fadeOut = function(duration){
		track.fadeOut(duration);
		playing = false;
	}

	this.play = function(){
		if(!playing){
			playing = true;
			track.fadeIn(100);
			track.play();
			console.log('play');
		}
		//alert('play!');
	}

	this.pause = function(){
		if(playing){
			track.pause();
			playing = false;
			console.log('pause');			
		}
	}

	this.setVolume = function(volume){
		track.setVolume(volume)
	}

})