<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
<head style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
	<meta name="viewport" content="width=600" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;" charset=utf-8">
	<title style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">SABAH PARKS</title>
	<style>
	    .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body style="margin: 0;padding: 15;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;-webkit-font-smoothing: antialiased;-webkit-text-size-adjust: none;">
	<table width="100%" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;overflow:auto;">
		<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
			<td align="center" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
			    <table width="100%" cellpadding="0" border="0" align="center" cellspacing="0" style="margin: 0;padding-top: 20;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
					<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
						<td align="center" style="padding-bottom: 0.5em;margin: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
							<h2 style="margin: 0;padding: 0 18px;font-family: sans-serif;line-height: 125%;display: block;font-size: 24px;font-style: normal;font-weight: bold;letter-spacing: normal;">{{ $title }}</h1>
						</td>
					</tr>
				</table>
				<table width="100%" cellpadding="0" border="0" align="center" cellspacing="0" style="margin: 0;margin-top: 10px;margin-bottom: 10px;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
					<tr style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
						<td width="100%" style="margin: 0;padding: 0;font-family: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;line-height: 1.25;">
							<table width="100%" align="center" style="margin: 0;padding: 20;border: 1px solid black;border-collapse: collapse;font-size:12px;">
							    <thead>
                                    <tr>
                                        <th style="width:45px;border: 1px solid black;padding: 3px;">No.</th>
                                        @foreach($headers as $key => $value)
                                            <th style="border: 1px solid black;padding: 3px;">{{ $value['header'] }}</th>
                                        @endforeach
                                    </tr>
                        	    </thead>
                        	    <tbody>
                        	        @foreach($data as $key => $value)
                        	        <tr>
                        	            <td style="width:45px;border: 1px solid black;padding: 3px;text-align:center;">{{$key+1}}</td>
                        	            @foreach($headers as $k => $head)
                                        <td style="border: 1px solid black;padding: 3px;white-space: nowrap !important;">{{ $value->{$head['field']} }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                        	    </tbody>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>