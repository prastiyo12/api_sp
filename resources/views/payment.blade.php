<!DOCTYPE html>
<html>
<head>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
    body {
        margin: 0;
        padding: 0;
        background-color: #FAFAFA;
		border: 1px grey solid;
        }
    * {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
    }	
    .page {
        width: 21cm;
        min-height: 29.7cm;
        padding: 0.5cm;
        margin: 1cm auto;
        border: 0px #000000 solid;
		border-collapse: collapse;
        border-radius: 1px;
        background: white;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }
    .subpage1 {
        padding: 1cm;
        border: 1px grey solid;
        height: 270mm;
        outline: 2cm #FFFFFF solid;
    }
	
	.subpage2 {
        padding: 1cm;
        border: 1px grey solid;
        height: 285mm;
        outline: 2cm #FFFFFF solid;
    }
    
    @page {
        size: A4 portrait;
        margin: 0;
    }
    @media print {
        .page {
            margin: 0;
            border: initial;
            border-radius: initial;
            width: initial;
            min-height: initial;
            box-shadow: initial;
            background: initial;
            page-break-after: always;
        }
    }

div.bookref {
  width: 150px;
  padding: 10px;
  border: 3px solid gray;
  margin: 0;
  text-align: center;
  background-color: #ffffff;
  vertical-align: center;
  font-family:montserrat;
  }

#bookrefnum {
vertical-align: center;
  font-family:montserrat;
  text-align: center;
  font-weight: bold;
  font-size: 20pt;
}

#bookrefer {
vertical-align: center;
  font-family:montserrat;
  text-align: center;
  font-weight: noscript;
  font-size:12pt;
}


h1, h2, h3, h4, h5 {
  page-break-after: avoid;
}

#invheader{
text-transform: uppercase;
background-color: #ffffff;
text-align: left;
font-family: montserrat;
}

#invsubheader{
  background-color: #CBEDE9;
  border: 2px solid #CBEDE9;
  padding: 4px;
  margin: 4px;
  text-align: left;
  font-family: montserrat;
  font-size: 10pt; 

}

#tnc1{
border: black 1px solid;
padding: 4px;
vertical-align:top;

}

#tnc2{
  text-align: justify;
  text-justify: inter-word;
  color:black;
  font-family:montserrat;
  font-size: 7.1pt; 
  line-height: 1.5;
  
}

#tnc-numbering{
vertical-align:top;
}

#bookdeetsfield{
  text-align: justify;
  text-justify: inter-word;
  color:black;
  font-family:montserrat;
  font-size: 7.1pt; 
  line-height: 1.5;
  font-weight: bold;
  width: 100px;
  margin: 10px;
  padding-left: 10px;
}

#bookdeetsfield2{
  text-align: justify;
  text-justify: inter-word;
  color:black;
  font-family:montserrat;
  font-size: 7.1pt; 
  line-height: 1.2;
  font-weight: light;
  
}

#bookdeets{
	border: black 1px solid;
	border-collapse: collapse;
	padding: 4px;
	vertical-align:top;
	font-family:montserrat;
}


ol.guests {
  list-style-type: ;
  margin: 10;
  padding: 10;
  overflow: hidden;
  background-color: #fffff;
  font-family:montserrat;
  font-size: 7.1pt;
  line-height: 1.8;
}

div.footer {
   position: fixed;
   left: 0;
   bottom: 0;
   width: 100%;
   color: black;
   text-align: center;
   padding-bottom: 10px;
   font-family:montserrat;
   font-size: 7.1pt;
   
}

div.footer2 {
   //position: fixed;
   left: 0;
   bottom: 0;
   width: 100%;
   color: black;
   text-align: center;
   padding-bottom: 10px;
   font-family:montserrat;
   font-size: 7.1pt;
   
}


th.actiheader{
font-family:montserrat; 
font-size: 8pt; 
line-height: normal; 
letter-spacing: 1px; 
font-weight: bold;
text-align: center;
border: 0px solid black;

}

td.acticontent{
font-family:montserrat; 
font-size: 8pt; 
line-height: normal; 
letter-spacing: 1px; 
font-weight: normal
text-align: left;
height:35px;
padding-left:20px;
}

