app.service('user', ['btrResource', function(resource){
	var userResource = new resource("/api/users/me");
	var user = userResource.get(function(){
		//console.log(user);
	});
	//console.log(user);
	return user;
}]); 


app.service('music_player', function(){
	
})