<html>
	<head>
		<meta name="viewport" content="width=device-width, user-scalable=no">
		<title>iKleks Coach</title>

		<script src='/web/js/libs/angular.min.js'></script>
		<link href='http://fonts.googleapis.com/css?family=Roboto:300,400,700,700italic,400italic&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		<link href='/web/css/main.css' rel='stylesheet'>
	</head>
	<body ng-app='app'>
		<div id='top_bar_wrapper' class='main_background noisy' ng-controller='user'>
			<div id='top_bar'>
				<div id='top_bar_navigation' ng-controller='navigation'>
					<div id='top_bar_current_location' ng-click='switch()'>
						<span>
							home
						</span>
						<span id='top_bar_downArrow'>
							&#9660;
						</span>
					</div>
					<div id='top_bar_navigationOptions' ng-show='show_navigation'>
						<div goto='/home' class='navigation_option active'>
							home
						</div>
						<div goto='/exercises' class='navigation_option'>
							exercises
						</div>
						<div class='navigation_option'>
						option3
						</div>
						<div class='navigation_option'>
						option4
						</div>
						<div class='navigation_option'>
						option5
						</div>
					</div>
				</div>
				<div id='top_bar_userInfo'>
					<div id='top_bar_photo' style="background-image:url({{user.photo_url}})"></div>
					<span id='top_bar_username'>
						{{user.name}} {{user.surname}}
					</span>
				</div>
			</div>
		</div>
		<div ng-view class='main_view'>

		</div>
	</body>
	<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
	<script src='/web/js/libs/angular-resource.js'></script>
	<script src='/web/js/libs/angular-route.js'></script>
	<script src='/web/js/libs/angular-sanitize.js'></script>
	<script src='/web/js/libs/angular-animate.js'></script>
	<script src='/web/js/libs/better_resource.js'></script>
	<script src='/web/js/libs/buzz.min.js'></script>
	<script src='/web/js/mvc/main_module.js'></script>
	<script src='/web/js/mvc/services.js'></script>
	<script src='/web/js/mvc/controllers.js'></script>
	<script src='/web/js/mvc/directives.js'></script>
</html>