</style>
</head>
<body >
    <div class="book">
        <div style="width: 21cm;min-height: 29.7cm;padding: 0.5cm;margin: 1cm auto;border: 0px #000000 solid;border-collapse: collapse;border-radius: 1px;background: white;box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);">
            <div style="padding: 1cm;border: 1px grey solid;height: 270mm;outline: 2cm #FFFFFF solid;">
    		
        		<!-- Header -->
        		
        		<table width="100%" style="border:1px; border-collapse: collapse;" >
        			<tr>
        				<td align="center" width="120">
        				    <img src="https://www.apisptix.sabahparks.org.my/auth/icon-success" />
        				</td>
        				
        			</tr>
        			<tr>
        			    <td rowspan="3" width="auto" align='center' style="padding-right: 10px;">
        					<div style="width: 200px;padding: 10px;border: 3px solid gray;margin: 0;text-align: center;background-color: #ffffff;vertical-align: center;font-family:montserrat;" >
        						<div style="vertical-align: center;font-family:montserrat;text-align: center;font-weight: noscript;font-size:12pt;">BOOKING REF.</div>
        						<div style="vertical-align: center;font-family:montserrat;text-align: center;font-weight: bold;font-size: 20pt;">{{$billing_id}}</div>
        					</div>
        				</td>
        			</tr>
        		</table>
        		<br>
    		
                <!-- Booking details -->
    
    		    <table width='100%' border='0'>
    			<tr>
    				<th style="background-color: #CBEDE9;border: 2px solid #CBEDE9;padding: 4px;margin: 4px;text-align: left;font-family: montserrat;font-size: 10pt; " >BOOKING DETAILS</th>
    			</tr>
    		</table>
        		<div style="padding:4px;"></div>
        		<table width='100%' style="border: black 1px solid;border-collapse: collapse;padding: 4px;vertical-align:top;font-family:montserrat;">
        			<tr>
        			<td style=" text-align: justify;text-justify: inter-word;color:black;font-family:montserrat;font-size: 7.1pt; line-height: 1.5;font-weight: bold;width: 100px;margin: 10px;padding-left: 10px;">BOOKING DATE</td>
        			    <td>:</td>
        			    <td style=" text-align: justify;text-justify: inter-word;color:black;font-family:montserrat;font-size: 7.1pt; line-height: 1.2;font-weight: light;"> {{$billing_date}}</td>
        			</tr>
        			<tr>
        			    <td style=" text-align: justify;text-justify: inter-word;color:black;font-family:montserrat;font-size: 7.1pt; line-height: 1.5;font-weight: bold;width: 100px;margin: 10px;padding-left: 10px;">NAME</td>
        			    <td>:</td>
        			    <td style=" text-align: justify;text-justify: inter-word;color:black;font-family:montserrat;font-size: 7.1pt; line-height: 1.2;font-weight: light;"> {{$name}}</td>
        			</tr>
        			<tr>
        			    <td style=" text-align: justify;text-justify: inter-word;color:black;font-family:montserrat;font-size: 7.1pt; line-height: 1.5;font-weight: bold;width: 100px;margin: 10px;padding-left: 10px;" width='100px'>ADDRESS</td>
        			    <td>:</td>
        			    <td style=" text-align: justify;text-justify: inter-word;color:black;font-family:montserrat;font-size: 7.1pt; line-height: 1.2;font-weight: light;"> {{$address}} </td>
        			</tr>
        			<tr>
        			    <td style=" text-align: justify;text-justify: inter-word;color:black;font-family:montserrat;font-size: 7.1pt; line-height: 1.5;font-weight: bold;width: 100px;margin: 10px;padding-left: 10px;" width='100px'>CONTACT</td>
        			    <td>:</td>
        			    <td style=" text-align: justify;text-justify: inter-word;color:black;font-family:montserrat;font-size: 7.1pt; line-height: 1.2;font-weight: light;"> {{$phone}}</td>
        			</tr>
        			<tr>
        		        <td style=" text-align: justify;text-justify: inter-word;color:black;font-family:montserrat;font-size: 7.1pt; line-height: 1.5;font-weight: bold;width: 100px;margin: 10px;padding-left: 10px;" width='100px'>EMAIL</td>
        			    <td>:</td>
        			    <td style=" text-align: justify;text-justify: inter-word;color:black;font-family:montserrat;font-size: 7.1pt; line-height: 1.2;ont-weight: light;">{{$email}}</td>
        			</tr>
        		</table>
        		<br>
        		<table width='100%' border='0'>
        			<tr>
        				<th style="background-color: #CBEDE9;border: 2px solid #CBEDE9;padding: 4px;margin: 4px;text-align: left;font-family: montserrat;font-size: 10pt;" >GUEST DETAILS</th>
        			</tr>
        		</table>
        		<div style="padding:4px;"></div>
        		<table width='100%' style="border: black 1px solid;border-collapse: collapse;padding: 4px;vertical-align:top;font-family:montserrat;">
        			<tr>
            			<td style="font-family:montserrat; font-size: 7pt; line-height: normal; letter-spacing: 1px; font-weight: bold;">
            				<ol style=" list-style-type: ;margin: 10;padding: 10;overflow: hidden;background-color: #fffff;font-family:montserrat;font-size: 7.1pt;line-height: 1.8;">
            					@foreach ($visitors as $key => $node)
                                    <li>{{ $node->visitor_name }}</li>
                                @endforeach
            				</ol>
            			</td>
        			</tr>
        		</table>
        		<br>
        		<table width='100%' border='0'>
        			<tr>
        				<th style="background-color: #CBEDE9;border: 2px solid #CBEDE9;padding: 4px;margin: 4px;text-align: left;font-family: montserrat;font-size: 10pt;" >ACTIVITY DETAILS</th>
        			</tr>
        		</table>
        		<div style="padding:4px ;"></div>
    		    <table width='100%' style="border: black 1px solid;border-collapse: collapse;padding: 4px 4px 4px 4px;vertical-align:top;font-family:montserrat;">
    		    <thead>
    			    <tr>
    			        <th style="font-family:montserrat;font-size: 8pt;line-height: normal;letter-spacing: 1px;font-weight: bold;text-align: center;border: 0px solid black;width: 40%;text-align: left; height:35px; padding-left:20px;">DESCRIPTION</th>
    			        <th style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: bold;text-align: center;border: 0px solid black;" >COST (MYR)</th>
    			        <th style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: bold;text-align: center;border: 0px solid black;" >QTY</th>
    			        <th style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: bold;text-align: center;border: 0px solid black;" >SUBTOTAL (MYR)</th>
    			    </tr>
    			</thead>
    			<tbody>
        			@foreach ($data as $dest)
        			    <tr>
        			        <td colspan="4" style="font-weight:bold;font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: norma;ltext-align: left;height:35px;padding-left:20px;">{{$dest->destination->destname}} <br> ( {{date('d F Y, l', strtotime($dest->ticketdatefrom))}} )</td>
        			    </tr>
            			@foreach ($dest->prices as $price)
            			   <tr>
            				<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: norma;ltext-align: left;height:35px;padding-left:20px;">
            				    @if($price->loc_price_18above > 0 && $price->loc_price_18below > 0 && $price->int_price_18above && $price->int_price_18below  )
            				            {{$price->price_type}}
            				    @endif
            				    @if($dest->loc_qty_18above > 0)
            				        @if($price->loc_price_18above > 0)
            				            <p style="padding-left:25px;">Malaysian/PR Holder 18yrs & above</p>
            				        @endif
            				    @endif
            				    @if($dest->loc_qty_18below > 0)
            				        @if($price->loc_price_18below > 0)
            					        <p style="padding-left:25px;">Malaysian/PR Holder 18yrs & below</p>
            					    @endif
            					@endif
            				    @if($dest->int_qty_18above > 0)
                				    @if($price->int_price_18above > 0)
                					    <p style="padding-left:25px;">International 18yrs & above</p>
                					@endif
            					@endif
            				    @if($dest->int_qty_18below > 0)
            				        @if($price->int_price_18below > 0)
            					        <p style="padding-left:25px;">International 18yrs & below</p>
            					    @endif
            					@endif
            				</td>
            				<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;"><br>
            				    @if($dest->loc_qty_18above > 0)
            				        @if($price->loc_price_18above > 0)
            				            <p align="center">MYR {{$price->loc_price_18above}} </p>
            				        @endif
            				    @endif
            					@if($dest->loc_qty_18below > 0)
            					    @if($price->loc_price_18below > 0)
            				            <p align="center">MYR {{$price->loc_price_18below}} </p>
            				        @endif
            				    @endif
            				     @if($dest->int_qty_18above > 0)
            				        @if($price->int_price_18above > 0)
            				            <p align="center">MYR {{$price->int_price_18above}} </p>
            				        @endif
            				    @endif
            				     @if($dest->int_qty_18below > 0)
            				        @if($price->int_price_18below > 0)
            				            <p align="center">MYR {{$price->int_price_18below}} </p>
            				        @endif
            				    @endif
            				</td>
            				<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;"><br>
            				    @if($dest->loc_qty_18above > 0)
            				        @if($price->loc_price_18above > 0)
            				            <p align="center">{{$dest->loc_qty_18above }} </p>
            				        @endif
            				    @endif
            					@if($dest->loc_qty_18below > 0)
            					    @if($price->loc_price_18below > 0)
            				            <p align="center">{{$dest->loc_qty_18below}} </p>
            				        @endif
            				    @endif
            				     @if($dest->int_qty_18above > 0)
            				        @if($price->int_price_18above > 0)
            				            <p align="center">{{$dest->int_qty_18above}} </p>
            				        @endif
            				    @endif
            				     @if($dest->int_qty_18below > 0)
            				        @if($price->int_price_18below > 0)
            				            <p align="center">{{$dest->int_qty_18below}} </p>
            				        @endif
            				    @endif
            				</td>
            				<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;"><br>
            				    @if($dest->loc_qty_18above > 0)
            				        @if($price->loc_price_18above > 0)
            				            <p align="center">MYR {{number_format($dest->loc_qty_18above * $price->loc_price_18above,2,'.',',')}} </p>
            				        @endif
            				        
            				    @endif
            					@if($dest->loc_qty_18below > 0)
            					    @if($price->loc_price_18below > 0)
            				            <p align="center">MYR {{number_format($dest->loc_qty_18below * $price->loc_price_18below,2,'.',',')}} </p>
            				        @endif
            				    @endif
            				     @if($dest->int_qty_18above > 0)
            				        @if($price->int_price_18above > 0)
            				            <p align="center">MYR {{number_format($dest->int_qty_18above * $price->int_price_18above,2,'.',',')}} </p>
            				        @endif
            				    @endif
            				     @if($dest->int_qty_18below > 0)
            				        @if($price->int_price_18below > 0)
            				            <p align="center">MYR {{number_format($dest->int_qty_18below * $price->int_price_18below,2,'.',',')}} </p>
            				        @endif
            				    @endif
            				</td>
            			</tr>
            			@endforeach
            		@endforeach
        		
        			<!--<tr>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: left;height:35px;padding-left:20px;">Permit fee :-->
        			<!--		<p style="padding-left:25px;">Malaysian/PR Holder 18yrs & above</p>-->
        			<!--		<p style="padding-left:25px;">Malaysian/PR Holder 18yrs & below</p>-->
        			<!--		<p style="padding-left:25px;">International 18yrs & above</p>-->
        			<!--		<p style="padding-left:25px;">International 18yrs & below</p>-->
        			<!--	</td>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;"><br>-->
        			<!--		<p align="center"> - </p>-->
        			<!--		<p align="center"> - </p>-->
        			<!--		<p align="center"> - </p>-->
        			<!--		<p align="center"> - </p>-->
        			<!--	</td>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;"><br>-->
        			<!--		<p align="center"> - </p>-->
        			<!--		<p align="center"> - </p>-->
        			<!--		<p align="center"> - </p>-->
        			<!--		<p align="center"> - </p>-->
        			<!--	</td>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;"><br>-->
        			<!--		<p align="center"> - </p>-->
        			<!--		<p align="center"> - </p>-->
        			<!--		<p align="center"> - </p>-->
        			<!--		<p align="center"> - </p>-->
        			<!--	</td>-->
        			<!--</tr>-->
        			<!--<tr>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: left;height:35px;padding-left:20px;">Insurance<br><br><br></td>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;vertical-align: top;" align="center">MYR 10</td>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;vertical-align: top;" align="center">1</td>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;vertical-align: top;" align="center">MYR 10</td>-->
        			<!--</tr>-->
        			<!--<tr>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: left;height:35px;padding:20px;">Service Fee 7%</td>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;" align="center">-</td>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;" align="center">-</td>-->
        			<!--	<td style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: normal;text-align: center;height:35px;padding-left:20px;" align="center">-</td>-->
        			<!--</tr>-->
        			<tr>
        			    <td colspan='4'><div style="border: 2px solid black; background-color: black; border-style: solid; width: auto; margin-left:20px; margin-right: 20px;" ></div>	
        			</tr>
    			</tbody>
    			<tfoot>
    				<tr>
    			        <th style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: bold;text-align: center;border: 0px solid black;width: 40%;text-align: left; vertical-align:center; height:35px; padding-left:20px;" colspan='3'>GRAND TOTAL</th>
    			        <th style="font-family:montserrat; font-size: 8pt; line-height: normal; letter-spacing: 1px; font-weight: bold;text-align: center;border: 0px solid black;" >MYR {{$total_cost}}</th>
    			     </tr>
        			<tr>
        			    <td colspan='4'><div style="border: 2px solid black; background-color: black; border-style: solid; width: auto; margin-left:20px; margin-right: 20px;"></div>	
        			</tr>
        			<tr>
        			    <td colspan='4'><div style="height: 20px; margin-left:20px; margin-right: 20px;"></div>	
        			</tr>
    			</tfoot>
    		</table>
    		    <p style="font-weight:bold;font-family:montserrat; font-size: 10pt; line-height: normal; letter-spacing: 1px; font-weight: bold;text-align: center;font-style:italic;height:35px;padding-left:20px;">* Please check your email for detail.</p>
        		<div style="  position: fixed;left: 0; bottom: 0;width: 100%; color: black; text-align: center;padding-bottom: 10px;font-family:montserrat; font-size: 7.1pt;">  Sabah Parks   •   Office: 088-523500   •   Fax: 088 - 486435   •   e_support@sabahparks.org.my</div>    
        	    <br>
        		<br>
    		 </div>
        </div> <!-- end of page 1 -->
        
    </div>
</body>
</html>
