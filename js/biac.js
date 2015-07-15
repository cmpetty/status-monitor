var stat_timeout = 180; /* timeout in seconds */
var sleep_time = 30; /* time between checks in seconds */
var DEBUG=false; /* enable/disable console messages */

function check_stat() {
    $.ajax({
       type: "GET",
       url: "funcs/get_stat.php",
       data: "stat=1",
       processData: false,
       dataType: "json",
       async: false,
       success:function(results){
           var currd = new Date();
           
           for ( host in results.data ){
            for ( s in results.data[host] ){
                if(DEBUG){ console.log("host: "+ host+", idx: " + s); };
           
                var d = new Date(results.data[host][s]['ts']);
                var thisstat = $(".machine-label:contains('" + host + "')").siblings("#" + results.data[host][s]['procname'] )[0]
                var up_delay = (currd - d)/1000; //time between now and last update ts in secs

                //if ( up_delay >= 0 ){
                if ( up_delay <= stat_timeout && up_delay >= 0 ){
                /*XXX add more cases */
                    if  (results.data[host][s]['started']){
                        if (results.data[host][s]['running']=="t" && !$(thisstat).hasClass("green")){
                            $(thisstat).attr('class','circle green');
                        } else if (results.data[host][s]['running']=="f" && !$(thisstat).hasClass("red")){
                            $(thisstat).attr('class','circle red');
                        }
                    }
           
                    //if last update between timeout and 20 minutes set red
                } else if ( (up_delay >= stat_timeout) && (up_delay <= 3600) ){
                    if (!$(thisstat).hasClass("red")){
                        $(thisstat).attr('class','circle red');
                    }
                    //otherwise just set it as empty
                } else {
                    if (!$(thisstat).hasClass("empty")){
                        $(thisstat).attr('class','circle empty');
                    }
                }
            }
           }
           
        }
    });
}

function check_disk() {
    $.ajax({
           type: "GET",
           url: "funcs/get_stat.php",
           data: "disk=1",
           processData: false,
           dataType: "json",
           async: false,
           success:function(results){
           var currd = new Date();
           
           for ( host in results.data ){
            for ( di in results.data[host] ){
                if (DEBUG){ console.log("host: "+ host+", idx: " + di + " , disk: " + results.data[host][di]['display'] ); };
                var d = new Date(results.data[host][di]['ts']);
                var thisdisk = $(".machine-label:contains('" + host + "')").siblings("#" + results.data[host][di]['display'] )[0]
                var up_delay = (currd - d)/1000; //time between now and last update ts in secs
                var usage = /(\d{1,})/.exec(results.data[host][di]['usage'])[0];
       
           if (thisdisk){
                if ( up_delay >= 0 ){
                //if ( up_delay <= stat_timeout && up_delay >= 0 ){
                /*XXX add more cases */
                    if  (thisdisk.style.height != results.data[host][di]['usage'] ){
                        $(thisdisk).css("height",results.data[host][di]['usage']);
                        $(thisdisk).find("span").text(results.data[host][di]['usage']);
                        if (usage > 90) {
                            $(thisdisk).attr('class','bar red');
                        } else if ((usage > 80) && (usage < 90)) {
                            $(thisdisk).attr('class','bar yellow');
                        } else {
                            $(thisdisk).attr('class','bar blue');
                        }
                    }
                }
           } else {
            if (DEBUG) { console.log("host: " + host + " disk: " + results.data[host][di]['display'] + " not found!"); };
           }
           //IS DELAY NECESSARY FOR DISKS?
           /*else if ( (up_delay >= stat_timeout) && (up_delay <= 3600) ){
            if (!$(thisdisk).hasClass("red")){
                $(thisdisk).attr('class','red bar');
            }
            //otherwise just set it as empty
           } else {
            if (!$(thisdisk).hasClass("empty")){
                $(thisdisk).attr('class','empty bar');
            }
           }
            */
            }
           }
           }
           });
}

