<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0;">
    <meta name="format-detection" content="telephone=no"/>
  </head>
  <body>
    <div style="min-width: 400px; max-width: 860px; border: 1px solid #004b77; padding: 20px 40px 30px; font: 12px/18px Arial, sans-serif; color: #38393a; background: white;">
      <div style="float: right;margin: 0 10px 20px 0; ">
        <a href="#"><img src="https://sabahpark.datanonisoeroso.com/img/apple-touch-icon-114x114-precomposed.png" style="border: none;"/></a>
      </div>
      <div style="font: bold 20px/22px Arial, sans-serif;margin: 0 10px 20px 0;">	
        E-TICKET
      </div>
      <div style="font: bold 13px/19px Arial, sans-serif; margin-bottom: 5px;">
        TICKET REF 
      </div>
	  <div style="font: bold 23px/19px Arial, sans-serif; margin-bottom: 15px;">
        {{$billing_id}}
      </div>
      <div style="font: bold 20px/19px Arial, sans-serif; margin-bottom: 5px;">
      {{$destname}}
      </div>
	  <div style="font: bold 20px/19px Arial, sans-serif; margin-bottom: 15px;">
      {{$datego}}
      </div>
      <div style="clear: both;"></div>
      <table cellpadding="0" cellspacing="0" border="0" width="100%" style="font: 12px/16px Arial, sans-serif; color: #4c4d4f; margin-bottom: 10px;">
        <tr>
          <th style="padding: 6px 8px; border: 1px solid #7f9eb1; color: white; background: #0be1ea; text-align: left;border-right: 1px solid #7f9eb1;">GUEST DETAILS</th>
          
        </tr>
        <tr>
          <td valign="top" style="padding: 6px 8px; border-left: 1px solid #a59f93; border-right: 1px solid #a59f93; border-bottom: 1px solid #a59f93;">NAME	: {{$nama_visitor}}</td>
        </tr>
        <tr>
          <td valign="top" style="padding: 6px 8px; border-left: 1px solid #a59f93; border-right: 1px solid #a59f93; border-bottom: 1px solid #a59f93;">EMAIL	:{{$email}}</td>
        </tr>
        <tr>
          <td valign="top" style="padding: 6px 8px; border-left: 1px solid #a59f93; border-right: 1px solid #a59f93; border-bottom: 1px solid #a59f93; ">CONTACT	: {{$phone}}</td>
        </tr>
      </table>
      <table cellpadding="0" cellspacing="0" border="0" width="100%" style="font: 12px/16px Arial, sans-serif; color: #4c4d4f; margin-bottom: 10px;">
        <tr>
          <th style="padding: 6px 8px; border: 1px solid #7f9eb1; color: white; color:black;text-align: left;">
		  Present ticket and valid identification at counter</th>
          <th style="padding: 6px 8px; border-top: 1px solid #7f9eb1; border-bottom: 1px solid #7f9eb1; border-right: 1px solid #7f9eb1; color:black; text-align: center;">Check-in at least 60 minutes before climbing
          </th>
          <th style="padding: 6px 8px; border-top: 1px solid #7f9eb1; border-bottom: 1px solid #7f9eb1; border-right: 1px solid #7f9eb1; color:black; text-align: center;">Please ensure all belongings are kept securely
          </th>
        </tr>
      </table>
      <div style="margin:auto; width:300px;display:block;margin-bottom: 20px;">
          
           <img style="height:300px;width:300px;margin-top:25px;" src="data:image/png;base64, {!!$message->embedData($ticket, 'test.png', 'image/png')!!}" />
        <p style="text-align: center;font: 22px/18px Arial"><b>SCAN HERE</p>
      </div>
      <table border="0" cellpadding="0" cellspacing="0" align="center" style=" margin-bottom: 10px;max-width: 240px; min-width: 120px; border-collapse: collapse; border-spacing: 0; padding: 0;">
        <tr>
          <td align="center" valign="middle" style="padding: 12px 24px; margin: 0;  border-collapse: collapse; border-spacing: 0; border-radius: 4px; -webkit-border-radius: 4px; -moz-border-radius: 4px; -khtml-border-radius: 4px;"
            bgcolor="#0be1ea">
            <a style=" color: #FFFFFF; font-family: sans-serif; font-size: 17px; font-weight: 400; line-height: 120%;"
              href="#">
            SABAHPARKS.COM
            </a>
          </td>
        </tr>
      </table>
      <div style="font-size: 11px; line-height: 17px; border-top: 1px solid #004b77; padding-top: 6px;">
        <p style="font: 22px/18px Arial;"><b>GO PAPERLESS</p>
        Show e-ticket in your SPTIX app or mobile web at check-in.<br/>
        To see bookings made on another device, log in with email
        used at the time of booking.
        <br/>
        <p style="font: 18px/18px Arial;"><b>Scan QR code to download SPTIX app for FREE</p>
      </div>
    </div>
  </body>
</html>