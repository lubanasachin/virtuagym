//main.js javascript library

var woMain = {},
	API = 'http://meetonsnap.com/virtuagym/api/index.php/',
	exerciseArr = [],
	addExerciseObj = {},
	updateStatus = 0;

(function() {

	/** init workout method : set default layout */
	this.init = function() {
		exerciseArr = [];
		addExerciseObj = {};
		updateStatus = 0;

		/** show hide elements to set default view */
		$('.planNameMsg, #modifyWorkoutPlan').hide();
		$('#addNewPlanBtn, #listWorkoutPlan').show();
		$('#planNameTxt').val('');

		/** add new plan button click event */
		$('#addNewPlanBtn').off('click').on('click',function(event) {
			$('#addNewPlanBtn, #listWorkoutPlan').hide();
			$('#modifyWorkoutPlan, #cancelBtn').show();
		});
		
		/** cancel button click event */
		$('#cancelBtn').hide().off('click').on('click',function(event) {
			$('#modifyWorkoutPlan, #cancelBtn').hide();
			$('#listWorkoutPlan, #addNewPlanBtn').show();
			addExerciseObj = {};
			updateStatus = 0;
			woMain.createExerciseList();
			$('.planNameMsg').hide();
			$('.planNameDiv').removeClass("has-danger");
			$('#planNameTxt').val('');
		});

		/** day option on change */
		$('.daysOpt').off('change').on('change',function(event) {
			woMain.createExerciseList();
		});

		/** save plan details button click */
		$('#savePlanBtn').off('click').on('click',function(event) {
			woMain.validateWorkoutPlan();
		});

		/** get list of exercises */
		woMain.execAPI({'method': 'GET', 'api': 'exercises', 'action': 'readExerciseList'});
		/** get list of plans created */
		woMain.execAPI({'method': 'GET', 'api': 'workout', 'action': 'readWorkoutPlans'});

	};

	/*** XHR functions for workout plan ****/
	this.execAPI = function(params) {
		var jqxhr;
		var apiUrl = API+params.api;
		if(params.method === 'POST' || params.method === 'PUT') {
			jqxhr = $.ajax({
				url: apiUrl,
				type: params.method,
				data: JSON.stringify(params.data),
				contentType: "application/json"
			});
		} if(params.method === 'GET') {
			jqxhr = $.get(apiUrl,{data:params});
		}	
	    jqxhr.done(function(data) { woMain.successCallback(data,params.action); });
	    jqxhr.fail(function() { console.log('AJAX failed'); });
	};

	/** On Ajax success,callback function based on actions passed */
	this.successCallback = function(response,requestOf) {
	    var stats = response.type;
	    if(stats == 'success') {
	        switch(requestOf) {
	            case 'readWorkoutPlans':
	            	woMain.createListWorkoutPlan(response);
	            	break;
	            case 'readExerciseList':
	            	exerciseArr = response.exerciseList;
	            	woMain.createExerciseList();	            	
	            	break;
	            case 'addNewPlan':
	            	woMain.init();
	            	break;
	            case 'getPlanDetails':
	            	woMain.createModifyView(response);
	            	break;
	        }
	    } else if(stats == 'failed') {
	    	console.log('ERR: '+response.descr);
    	}
	};

	/** create list of exercises present */
	this.createExerciseList = function() {	
		$("#exerciseList").html("");		
		$.each(exerciseArr,function(key,val) {
			$(document.createElement("div")).attr({"class": "circleDiv","id":"circleDiv_"+val.id}).text(val.exercise_name).appendTo("#exerciseList").on('click',function(event){
				woMain.setExerciseList(val,1);
			});
			woMain.setExerciseList(val,0);
		});		
	};

	/** set list of exercises selected| not selected */
	this.setExerciseList = function(exData,isClicked) {
		var selDay = $('input[name=daysOpt]:checked').val();
		var elem = $("#circleDiv_"+exData.id);
		if(addExerciseObj[selDay] === undefined) addExerciseObj[selDay] = [];

		if(isClicked === 1) {
			if(addExerciseObj[selDay].indexOf(exData.id) != -1) {
				elem.removeClass("selected");
				addExerciseObj[selDay].splice(addExerciseObj[selDay].indexOf(exData.id),1);
			} else {			
				elem.addClass("selected");
				addExerciseObj[selDay].push(exData.id);				
			}
		} else {
			elem.removeClass("selected");
			if(addExerciseObj[selDay].indexOf(exData.id) != -1) {
				elem.addClass("selected");
			} else elem.removeClass("selected");		
		}
	};

	/** dynamically create HTML markups for list of plan created */
	this.createListWorkoutPlan = function(response) {
		var plansList = response.planList;
		$('#listWorkoutPlan').html('').show();
		if(Object.keys(plansList).length <= 0) {
			var msg = "Impossible isn't a fact. It's an opinion. Impossible isn't a declaration. It's a dare. Impossible is potential. Impossible is nothing";
            $(document.createElement("div")).attr({"class":"row", "id": "row_1"}).appendTo("#listWorkoutPlan");
            $(document.createElement("div")).attr({"class": "col-sm-12", "id" : "col_1"}).appendTo("#row_1");
            $(document.createElement("div")).attr({"class": "card", "id" : "card_1"}).appendTo("#col_1");
            $(document.createElement("div")).attr({"class": "card-block", "id" : "cardblock_1"}).appendTo("#card_1");
            $(document.createElement("h4")).attr({"class": "card-title"}).append('No plans added!').appendTo("#cardblock_1");
            $(document.createElement("p")).attr({"class": "card-text"}).text(msg).appendTo("#cardblock_1");
			return;
		} 
		$.each(plansList,function(idx,val) {
			var id = val.id;
			$(document.createElement("div")).attr({"class":"row", "id": "row_"+id}).appendTo("#listWorkoutPlan");
			$(document.createElement("div")).attr({"class": "col-sm-12", "id" : "col_"+id}).appendTo("#row_"+id);
			$(document.createElement("div")).attr({"class": "card", "id" : "card_"+id}).appendTo("#col_"+id);
			$(document.createElement("div")).attr({"class": "card-block", "id" : "cardblock_"+id}).appendTo("#card_"+id);
			$(document.createElement("h4")).attr({"class": "card-title"}).append(val.plan_name).appendTo("#cardblock_"+id);
			$(document.createElement("p")).attr({"class": "card-text"}).text(val.message).appendTo("#cardblock_"+id);
			$(document.createElement("p")).attr({"class": "card-text"}).html("<small class='text-muted'>Last updated "+val.time+" mins ago</small>").appendTo("#cardblock_"+id);
			$(document.createElement("a")).attr({"class": "btn btn-primary", "href": "#"}).text("Edit").appendTo("#cardblock_"+id).on('click',function() {
				woMain.getPlanDetails(val.id);	
			});
		});
	};

	/** exec ajax for create new workout plan */
	this.addNewWorkoutPlan = function() {
		var params = {
			'method': (updateStatus === 0) ? 'POST' : 'PUT', 
			'api': (updateStatus === 0) ? 'workout' :  'workout/'+updateStatus,
			action: 'addNewPlan',
			data: {
            	name: $('#planNameTxt').val(),
            	exercises: addExerciseObj
        	}
		}
		woMain.execAPI(params);
	};

	/** populate plan details for modification  */
	this.createModifyView = function(response) {
		addExerciseObj = response['planExercise'];
		$('#planNameTxt').val(response['plan']['name']);
		woMain.createExerciseList();
		$('#addNewPlanBtn, #listWorkoutPlan').hide();
		$('#modifyWorkoutPlan, #cancelBtn').show();
		updateStatus = response['plan']['id'];	
	};

	/** exec ajax to get selected plan details */
	this.getPlanDetails = function(planId) {
        var params = {
            'method': 'GET',
            'api': 'workout/'+planId,
            action: 'getPlanDetails',
        }
        woMain.execAPI(params);
	};

	/** validate if required fields added for workout plan */
	this.validateWorkoutPlan = function() {
		$('.planNameDiv').removeClass("has-danger");
		if($('#planNameTxt').val() === "") {
			$('.planNameDiv').addClass("has-danger");
			$('.planNameMsg').show();
			return false;
		}
		woMain.addNewWorkoutPlan();
		return;
	};

}).apply(woMain);


$(document).ready(function() {
	woMain.init();
})