function showBrowser(scanner) {
	clear_exambrowser();
    
	var mybrowser = $("#exambrowser");
    
	var myresults = mybrowser.find("#results");
	var loader = "<img src=\"images/ajax-loader2.gif\" id=\"loader\" title=\"loading\" alt=\"loading\"/>";
	myresults.append(loader);
    
	examData = null;
	$.ajax({
        type: "GET",
        url: "funcs/exambrowser_v2.php" + "?sc=" + scanner,
        processData: false,
        async: false,
        success: function(results){
           //alert(results);
           examData = results;
           examFrame = "<div id=\"exams\">";
           for ( key in results.exams ) {
            examFrame += "<div class=\"exam-show\" data-examnum=\'" + results.exams[key] + "\'>" + results.exams[key] + "</div>\n";
           }
           examFrame += "</div>";
           examFrame += "<div id=\"examTable\" />";
           examFrame += "<div id=\"linkbox\" />";
           myresults.find("#loader").remove();
           myresults.append(examFrame);
        }
    });
    
    mybrowser.fadeIn("fast");
    return examData;
}; 

function showTransferBrowser(scanner){
    clear_exambrowser();
    
    var mybrowser = $("#exambrowser");
    var myresults = mybrowser.find("#results");
    
    var loader = "<img src=\"images/ajax-loader2.gif\" id=\"loader\" title=\"loading\" alt=\"loading\"/>";
    myresults.append(loader);
    
    $.ajax({
           type: "GET",
           url: "funcs/transferbrowser_v2.php?",
           data: ({ sc:scanner, offset:0, limit:0 }),
           processData: true,
           async: false,
           success: function(results){
           //alert(results);
            if ( results.columns[1] ) {
                var myTable = "<div id=\"transTable\">";
                myTable += "<table>";
                for ( key in results.columns ) {
                    myTable += "<th>" + results.columns[key] + "</th>";
                }
           
                for ( key in results.data ) {
                    myTable += "<tr>";
                    for ( dataKey in results.data[key] ) {
                        if ( results.data[key][dataKey] == "Not Sent" ) {
                            myTable += "<td class=\"error\">";
                        } else {
                            myTable += "<td>";
                        }
                        myTable += results.data[key][dataKey] + "</td>";
                    }
                    myTable += "</tr>";
                }
           
                myTable += "</table></div>";
                //alert(myTable);
                myresults.find("#loader").remove();
                myresults.append(myTable);
            } else {
                myresults.find("#loader").remove();
                myresults.append("<div id=\"transTable\">No scans found in the current scanner database</div>");
            }
           }
        });
    mybrowser.fadeIn("fast");
}; 

function showDCMHistory(scanner){
    
    clear_exambrowser();
    
    var mybrowser = $("#exambrowser");
    var myresults = mybrowser.find("#results");
    var myform = mybrowser.find("#searchrange");
    
    mybrowser.fadeIn("fast");
    myform.show();
    myform.find("input[name='sc']").val(scanner);
    
    //must unbind first to prevent multiple events
    $("#mybutton").unbind('click').click(function() {
        var loader = "<img src=\"images/ajax-loader2.gif\" id=\"loader\" title=\"loading\" alt=\"loading\"/>";
        myresults.append(loader);
                                         
        $.ajax({
            type: "GET",
            url: "funcs/dcmhist_v2.php?",
            data: $("form").serialize() ,
            processData: true,
            success: function(results){
                //alert(results);
                if ( results.columns[1] ) {
                    var myTable = "<div id=\"transTable\">";
                    myTable += "<table>";
                    for ( key in results.columns ) {
                        myTable += "<th>" + results.columns[key] + "</th>";
                    }
               
                    for ( key in results.data ) {
                        myTable += "<tr>";
                        for ( dataKey in results.data[key] ) {
                            if ( results.data[key][dataKey] == "Not Sent" ) {
                                myTable += "<td class=\"error\">";
                            } else if ( results.data[key][dataKey] == "Yes"  ) {
                                myTable += "<td class=\"current\">";
                            } else {
                                myTable += "<td>";
                            }
                            myTable += results.data[key][dataKey] + "</td>";
                        }
                        myTable += "</tr>";
                    }
                                                
                    myTable += "</table></div>";
                    //alert(myTable);
                    myresults.find("#loader").remove();
                    myform.hide();
                    myresults.append(myTable);
                } else {
                    myresults.find("#loader").remove();
                    myform.hide();
                    myresults.append("<div id=\"transTable\">No scans found within this range</div>");
                }
            }
        });
        mybrowser.fadeIn("fast");
        });
}; 


