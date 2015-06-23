var stat_timeout = 180; /* timeout in seconds */
var DEBUG=true; /* enable/disable console messages */

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
           var outputstr = "";
           var stat_hldr=$("#simple_stat_holder");
           
           for ( host in results.data ){
            for ( s in results.data[host] ){
                if(DEBUG){ console.log("host: "+ host+", idx: " + s); };
           
                var d = new Date(results.data[host][s]['ts']);
                var up_delay = (currd - d)/1000; //time between now and last update ts in secs

                if ( up_delay <= stat_timeout && up_delay >= 0 ){
                    if  (results.data[host][s]['started']=="t"){
                        if (results.data[host][s]['running']=="t"){
                            if(DEBUG){ console.log("P host: "+ host+", procname: " + results.data[host][s]['procname']+ " on"); };
                            outputstr += "P "+ host.split('.')[0] + " " + results.data[host][s]['procname']+ " on ";
                        } else if (!results.data[host][s]['running']=="f"){
                            if(DEBUG){ console.log("P host: "+ host+", procname: " + results.data[host][s]['procname']+ " off"); };
                            outputstr += "P "+ host.split('.')[0] + " " + results.data[host][s]['procname']+ " off ";
                        }
                    } else {
                        if (results.data[host][s]['running']=="t"){
                            if(DEBUG){ console.log("P host: "+ host+", procname: " + results.data[host][s]['procname']+ " on"); };
                            outputstr += "P "+ host.split('.')[0] + " " + results.data[host][s]['procname']+ " on ";
                        }
                    }
           
                    //if last update between timeout and 20 minutes set red
                } else if ( (up_delay >= stat_timeout) && (up_delay <= 3600) ){
                    if(DEBUG){ console.log("P host: "+ host+", procname: " + results.data[host][s]['procname']+ " off"); };
                    outputstr += "P "+ host.split('.')[0] + " " + results.data[host][s]['procname']+ " off ";

                    //otherwise just set it as empty
                } else {
                    if(DEBUG){ console.log("P host: "+ host+", procname: " + results.data[host][s]['procname']+ " unknown"); };
                    outputstr += "P "+ host.split('.')[0] + " " + results.data[host][s]['procname']+ " unknown ";
                }
            }
           }
           
           stat_hldr.append(outputstr);
           
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
            var outputstr = "";
            var stat_hldr=$("#simple_stat_holder");

           for ( host in results.data ){
            for ( di in results.data[host] ){
                if (DEBUG){ console.log("host: "+ host+", idx: " + di + " , disk: " + results.data[host][di]['display'] ); };
                var d = new Date(results.data[host][di]['ts']);
                var up_delay = (currd - d)/1000; //time between now and last update ts in secs
                var usage = /(\d{1,})/.exec(results.data[host][di]['usage'])[0];
       
                if ( up_delay <= stat_timeout && up_delay >= 0 ){
                    if(DEBUG){ console.log("D host: "+ host+", disk: " + results.data[host][di]['display']+ " usage: " + usage); };
                    outputstr += "D "+ host.split('.')[0] + " " + results.data[host][di]['display']+ " " + usage + " ";

                } else {
                    if(DEBUG){ console.log("D host: "+ host+", disk: " + results.data[host][di]['display']+ " usage: " + "unknown") };
                    outputstr += "D "+ host.split('.')[0] + " " + results.data[host][di]['display']+ " unknown ";

                }
           }
         }
           
           stat_hldr.append(outputstr);

        }
    });
}


$( document ).ready(function() {
    if (DEBUG) { console.log( "ready!" ); };
         
                    
    check_disk();
    check_stat();

                    
});