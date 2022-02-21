const https = require("https"); //First require the module 

const url = https://url.com 
//good practice is to assign the url to a const named url//

app.get("/", function(req, res){
    https.get(url, function(res){
        console.log(res);//if you wish to console log the respone from the server
    });