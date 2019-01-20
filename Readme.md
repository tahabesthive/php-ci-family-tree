Docker environment
==================================

  * Start containers in the background: `docker-compose up -d`
  * Start containers on the foreground: `docker-compose up`. You will see a stream of logs for every container running.
  * Stop containers: `docker-compose stop`
  * Kill containers: `docker-compose kill`
  * View container logs: `docker-compose logs`

  Docker will deploy initial required database structure such as organization types and REST Server API Keys.

# Application #

Application is developed over PHP Codeigniter MVC - REST Server, you can access endpoints with API-KEY in headers or you can turn off from config/rest.php settings.

Following file has been used to complete the task : 

* Controller : application/controller/api/v1/Organization.php
* Model : application/model/Organization_model.php
* Helper : application/helpers/MY_array_helper.php
* Libraries : ['application/libraries/REST_Controller.php', 'application/libraries/Format.php']
* Language : application/language/english/rest_controller_lang.php

note : there was no instruction given relate to data deleting or unlinking state, if earlier any daughter records was added its remain link and it will display in search result if any new relation submit call requested to that tree.


# Database #

You can review initial database structure at : /phpdocker/data/init.sql


# Endpoints #

* URL [POST] - http://localhost/api/v1/organization/search/

	Headers : X-API-KEY : kks8w4k080g4w4os40wckkg4c4cc0koo0ossc08o

	Body : raw 

	application/json : {
	"term" : "Black Banana",
	"limit" : 100,
	"sort" : "asc"
}

	Rules : 
	* term : trim|required|min_length[3]
	* limit : trim|numeric|less_than[101]
	* start : trim|numeric
	* sort : trim|in_list['asc','desc']


* URL [POST] - http://localhost/api/v1/organization/add/

	Headers : X-API-KEY : kks8w4k080g4w4os40wckkg4c4cc0koo0ossc08o

	Body : raw 

	application/json : {"org_name":"Paradise Island","daughters":[{"org_name":"Banana tree","daughters":[{"org_name":"Yellow Banana"},{"org_name":"Brown Banana"},{"org_name":"Black Banana"}]},{"org_name":"Big banana tree","daughters":[{"org_name":"Yellow Banana"},{"org_name":"Brown Banana"},{"org_name":"Green Banana"},{"org_name":"Black Banana","daughters":[{"org_name":"Phoneutria Spider"}]}]}]}

	Rules : First organization must have daughter tree, later organization name can be submit without tree, given pattern is sticky.

*  Example : 
application/json:{"org_name":"Paradise Island","daughters":[{"org_name":"Banana tree","daughters":[{"org_name":"Yellow Banana"}]},{"org_name":"Big banana tree","daughters":[{"org_name":"Brown Banana"},{"org_name":"Black Banana","daughters":[{"org_name":"Phoneutria Spider"}]}]}]}

# logs #

All endpoints footprints is logged in database with table name logs.