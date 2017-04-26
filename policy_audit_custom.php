<HTML>
<HEAD>
<SCRIPT TYPE="text/javascript">
function toggle(i){
     if(document.getElementById(i).style.display == "none"){
     	document.getElementById(i).style.display = "block";
     }
     else{
     	document.getElementById(i).style.display = "none";
     }
}
</SCRIPT>
<BODY>
<?php

if($_GET["submit"] == 1){
	$environment = $_POST["environment"];
	$policies = $_POST["policies"];
	
	// if the environment does not exists, exit
	if(strcmp($environment,"dev") != 0 &&
	   strcmp($environment,"qa") != 0 &&
	   strcmp($environment,"prod") != 0 &&
	   strcmp($environment,"ext") != 0){

		print "environment not found\n";
		exit;
	}

	// if policies string length is exceedingly large, exit
	if(strlen($policies)>20000){print "policies too large.\n";exit;}

	// gather existing policies from existing policy audit
	$tmpPolicies = `cat /var/www/html/reports/policy_audit_$environment.html`;
	$apolicies = explode("\n",$tmpPolicies);

	// store all submitted policies in submittedPolicies
	$policies = trim($policies);
	$submittedPolicies = explode("\r\n",$policies);

	// store found policies in allp associative array, key is the policy name
	// value is the policy html
	$inPolicy=0;
	$buf = "";
	$foundPolicy="";
	$allp = array();
	foreach( $apolicies as $line ){
        	$line = trim($line);
        	if( $inPolicy == 1 ){ $buf .= $line."\n"; }
        	if( preg_match("/\&nbsp\;---------- (.*)/",$line,$matches) == 1 ){
                	$foundPolicy = $matches[1];
                	$buf .= $line."\n";
                	$inPolicy = 1;
        	}
        	if( preg_match("/\<SCRIPT/",$line) == 1 && $inPolicy == 1 ){
                	$allp["$foundPolicy"] = $buf;
                	$buf="";
                	$inPolicy=0;
        	}
	}

	// print out the results
	print "<CENTER><H2>Policy Audit - Custom</H2></CENTER>\n";
	print "Click on \"Expand\" next to the policy path to get more details on the policy.  The first column is a \"flags\" column, and will summarize the enabled protections for the service.<BR><BR>\n";
	print "<B><U>Key</U></B><BR>\n";
	print "The flags column is in the format &lt;requestFlags&gt;-&lt;responseFlags&gt;.  If the policy is using a custom variable for the target message, it will not be reflected as its use is ambiguous.<BR>L - Size limit enabled<BR>\n";
	print "R - Rate limit enabled<BR>\n";
	print "C - Protect against code injection enabled<BR>\n";
	print "F - CSRF protections enabled<BR>\n";
	print "J - JSON protections enabled<BR>\n";
	print "S - SQL protections enabled<BR>\n";
	print "X - XML protections enabeld<BR><BR>\n";
	print "A key (<img src=\"/key.png\" width=\"20px\" height=\"20px\">) icon indicates the policy uses TLS 1.2<BR>\n";
	print "A cert error (<img src=\"/nocert.png\">) icon indicates no cert authentication was detected<BR><BR>\n";
	print "<HTML><BODY><SPAN STYLE=\"text-decoration:underline;color:blue\" onClick=\"toggle_all()\">Expand All</SPAN><BR><BR>\n";

	// loop through submitted policies and check if the key exists
	// in $allp.  If not, note that it does not exist
        foreach( $submittedPolicies as $v){
        	if( array_key_exists($v,$allp) ){
               		print $allp{"$v"};
                }
                else{print substr($v,0,20)."... does not exists<BR>\n";}
        }
}
else{ // form not submitted, print default page

?>

<!-- Default page -->
<CENTER><H2>Create Custom Policy Audits</H2></CENTER>
	<form action="https://cop.localsite.com/policy_audit_custom.php?submit=1" method="post">
		Please specify the environment:&nbsp;&nbsp;
		<select name="environment" id="environment">
			<option value="dev">Dev</option>
			<option value="qa">QA</option>
			<option value="prod">Prod</option>
			<option value="ext">Ext</option>
		</select><BR>
		Please specify the policies:<BR>
		<textarea id="policies" name="policies" rows="10" cols="60" placeholder="Please specify one policy per line"></textarea>
		<BR><input type="submit">
	</form>

<HR>

Common policy groups:<BR><BR>
<SPAN STYLE="color:blue;text-decoration:underline;" onclick="toggle('dtiprodext')">Ext - DTI</SPAN><BR>
<SPAN ID="dtiprodext" STYLE="display:none;">
<PRE ID="dtiprodext_text">
/DTI
/DTI/InfoSys
</PRE>
<button type="button" onclick="setCustom('ext','dtiprodext_text')">Fill in this policy group</button>
</SPAN>

<SPAN STYLE="color:blue;text-decoration:underline;" onclick="toggle('mmmintprod')">Int - MMM</SPAN><BR>
<SPAN ID="mmmintprod" STYLE="display:none;">
<PRE ID="mmmintprod_text">
/dsitpr/mmm*
/dsitpr/sdpweb/*
</PRE>
<button type="button" onclick="setCustom('prod','mmmintprod_text')">Fill in this policy group</button>
</SPAN>


<?php

}// end default page

?>

<SCRIPT TYPE="text/javascript">
// this is needed to properly display the element id's for the found policies
var elements = new Array();
for(i=0; i <= 5000;i++){
	if(document.getElementById("id_"+i) == null){}
        else{elements.push(document.getElementById("id_"+i));}
}
function setCustom(env,policies){
	document.getElementById('environment').value=env;
	document.getElementById('policies').value=document.getElementById(policies).innerHTML;
}
function toggle_all(){
	c = elements[0].style.display;
     	for(i=0; i<elements.length;i++){
        	if(c == "none"){
                	elements[i].style.display = "block";
          	}
          	else{
               		elements[i].style.display = "none";
          	}
      	}
}
</SCRIPT>
</BODY>