function buildrows(inobj){
    var tablerows;
    for (r in inobj){
        tablerows += "<tr>"
        tablerows += "<td>"
        tablerows += inobj[r].join("</td><td>")
        tablerows += "</td>"
    }
    return tablerows;
}

function clear_exambrowser() {
	var myresults = $("#results",$("#exambrowser"));
    myresults
        .find("#exams").remove()
        .end()
        .find("#examTable").remove()
        .end()
        .find("#transTable").remove()
        .end()
        .find("#linkbox").remove();
    
    $("#searchrange",$("#exambrowser")).hide();
};



function examToggler(exam) {
	var mybrowser = $("#exambrowser");
	var myresults = mybrowser.find("#results");
	var myTable = "<table>";
    
	for ( col in examData.columns )	{
		myTable += "<th>";
		myTable += examData.columns[col];
		myTable += "</th>";
	}
    myTable += "\n";
    
	for ( key in examData.data ) {
		for ( k in examData.data[key] ) {
			if ( k == exam ) {
                myTable += "<tr>";
                for ( col in examData.data[key][k] ) {
					myTable += "<td>";
					myTable += examData.data[key][k][col];
					myTable += "</td>";
                }
                myTable += "</tr>";
			}
		}
	}
    
	myTable += "</table>";
	myresults.find("#examTable").html(myTable);
    
    //myresults.find("#linkbox").html("<div class=\"thisexam\" id=\"" + exam + "\">View Logs</div>");
}

/* run disk/stat updates in loop */
function run_forever() {
    if (DEBUG){ console.log( "running" ); };
    check_disk();
    check_stat();
    var currd = new Date();
    $("#curr_time").text( currd.toLocaleDateString() + " " + currd.toLocaleTimeString() );
}



$( document ).ready(function() {
    if (DEBUG) { console.log( "ready!" ); };
                    
    run_forever();
    window.setInterval(function(){ run_forever() },sleep_time * 1000);
                    
    var mybrowser = $("#exambrowser");
    var myresults = mybrowser.find("#results");
    var lastExam = null;
                    
    $("#close").click(function() {
        mybrowser.fadeOut("fast");
        clear_exambrowser();
    });

    //pressing escape will close the popup
    $(document).keyup(function(e) {
        if ( e.keyCode == 27 ) { //escape keyCode
            $("#close").click();
        } else if ( e.keyCode == 18 ) {
            if ( $(".logbox").css('height') == "0px" ){
                $(".logbox").animate({height:"60%"});
            } else {
                $(".logbox").animate({height:"0%"});
            }
        }
    });

    /* scanner button functions */
    $("a.scanner-button").click(function(e) {
        e.preventDefault();
        var scanner=$(this).data('suiteid');
        var which_func = $(this).text();
        
        if (which_func == 'exams'){
            /* exam browser */
            showBrowser(scanner);
        } else if (which_func=='dcm'){
            /* show dicom transfers */
            showTransferBrowser(scanner);
        } else if (which_func=='history'){
            /* dicom history browser */
            showDCMHistory(scanner);
        } else {
            if (DEBUG) { console.log("nothing to do"); };
        }
    });
     
    /* exambrowser functions */
    $("div#results").on("click",$("div.exam-show"),function(e){
        //var examnum=$(e.toElement).data('examnum');
        var examnum=e.target.dataset.examnum;
        /*remove the last green exam*/
        if(lastExam!=null){
            $(lastExam).removeClass("green");
        }
        /* show the new exam scans */
        if(examnum){
            lastExam = e.target;
            $(lastExam).addClass("green");
            examToggler(examnum);
            if (DEBUG) { console.log("clicked: " + examnum); };
        }
    });
                    
    /* tooltip functionality */
    $("div.circle, div.bar").hover(function(e){
        $(this).append( "<div class=\"tooltip\" title=\"" + $(this).attr("id") + "\"></div>" );
        }, function(e){
        $(this).find("div.tooltip").remove();
      }
    );
    
                    
                    /*
                    $("div.circle").on("click", function(){
                        $(this).append( "<div class=\"tooltip\" title=\"" + $(this).attr("id") + "\"></div>" );
                        console.log("clicked " + $(this).attr("id") );
                    });
                    */
                    
});