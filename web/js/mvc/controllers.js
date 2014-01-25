app.controller('user', ['$scope', 'user', function($scope, user){
	$scope.user = user;
	$scope.log = function(){
	}
}]);

app.controller('navigation', ['$scope', function($scope){
	$scope.show_navigation = false;

	$scope.switch = function(){
		$scope.show_navigation = ! $scope.show_navigation;
	}
}])

app.controller('home', ['$scope', '$http', 'user', function($scope, $http, user){
	$scope.recommendations = [];

	$scope.user = user;

	$http.get('/api/recom').success(function(data){
		$scope.recommendations = data;
	})

	$scope.scores = [];
	$scope.current_score = '';
	$scope.score_lookBehind = 30;
	$scope.score_loaded = false;

	$scope.refreshChart = function(lookBehind){
		$http.get('/api/scoreboard?amount='+lookBehind).success(function(data){
			$scope.scores = data;
			$scope.current_score = data[0]['me'];
			$scope.score_loaded = true;
		})			
	}

	$scope.refreshChart($scope.score_lookBehind);

	$scope.$watch('score_lookBehind', function(newVal){
		$scope.refreshChart(newVal);
	})

}])

app.controller('exercise_list', ['$scope', '$http', '$routeParams', function($scope, $http, $routeParams){
	console.log($routeParams);
	$scope.exercise_list_loaded = false;
	$scope.exercises = [];
	$scope.filter = {
		muscle_part: 'all'
	}

	$scope.sort='days_since_last_exercise';

	$scope.muscle_parts  =[
		{
			id: 'all',
			name: 'any'
		}
	];

	$scope.filterExercises = function(exercise){
		var muscle_part = $scope.filter.muscle_part;
		if(muscle_part=='all'){
			return true;
		}else{
			if(muscle_part==exercise.muscle_part_id){
				return true;
			}
		}
		return false;
	}

	$scope.getMuscleName = function(){
		for(var i in $scope.muscle_parts){
			if($scope.muscle_parts[i].id==$scope.filter.muscle_part){
				return $scope.muscle_parts[i].name;
			}
		}
	}

	$scope.$watch('filter.muscle_part', function(newVal){

	})

	$http.get('/api/exercises').success(function(data){
		$scope.exercises = data;
		for(var i in data){
			var muscle_part = {
				id: data[i].muscle_part_id,
				name: data[i].type_name
			}
			var found = false;
			for(var j in $scope.muscle_parts){
				if($scope.muscle_parts[j].id==muscle_part.id){
					found = true;
				}
			}
			if(!found){
				$scope.muscle_parts.push(muscle_part);
			}
			data[i].days_since_last_exercise = parseInt(data[i].days_since_last_exercise);
		}
		if($routeParams.muscle_type_name!=undefined && $routeParams.muscle_type_name!='all'){
			for(var i in $scope.muscle_parts){
				if($scope.muscle_parts[i].name==$routeParams.muscle_type_name){
					$scope.filter.muscle_part = $scope.muscle_parts[i].id;
				}
			}
		}
		$scope.exercise_list_loaded = true;
	});
}]);		


app.controller('new_exercise', ['$scope', '$http', function($scope, $http){

	$scope.exercise_list_loaded;
	$scope.muscle_parts = [];

	$scope.exercise_data = {
		name: "",
		muscle_part_name: "",
		muscle_part_name_custom: "",
		use_custom_muscle:false,
		template: {
			type: 'traditional',
			params:{
				traditional:{
					set_amount: 5
				},
				outside:{
					distance: true,
					avg_speed: true,
					calories: false,
					max_speed: false
				},
				fancy:{
					rows:[
						{
							name: "",
							unit: ""
					}
					]
				}
			}
		}
	}

	$scope.status = {};

	$scope.add_fancy_row = function(){
		$scope.exercise_data.template.params.fancy.rows.push({
			name:"",
			unit:""
		})
	}

	$scope.remove_fancy_row = function(index){
		$scope.exercise_data.template.params.fancy.rows.splice(index, 1);
	}

	$scope.validateTemplate = function(){
		var data = $scope.exercise_data;
		var template = data.template;
		switch(template.type){
			case "traditional":
				if(parseInt(template.params.traditional.set_amount)>0){
					return {
						status: "ok"
					}
				}else{
					return {
						status: "error"
					}
				}
				break;
			case "outside":
				var correct = true;
				for(var i in template.params.outside){
					if(typeof template.params.outside[i] != "boolean"){
						correct = false;
					}
				}
				if(correct){
					return {
						status: "ok"
					}
				}else{
					return {
						status: "error"
					}
				}
				break;
			case "fancy":
				var rows = template.params.fancy.rows;
				var correct = true;
				for(var i in rows){
					if(rows[i].name.length==0 || rows[i].unit.length==0){
						correct = false;
					}
				}
				if(correct){
					return {
						status: "ok"
					}
				}else{
					return {
						status: "error"
					}
				}
				break;
		}
	}

	$scope.validateName = function(name){
		var status='ok';
		var message="ok!";
		if(name==""){
			return {};
		}
		if(name.length<4 && name.length>0){
			status = 'error';
			message= 'too short!'
		}
		for(var i in $scope.exercises){
			if($scope.exercises[i].name.toLowerCase()==name.toLowerCase()){
				status='error'
				message='already exists!';
			}
		}
		return {
			status: status, 
			message:message
		}
	}

	$scope.getStatus = function(){
		var name_ok = $scope.validateName($scope.exercise_data.name).status!='error' && $scope.exercise_data.name.length>4;
		var type_ok = $scope.exercise_data.muscle_part_name!="" || ($scope.exercise_data.use_custom_muscle && $scope.exercise_data.muscle_part_name_custom!="");
		var template_ok = $scope.validateTemplate().status!='error';
		return {
			name_ok: name_ok,
			type_ok: type_ok,
			template_ok: template_ok
		}
	}

	$scope.statusOK = function(){
		var status = $scope.getStatus();
		for(var i in status){
			if(status[i]==false){
				return false;
			}
		}
		return true;
	}

	$http.get('/api/exercises').success(function(data){
		$scope.exercises = data;
		for(var i in data){
			var muscle_part = {
				id: data[i].muscle_part_id,
				name: data[i].type_name
			}
			var found = false;
			for(var j in $scope.muscle_parts){
				if($scope.muscle_parts[j].id==muscle_part.id){
					found = true;
				}
			}
			if(!found){
				$scope.muscle_parts.push(muscle_part);
			}
		}
		$scope.exercise_list_loaded = true;
	});

	$scope.create = function(){
		alert("todo!");
	}

}]);

app.controller('exercise', ['$scope', '$http', '$routeParams', function($scope, $http, $routeParams){
	$scope.data_loaded = false;

	$scope.log_loaded = false;

	$scope.round = Math.round;

	$http.get('/api/exercises/' + $routeParams.id).success(function(data){
		$scope.data_loaded = true;
		$scope.exercise = data;
	})

	$http.get('/api/exercises/'+$routeParams.id + "/log?count=10").success(function(data){
		$scope.log_loaded = true;
		$scope.log = data;
	})
}])

app.controller('exercise_go', ["$scope", "$http", "$routeParams", "music_player", function($scope, $http, $routeParams, music_player){
	$scope.exercise_loaded = false;

	$scope.exercise;

	$scope.template;

	$scope.current_set = 1;

	$http.get('/api/exercises/' + $routeParams.id).success(function(data){
		$scope.exercise = data;
		$scope.exercise_loaded = true;
		$scope.template = data.setTemplates;
		var c =0;
		for(var i in $scope.template){
			c++;
		}
		$scope.template.length = c;
	})
}]);